<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RecommendationLog;
use App\Services\SpatialSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    public function __construct(
        protected SpatialSearchService $spatialSearchService
    ) {}

    /**
     * Generate dynamic, context-aware service bundle.
     */
    public function generateBundle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'radius_meters' => 'nullable|numeric|min:1000|max:50000',
            'group_size' => 'nullable|integer|min:1',
            'trip_duration_days' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $location = Location::findOrFail($validated['location_id']);

        // 1. Spatial Pruning via PostGIS
        $spatialData = $this->spatialSearchService->getNearbyCandidates(
            $location,
            $validated['radius_meters'] ?? 15000
        );

        // 2. Prepare payload for FastAPI microservice
        $mlPayload = [
            'user' => [
                'id' => $user->id,
                'preferred_travel_style' => $user->preferred_travel_style,
                'preferred_budget_tier' => $user->preferred_budget_tier,
                'prefers_guided_tours' => $user->prefers_guided_tours,
                'requires_accessibility' => $user->requires_accessibility,
                'ml_cluster_id' => $user->ml_cluster_id,
            ],
            'context' => [
                'group_size' => $validated['group_size'] ?? 1,
                'trip_duration_days' => $validated['trip_duration_days'] ?? 1,
            ],
            'spatial_candidates' => $spatialData,
        ];

        // 3. Dispatch to FastAPI Engine
        $fastApiUrl = config('services.fastapi.url', 'http://127.0.0.1:8001');

        try {
            $response = Http::timeout(5)->post("{$fastApiUrl}/api/v1/recommend/bundle", $mlPayload);

            if ($response->successful()) {
                $bundleResult = $response->json();

                // 4. Log recommendation event asynchronously/directly
                RecommendationLog::create([
                    'user_id' => $user->id,
                    'location_id' => $location->id,
                    'generated_bundle_ids' => $bundleResult['bundle_ids'] ?? [],
                    'environmental_context' => $bundleResult['weather_context'] ?? [],
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $bundleResult,
                ]);
            }

            Log::error('FastAPI ML recommendation returned error', ['body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('FastAPI connection failure: ' . $e->getMessage());
        }

        // Fallback: return raw spatially sorted list if ML microservice is unreachable
        return response()->json([
            'success' => true,
            'fallback' => true,
            'message' => 'ML Engine unavailable; showing direct spatial proximity recommendations.',
            'data' => $spatialData['candidates'],
        ]);
    }
}