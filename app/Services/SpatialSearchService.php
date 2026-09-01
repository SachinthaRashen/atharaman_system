<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Guide;
use App\Models\Vehicle;
use App\Models\Shop;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class SpatialSearchService
{
    /**
     * Radius in meters (default: 15,000 m = 15 km)
     */
    protected float $defaultRadius = 15000.0;

    /**
     * Retrieve all spatially pruned service candidates around target coordinates.
     */
    public function getNearbyCandidates(Location $location, ?float $radiusMeters = null): array
    {
        $radius = $radiusMeters ?? $this->defaultRadius;

        // PostGIS point geometry reference from target location
        $targetPoint = "ST_SetSRID(ST_MakePoint(ST_X(coordinates::geometry), ST_Y(coordinates::geometry)), 4326)::geography";

        // 1. Spatially filter Hotels with primary images and average ratings
        $hotels = Hotel::query()
            ->select('hotels.*')
            ->selectRaw("ST_Distance(coordinates, (SELECT coordinates FROM locations WHERE id = ?)) as distance_meters", [$location->id])
            ->whereRaw("ST_DWithin(coordinates, (SELECT coordinates FROM locations WHERE id = ?), ?)", [$location->id, $radius])
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->withAvg('reviews', 'rating')
            ->orderBy('distance_meters')
            ->get();

        // 2. Filter Guides (via Pivot Table expertise first; Fallback to Spatial Coordinates)
        $guides = Guide::query()
            ->whereHas('locations', function ($query) use ($location) {
                $query->where('locations.id', $location->id);
            })
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->withAvg('reviews', 'rating')
            ->get();

        // Spatial fallback if no specialized guides exist on pivot
        if ($guides->isEmpty()) {
            $guides = Guide::query()
                ->select('guides.*')
                ->selectRaw("ST_Distance(coordinates, (SELECT coordinates FROM locations WHERE id = ?)) as distance_meters", [$location->id])
                ->whereRaw("ST_DWithin(coordinates, (SELECT coordinates FROM locations WHERE id = ?), ?)", [$location->id, $radius])
                ->with(['images' => fn($q) => $q->where('is_primary', true)])
                ->withAvg('reviews', 'rating')
                ->orderBy('distance_meters')
                ->get();
        }

        // 3. Spatially filter Vehicles (filtered by terrain constraint if location requires 4x4)
        $vehiclesQuery = Vehicle::query()
            ->select('vehicles.*')
            ->selectRaw("ST_Distance(coordinates, (SELECT coordinates FROM locations WHERE id = ?)) as distance_meters", [$location->id])
            ->whereRaw("ST_DWithin(coordinates, (SELECT coordinates FROM locations WHERE id = ?), ?)", [$location->id, $radius])
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->withAvg('reviews', 'rating');

        if ($location->requires_4x4) {
            $vehiclesQuery->where('terrain_capability', 'off_road_4x4');
        }

        $vehicles = $vehiclesQuery->orderBy('distance_meters')->get();

        // 4. Spatially filter Shops & their active Shop Items
        $shops = Shop::query()
            ->select('shops.*')
            ->selectRaw("ST_Distance(coordinates, (SELECT coordinates FROM locations WHERE id = ?)) as distance_meters", [$location->id])
            ->whereRaw("ST_DWithin(coordinates, (SELECT coordinates FROM locations WHERE id = ?), ?)", [$location->id, $radius])
            ->with(['items.images' => fn($q) => $q->where('is_primary', true)])
            ->orderBy('distance_meters')
            ->get();

        return [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'location_type' => $location->location_type,
                'terrain_difficulty' => $location->terrain_difficulty,
                'requires_guide' => $location->requires_guide,
                'requires_4x4' => $location->requires_4x4,
                'elevation_meters' => $location->elevation_meters,
            ],
            'candidates' => [
                'hotels' => $hotels,
                'guides' => $guides,
                'vehicles' => $vehicles,
                'shops' => $shops,
            ]
        ];
    }
}