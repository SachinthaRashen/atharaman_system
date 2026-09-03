<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RecommendationLog;
use App\Services\SpatialSearchService;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    public function __construct(
        protected SpatialSearchService $spatialSearchService,
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Generate dynamic, context-aware service bundle (Hotels, Guides, Vehicles, Shop Items).
     */
    public function generateBundle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id'        => 'required|exists:locations,id',
            'radius_meters'      => 'nullable|numeric|min:1000|max:50000',
            'group_size'         => 'nullable|integer|min:1',
            'trip_duration_days' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $location = Location::findOrFail($validated['location_id']);

        // 1. Spatial Pruning via PostGIS (Fetches hotels, guides, vehicles, shops within radius)
        $spatialData = $this->spatialSearchService->getNearbyCandidates(
            $location,
            $validated['radius_meters'] ?? 15000
        );

        // 2. Rank candidates across all 4 entity types via FastAPI Engine
        $rankedBundle = $this->recommendationService->rankBundle(
            $user,
            $location,
            $spatialData
        );

        // 3. Log recommendation event
        try {
            RecommendationLog::create([
                'user_id'               => $user->id,
                'location_id'           => $location->id,
                'generated_bundle_ids'  => [
                    'hotels'     => array_column($rankedBundle['hotels'], 'item_id'),
                    'guides'     => array_column($rankedBundle['guides'], 'item_id'),
                    'vehicles'   => array_column($rankedBundle['vehicles'], 'item_id'),
                    'shop_items' => array_column($rankedBundle['shop_items'], 'item_id'),
                ],
                'environmental_context' => [
                    'terrain_difficulty' => $location->terrain_difficulty,
                    'elevation_meters'   => $location->elevation_meters,
                    'requires_4x4'       => $location->requires_4x4,
                    'requires_guide'     => $location->requires_guide,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not log recommendation: ' . $e->getMessage());
        }

        // 4. Return ranked multi-entity bundle to frontend
        return response()->json([
            'success'  => true,
            'location' => [
                'id'                 => $location->id,
                'name'               => $location->name,
                'location_type'      => $location->location_type,
                'terrain_difficulty' => $location->terrain_difficulty,
            ],
            'bundle'   => $rankedBundle,
        ]);
    }
}