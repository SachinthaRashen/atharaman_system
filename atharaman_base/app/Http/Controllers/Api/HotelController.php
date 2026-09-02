<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    public function store(Request $request)
    {
        // 1. Authorization
        abort_if($request->user()->role !== 'hotel_owner', 403, 'Only hotel owners can list properties.');

        // 2. Validation
        $validated = $request->validate([
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
            
            // Coordinates for PostGIS
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // Images
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 3. Create Hotel with PostGIS Geometry
        $hotel = new Hotel();
        $hotel->fill($request->except(['latitude', 'longitude', 'images']));
        $hotel->user_id = $request->user()->id;
        
        // Securely cast coordinates to floats to prevent SQL injection in raw query
        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $hotel->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        
        $hotel->save();

        // 4. Handle Polymorphic Image Uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('vendor_images', 'public');
                
                $hotel->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0, // Make the first uploaded image the thumbnail
                ]);
            }
        }

        return response()->json([
            'message' => 'Hotel portfolio created successfully!',
            'hotel_id' => $hotel->id
        ], 201);
    }

    /**
     * VENDOR ACTION: Update Hotel
     */
    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        abort_if($hotel->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $validated = $request->validate([
            'hotel_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|string|max:255',
            'contact_number' => 'sometimes|string',
            'whatsapp_number' => 'nullable|string',
            'email' => 'nullable|email',
            'budget_tier' => 'sometimes|in:budget,mid_range,luxury',
            'pricing_model' => 'sometimes|in:per_room,per_person',
            'base_price' => 'sometimes|numeric|min:0',
            'max_total_capacity' => 'sometimes|integer|min:1',
            'is_wheelchair_accessible' => 'sometimes|boolean',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'images' => 'sometimes|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $hotel->fill($request->except(['latitude', 'longitude', 'images']));

        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $hotel->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        $hotel->save();

        // Handle image updates
        if ($request->hasFile('images')) {
            foreach ($hotel->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('vendor_images', 'public');
                $hotel->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json(['message' => 'Hotel updated successfully!', 'hotel' => $hotel->load('images')]);
    }

    /**
     * VENDOR ACTION: Delete Hotel
     */
    public function destroy(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        abort_if($hotel->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        foreach ($hotel->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $hotel->delete();

        return response()->json(['message' => 'Hotel deleted successfully.']);
    }
}