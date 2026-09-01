<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Shared authorization gate
     */
    protected function authorizeAdmin(Request $request): void
    {
        abort_if($request->user()->role !== 'admin', 403, 'Unauthorized. Administrator access required.');
    }

    // =========================================================================
    // 1. USER ROLE & PRIVILEGE MANAGEMENT
    // =========================================================================

    /**
     * Directly promote any existing user to a vendor role.
     */
    public function promoteUser(Request $request, $userId)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'role' => 'required|in:guide,hotel_owner,shop_owner,vehicle_owner',
        ]);

        $user = User::findOrFail($userId);
        abort_if($user->role === 'admin', 400, 'Cannot change role of an administrator.');

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'message' => "User {$user->name} has been promoted to {$validated['role']}.",
            'user' => $user->only(['id', 'name', 'email', 'role'])
        ]);
    }

    /**
     * Forcefully revoke a user's vendor privileges, delete their listings, and demote to tourist.
     */
    public function revokePrivileges(Request $request, $userId)
    {
        $this->authorizeAdmin($request);

        $user = User::findOrFail($userId);
        abort_if($user->role === 'admin', 400, 'Cannot revoke privileges from an administrator.');

        DB::transaction(function () use ($user) {
            // 1. Delete associated polymorphic images from storage and database
            $entities = collect()
                ->merge($user->hotels)
                ->merge($user->vehicles)
                ->merge($user->shops)
                ->push($user->guide)
                ->filter();

            foreach ($entities as $entity) {
                // Delete shop item images if the entity is a shop
                if ($entity instanceof Shop) {
                    foreach ($entity->items as $item) {
                        foreach ($item->images as $img) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $img->image_url));
                            $img->delete();
                        }
                    }
                }

                foreach ($entity->images as $image) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                    $image->delete();
                }
            }

            // 2. Cascade delete vendor entities
            $user->guide()->delete();
            $user->hotels()->delete();
            $user->vehicles()->delete();
            $user->shops()->delete();
            $user->roleRequests()->delete();

            // 3. Reset user role back to tourist
            $user->update(['role' => 'tourist']);
        });

        return response()->json([
            'message' => "All vendor privileges and listings for {$user->name} have been revoked."
        ]);
    }

    // =========================================================================
    // 2. SURROGATE LISTING CREATION (ON BEHALF OF SERVICE PROVIDERS)
    // =========================================================================

    /**
     * Admin creates a Hotel assigned to an existing vendor.
     */
    public function surrogateCreateHotel(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'hotel_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string',
            'whatsapp_number' => 'nullable|string',
            'email' => 'nullable|email',
            'budget_tier' => 'required|in:budget,mid_range,luxury',
            'pricing_model' => 'required|in:per_room,per_person',
            'base_price' => 'required|numeric|min:0',
            'max_total_capacity' => 'required|integer|min:1',
            'is_wheelchair_accessible' => 'boolean',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $targetUser = User::findOrFail($validated['target_user_id']);
        if ($targetUser->role === 'tourist') {
            $targetUser->update(['role' => 'hotel_owner']);
        }

        $hotel = new Hotel();
        $hotel->fill($request->except(['target_user_id', 'latitude', 'longitude', 'images']));
        $hotel->user_id = $targetUser->id;

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $hotel->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $hotel->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('vendor_images', 'public');
                $hotel->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'message' => "Hotel successfully created and assigned to {$targetUser->name}.",
            'hotel_id' => $hotel->id
        ], 201);
    }

    /**
     * Admin creates a Guide profile assigned to an existing vendor.
     */
    public function surrogateCreateGuide(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'contact_number' => 'required|string',
            'whatsapp_number' => 'nullable|string',
            'bio' => 'nullable|string',
            'specialty' => 'required|string',
            'languages_spoken' => 'required|array',
            'daily_rate' => 'required|numeric|min:0',
            'experience_years' => 'required|integer|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $targetUser = User::findOrFail($validated['target_user_id']);
        abort_if($targetUser->guide()->exists(), 400, 'Target user already has a guide profile.');

        if ($targetUser->role === 'tourist') {
            $targetUser->update(['role' => 'guide']);
        }

        $guide = new Guide($request->except(['target_user_id', 'latitude', 'longitude', 'location_ids', 'profile_picture']));
        $guide->user_id = $targetUser->id;

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $guide->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $guide->save();

        if (!empty($validated['location_ids'])) {
            $guide->locations()->sync($validated['location_ids']);
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('vendor_images', 'public');
            $guide->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => "Guide profile successfully created and assigned to {$targetUser->name}.",
            'guide_id' => $guide->id
        ], 201);
    }

    /**
     * Admin creates a Vehicle assigned to an existing vendor.
     */
    public function surrogateCreateVehicle(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'vehicle_make_model' => 'required|string|max:255',
            'registration_number' => 'required|string|unique:vehicles,registration_number|max:50',
            'rental_type' => 'required|in:self_drive,with_driver',
            'driver_name' => 'nullable|string|max:255',
            'contact_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'vehicle_type' => 'required|in:scooter,motorbike,tuk_tuk,sedan_car,safari_jeep,suv_4x4,passenger_van,mini_bus',
            'terrain_capability' => 'required|in:standard_road,off_road_4x4',
            'pricing_model' => 'required|in:per_day,per_km,both',
            'rate_per_day' => 'nullable|numeric|min:0',
            'rate_per_km' => 'nullable|numeric|min:0',
            'passenger_capacity' => 'required|integer|min:1',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'images' => 'required|array|min:1|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $targetUser = User::findOrFail($validated['target_user_id']);
        if ($targetUser->role === 'tourist') {
            $targetUser->update(['role' => 'vehicle_owner']);
        }

        $vehicle = new Vehicle();
        $vehicle->fill($request->except(['target_user_id', 'latitude', 'longitude', 'images']));
        $vehicle->user_id = $targetUser->id;

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $vehicle->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $vehicle->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('vendor_images', 'public');
                $vehicle->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'message' => "Vehicle successfully created and assigned to {$targetUser->name}.",
            'vehicle_id' => $vehicle->id
        ], 201);
    }

    /**
     * Admin creates a Shop assigned to an existing vendor.
     */
    public function surrogateCreateShop(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'store_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $targetUser = User::findOrFail($validated['target_user_id']);
        if ($targetUser->role === 'tourist') {
            $targetUser->update(['role' => 'shop_owner']);
        }

        $shop = new Shop();
        $shop->fill($request->except(['target_user_id', 'latitude', 'longitude', 'store_image']));
        $shop->user_id = $targetUser->id;

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $shop->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $shop->save();

        if ($request->hasFile('store_image')) {
            $path = $request->file('store_image')->store('vendor_images', 'public');
            $shop->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => "Shop successfully created and assigned to {$targetUser->name}.",
            'shop_id' => $shop->id
        ], 201);
    }

    // =========================================================================
    // 3. GLOBAL ENTITY MODERATION (FORCEFUL DELETIONS & UPDATES)
    // =========================================================================

    /**
     * Forcefully delete any hotel, guide, vehicle, or shop.
     */
    public function forceDeleteEntity(Request $request, $type, $id)
    {
        $this->authorizeAdmin($request);

        $model = match ($type) {
            'hotel' => Hotel::findOrFail($id),
            'guide' => Guide::findOrFail($id),
            'vehicle' => Vehicle::findOrFail($id),
            'shop' => Shop::with('items.images')->findOrFail($id),
            default => abort(400, 'Invalid entity type.'),
        };

        // Clean up images from disk and database
        if ($model instanceof Shop) {
            foreach ($model->items as $item) {
                foreach ($item->images as $img) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $img->image_url));
                    $img->delete();
                }
            }
        }

        foreach ($model->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $model->delete();

        return response()->json(['message' => ucfirst($type) . ' listing forcefully deleted.']);
    }

    // =========================================================================
    // 4. REVIEW MODERATION
    // =========================================================================

    /**
     * List all reviews across the system with pagination.
     */
    public function indexReviews(Request $request)
    {
        $this->authorizeAdmin($request);

        $reviews = Review::with(['user:id,name,email', 'reviewable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Delete a fraudulent or inappropriate review.
     */
    public function deleteReview(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}