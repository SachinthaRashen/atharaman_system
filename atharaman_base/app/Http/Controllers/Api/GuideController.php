<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuideController extends Controller
{
    public function store(Request $request)
    {
        // 1. Authorization & One-to-One Check
        abort_if($request->user()->role !== 'guide', 403, 'Only verified guides can create a profile.');
        if ($request->user()->guide()->exists()) {
            return response()->json(['message' => 'You already have a guide profile.'], 422);
        }

        // 2. Validation
        $validated = $request->validate([
            'contact_number' => 'required|string',
            'whatsapp_number' => 'nullable|string',
            'bio' => 'nullable|string',
            'specialty' => 'required|string',
            'languages_spoken' => 'required|array', // e.g., ["English", "German"]
            'daily_rate' => 'required|numeric|min:0',
            'experience_years' => 'required|integer|min:0',
            
            // Spatial & Pivot Data
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_ids' => 'nullable|array', // IDs of tourist attractions they specialize in
            'location_ids.*' => 'exists:locations,id',
            
            // Profile Picture
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 3. Create Guide Profile
        $guide = tap(new Guide($request->except(['latitude', 'longitude', 'location_ids', 'profile_picture'])), function ($g) use ($request, $validated) {
            $g->user_id = $request->user()->id;
            
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $g->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
            
            $g->save();
        });

        // 4. Sync Pivot Table (Guide <-> Locations)
        if (!empty($validated['location_ids'])) {
            $guide->locations()->sync($validated['location_ids']);
        }

        // 5. Handle Polymorphic Profile Picture
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('vendor_images', 'public');
            $guide->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Guide profile activated!',
            'guide_id' => $guide->id
        ], 201);
    }

    /**
     * VENDOR ACTION: Update Guide Profile
     */
    public function update(Request $request, $id)
    {
        $guide = Guide::findOrFail($id);
        abort_if($guide->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $validated = $request->validate([
            'contact_number' => 'sometimes|string',
            'whatsapp_number' => 'nullable|string',
            'bio' => 'nullable|string',
            'specialty' => 'sometimes|string',
            'languages_spoken' => 'sometimes|array',
            'daily_rate' => 'sometimes|numeric|min:0',
            'experience_years' => 'sometimes|integer|min:0',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $guide->fill($request->except(['latitude', 'longitude', 'location_ids', 'profile_picture']));

        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $guide->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        $guide->save();

        if (array_key_exists('location_ids', $validated)) {
            $guide->locations()->sync($validated['location_ids']);
        }

        // Handle profile picture update
        if ($request->hasFile('profile_picture')) {
            foreach ($guide->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            $path = $request->file('profile_picture')->store('vendor_images', 'public');
            $guide->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json(['message' => 'Guide profile updated successfully!', 'guide' => $guide->load(['images', 'locations'])]);
    }

    /**
     * VENDOR ACTION: Delete Guide Profile
     */
    public function destroy(Request $request, $id)
    {
        $guide = Guide::findOrFail($id);
        abort_if($guide->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        // Delete associated profile picture
        foreach ($guide->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $guide->delete();

        return response()->json(['message' => 'Guide profile deleted successfully.']);
    }
}