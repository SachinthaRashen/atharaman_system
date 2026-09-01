<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        // 1. Authorization
        abort_if($request->user()->role !== 'vehicle_owner', 403, 'Only verified vehicle owners can list vehicles.');

        // 2. Validation
        $validated = $request->validate([
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
            
            // Coordinates for PostGIS (Where the vehicle is normally parked/dispatched from)
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // Images
            'images' => 'required|array|min:1|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 3. Create Vehicle with PostGIS Geometry
        $vehicle = new Vehicle();
        $vehicle->fill($request->except(['latitude', 'longitude', 'images']));
        $vehicle->user_id = $request->user()->id;
        
        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $vehicle->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        
        $vehicle->save();

        // 4. Handle Polymorphic Image Uploads
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
            'message' => 'Vehicle listed successfully!',
            'vehicle_id' => $vehicle->id
        ], 201);
    }

    /**
     * VENDOR ACTION: Update Vehicle
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        abort_if($vehicle->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $validated = $request->validate([
            'vehicle_make_model' => 'sometimes|string|max:255',
            'registration_number' => 'sometimes|string|unique:vehicles,registration_number,' . $id . '|max:50',
            'rental_type' => 'sometimes|in:self_drive,with_driver',
            'driver_name' => 'nullable|string|max:255',
            'contact_number' => 'sometimes|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'vehicle_type' => 'sometimes|in:scooter,motorbike,tuk_tuk,sedan_car,safari_jeep,suv_4x4,passenger_van,mini_bus',
            'terrain_capability' => 'sometimes|in:standard_road,off_road_4x4',
            'pricing_model' => 'sometimes|in:per_day,per_km,both',
            'rate_per_day' => 'nullable|numeric|min:0',
            'rate_per_km' => 'nullable|numeric|min:0',
            'passenger_capacity' => 'sometimes|integer|min:1',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'images' => 'sometimes|array|min:1|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $vehicle->fill($request->except(['latitude', 'longitude', 'images']));

        // Update PostGIS coordinates if provided
        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $vehicle->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        $vehicle->save();

        // Handle image updates
        if ($request->hasFile('images')) {
            // Delete existing images
            foreach ($vehicle->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            // Upload new images
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('vendor_images', 'public');
                
                $vehicle->images()->create([
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json(['message' => 'Vehicle profile updated successfully!', 'vehicle' => $vehicle]);
    }

    /**
     * VENDOR ACTION: Delete Vehicle
     */
    public function destroy(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        abort_if($vehicle->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        // Delete associated images
        foreach ($vehicle->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle profile deleted successfully.']);
    }   
}