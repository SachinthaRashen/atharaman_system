<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    /**
     * Rank spatial candidate entities across all types using FastAPI ML engine.
     */
    public function rankBundle(?User $user, Location $location, array $spatialCandidates): array
    {
        // 1. Format User profile to match FastAPI UserProfile schema
        $age = $user && $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : 30;

        $userPayload = [
            'user_id'                => $user ? $user->id : 0,
            'age'                    => (float) $age,
            'country'                => $user->country ?? 'Unknown',
            'native_language'        => $user->native_language ?? 'English',
            'preferred_travel_style' => $user->preferred_travel_style ?? 'adventure',
            'preferred_budget_tier'  => $user->preferred_budget_tier ?? 'mid_range',
            'prefers_guided_tours'   => (bool) ($user->prefers_guided_tours ?? false),
            'requires_accessibility' => (bool) ($user->requires_accessibility ?? false),
        ];

        // 2. Format Location Context to match FastAPI LocationContext schema
        $locationPayload = [
            'latitude'           => (float) ($location->latitude ?? 0.0),
            'longitude'          => (float) ($location->longitude ?? 0.0),
            'location_id'        => (int) $location->id,
            'name'               => $location->name,
            'location_type'      => $location->location_type,
            'terrain_difficulty' => $location->terrain_difficulty,
            'requires_4x4'       => (bool) ($location->requires_4x4 ?? false),
            'requires_guide'     => (bool) ($location->requires_guide ?? false),
            'elevation_meters'   => $location->elevation_meters ? (float) $location->elevation_meters : null,
        ];

        // 3. Normalize Eloquent models into a single flat list for FastAPI
        $candidatesPayload = $this->normalizeCandidates($spatialCandidates);

        if (empty($candidatesPayload)) {
            return [
                'hotels'     => [],
                'guides'     => [],
                'vehicles'   => [],
                'shop_items' => [],
            ];
        }

        // 4. Dispatch to FastAPI Engine
        $url = config('services.fastapi.url', 'http://127.0.0.1:8000') . '/recommendations/rank';

        try {
            $response = Http::timeout(5)->post($url, [
                'user'              => $userPayload,
                'candidates'        => $candidatesPayload,
                'selected_location' => $locationPayload,
            ]);

            if ($response->successful()) {
                $rankedList = $response->json('recommendations') ?? [];
                return $this->groupRankedResults($rankedList);
            }

            Log::error('FastAPI ML recommendation returned error', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
        } catch (\Throwable $e) {
            Log::error('FastAPI connection failure: ' . $e->getMessage());
        }

        // Fallback: Group unranked candidates directly if FastAPI fails
        return $this->groupRankedResults($candidatesPayload);
    }

    /**
     * Converts Eloquent models to arrays and formats them for FastAPI.
     */
    protected function normalizeCandidates(array $spatialData): array
    {
        $normalized = [];
        $groups = $spatialData['candidates'] ?? [];

        // 1. Map Hotels
        if (!empty($groups['hotels'])) {
            foreach ($groups['hotels'] as $hotel) {
                $candidate = $hotel->toArray(); 
                $candidate['item_type'] = 'hotels';
                $candidate['item_id'] = $hotel->id;
                $candidate['is_wheelchair_accessible'] = (bool) $hotel->is_wheelchair_accessible;
                $candidate['base_price'] = (float) $hotel->base_price;
                $normalized[] = $candidate;
            }
        }

        // 2. Map Guides
        if (!empty($groups['guides'])) {
            foreach ($groups['guides'] as $guide) {
                $candidate = $guide->toArray();
                $candidate['item_type'] = 'guides';
                $candidate['item_id'] = $guide->id;
                $candidate['daily_rate'] = (float) $guide->daily_rate;
                $normalized[] = $candidate;
            }
        }

        // 3. Map Vehicles
        if (!empty($groups['vehicles'])) {
            foreach ($groups['vehicles'] as $vehicle) {
                $candidate = $vehicle->toArray();
                $candidate['item_type'] = 'vehicles';
                $candidate['item_id'] = $vehicle->id;
                $candidate['rate_per_day'] = (float) $vehicle->rate_per_day;
                $candidate['rate_per_km'] = (float) $vehicle->rate_per_km;
                $normalized[] = $candidate;
            }
        }

        // 4. Map Shop Items (Nested inside Shops)
        if (!empty($groups['shops'])) {
            foreach ($groups['shops'] as $shop) {
                if ($shop->items) {
                    foreach ($shop->items as $item) {
                        $candidate = $item->toArray();
                        $candidate['item_type'] = 'shop_items';
                        $candidate['item_id'] = $item->id;
                        $candidate['rental_price_per_day'] = (float) $item->rental_price_per_day;
                        // Attach parent shop name for the UI
                        $candidate['shop_name'] = $shop->shop_name; 
                        $normalized[] = $candidate;
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * Groups a flat list of ranked items back into entity categories.
     */
    protected function groupRankedResults(array $rankedList): array
    {
        $grouped = [
            'hotels'     => [],
            'guides'     => [],
            'vehicles'   => [],
            'shop_items' => [],
        ];

        foreach ($rankedList as $item) {
            $type = $item['item_type'] ?? '';
            if (isset($grouped[$type])) {
                $grouped[$type][] = $item;
            }
        }

        return $grouped;
    }
}