<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LocationController extends Controller
{
    /**
     * Create a new Tourist Attraction / Location (Admin Only)
     */
    public function store(Request $request)
    {
        abort_if($request->user()->role !== 'admin', 403, 'Only administrators can create locations.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            
            // ML Context Variables
            'location_type' => 'required|in:mountain_trek,waterfall,tea_estate,lake_reservoir,beach_coastal,wildlife_safari,rainforest,ancient_ruins,religious_site,botanical_garden,urban_city,campsite,village_getaway',
            'terrain_difficulty' => 'required|in:easy,moderate,challenging,extreme',
            'requires_4x4' => 'boolean',
            'requires_guide' => 'boolean',
            'elevation_meters' => 'nullable|integer',
            
            // Coordinates for PostGIS
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // Gallery Images
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $location = new Location();
        $location->fill($request->except(['latitude', 'longitude', 'images']));
        
        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $location->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $location->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('location_images', 'public');
                $location->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'message' => 'Tourist attraction created successfully!',
            'location' => $location->load('images')
        ], 201);
    }

    /**
     * Update an existing Location (Admin Only)
     */
    public function update(Request $request, $id)
    {
        abort_if($request->user()->role !== 'admin', 403, 'Only administrators can update locations.');

        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'province' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'location_type' => 'sometimes|in:mountain_trek,waterfall,tea_estate,lake_reservoir,beach_coastal,wildlife_safari,rainforest,ancient_ruins,religious_site,botanical_garden,urban_city,campsite,village_getaway',
            'terrain_difficulty' => 'sometimes|in:easy,moderate,challenging,extreme',
            'requires_4x4' => 'sometimes|boolean',
            'requires_guide' => 'sometimes|boolean',
            'elevation_meters' => 'nullable|integer',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'images' => 'sometimes|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $location->fill($request->except(['latitude', 'longitude', 'images']));

        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $location->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        $location->save();

        if ($request->hasFile('images')) {
            // Delete old gallery photos
            foreach ($location->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            // Save new images
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('location_images', 'public');
                $location->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'message' => 'Location updated successfully!',
            'location' => $location->load('images')
        ]);
    }

    /**
     * Delete a Location (Admin Only)
     */
    public function destroy(Request $request, $id)
    {
        abort_if($request->user()->role !== 'admin', 403, 'Only administrators can delete locations.');

        $location = Location::findOrFail($id);

        foreach ($location->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}