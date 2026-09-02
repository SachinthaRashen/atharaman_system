<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Shop;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    /**
     * LOCATIONS
     */
    public function getLocations(Request $request)
    {
        $locations = Location::query()
            ->select('locations.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->when($request->type, fn($q, $type) => $q->where('location_type', $type))
            ->paginate(15);

        return response()->json($locations);
    }

    public function showLocation($id)
    {
        $location = Location::query()
            ->select('locations.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images', 'reviews.user:id,name'])
            ->findOrFail($id);

        return response()->json($location);
    }

    /**
     * HOTELS
     */
    public function getHotels(Request $request)
    {
        $hotels = Hotel::query()
            ->select('hotels.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->withAvg('reviews', 'rating')
            ->when($request->budget_tier, fn($q, $tier) => $q->where('budget_tier', $tier))
            ->paginate(15);

        return response()->json($hotels);
    }

    public function showHotel($id)
    {
        $hotel = Hotel::query()
            ->select('hotels.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images', 'reviews.user:id,name', 'user:id,name,email,phone_number'])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($hotel);
    }

    /**
     * GUIDES
     */
    public function getGuides(Request $request)
    {
        $guides = Guide::query()
            ->select('guides.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images' => fn($q) => $q->where('is_primary', true), 'user:id,name', 'locations:id,name'])
            ->withAvg('reviews', 'rating')
            ->paginate(15);

        return response()->json($guides);
    }

    public function showGuide($id)
    {
        $guide = Guide::query()
            ->select('guides.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images', 'reviews.user:id,name', 'user:id,name,email,phone_number', 'locations'])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($guide);
    }

    /**
     * VEHICLES
     */
    public function getVehicles(Request $request)
    {
        $vehicles = Vehicle::query()
            ->select('vehicles.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->withAvg('reviews', 'rating')
            ->when($request->vehicle_type, fn($q, $type) => $q->where('vehicle_type', $type))
            ->paginate(15);

        return response()->json($vehicles);
    }

    public function showVehicle($id)
    {
        $vehicle = Vehicle::query()
            ->select('vehicles.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images', 'reviews.user:id,name', 'user:id,name,email,phone_number'])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json($vehicle);
    }

    /**
     * SHOPS
     */
    public function getShops(Request $request)
    {
        $shops = Shop::query()
            ->select('shops.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->paginate(15);

        return response()->json($shops);
    }

    public function showShop($id)
    {
        $shop = Shop::query()
            ->select('shops.*')
            ->selectRaw("ST_X(coordinates::geometry) as longitude, ST_Y(coordinates::geometry) as latitude")
            ->with([
                'images', 
                'user:id,name,email,phone_number',
                // Load items and their primary images so the shop page looks like an e-commerce store
                'items.images' => fn($q) => $q->where('is_primary', true) 
            ])
            ->findOrFail($id);

        return response()->json($shop);
    }
}