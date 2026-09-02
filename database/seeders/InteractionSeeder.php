<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Vehicle;
use App\Models\Guide;
use App\Models\Shop;
use App\Models\ShopItem;
use App\Models\Review;
use App\Models\UserInteraction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InteractionSeeder extends Seeder
{
    public function run(): void
    {
        UserInteraction::truncate();
        Review::truncate();

        $tourists = User::where('role', 'tourist')->get();
        
        $hotels = Hotel::all()->groupBy('budget_tier');
        $locations = Location::all();
        $vehicles = Vehicle::all()->groupBy('vehicle_type');
        $guides = Guide::all();
        $shops = Shop::with('items')->get(); // Eager load items

        foreach ($tourists as $tourist) {
            // ---------------------------------------------------------
            // 1. HOTEL INTERACTIONS (60% Phone, 20% Bookmark, 15% WhatsApp, 5% Email)
            // ---------------------------------------------------------
            $preferredHotels = $hotels->get($tourist->preferred_budget_tier) ?? collect();
            
            foreach ($preferredHotels->random(min(4, $preferredHotels->count())) as $hotel) {
                UserInteraction::updateOrCreate([
                    'user_id' => $tourist->id,
                    'interactable_id' => $hotel->id,
                    'interactable_type' => Hotel::class,
                    'interaction_type' => $this->getWeightedInteraction([
                        'phone_revealed' => 60, 'bookmarked' => 20, 'whatsapp_clicked' => 15, 'email_clicked' => 5
                    ]),
                ]);

                Review::create([
                    'user_id' => $tourist->id,
                    'reviewable_id' => $hotel->id,
                    'reviewable_type' => Hotel::class,
                    'rating' => rand(4, 5),
                    'comment' => 'Perfect accommodation for my travel style!',
                ]);
            }

            // Cross-Pollination (Negative ML feedback)
            if ($tourist->preferred_budget_tier === 'budget' && $hotels->has('luxury')) {
                $mismatchedHotel = $hotels->get('luxury')->random();
                Review::create([
                    'user_id' => $tourist->id,
                    'reviewable_id' => $mismatchedHotel->id,
                    'reviewable_type' => Hotel::class,
                    'rating' => rand(1, 2),
                    'comment' => 'Way too expensive. Not worth the price tag.',
                ]);
            }

            // ---------------------------------------------------------
            // 2. LOCATION INTERACTIONS (100% Bookmarks)
            // ---------------------------------------------------------
            $targetLocationTypes = match($tourist->preferred_travel_style) {
                'adventure' => ['mountain_trek', 'campsite', 'waterfall', 'beach_coastal'],
                'cultural_historic' => ['ancient_ruins', 'religious_site', 'urban_city'],
                'nature_wildlife' => ['wildlife_safari', 'rainforest', 'botanical_garden'],
                'leisure_wellness' => ['beach_coastal', 'tea_estate', 'lake_reservoir'],
                default => ['urban_city']
            };

            $matchedLocations = clone $locations;
            $matchedLocations = $matchedLocations->whereIn('location_type', $targetLocationTypes)->random(min(3, $matchedLocations->count()));

            foreach ($matchedLocations as $location) {
                UserInteraction::updateOrCreate([
                    'user_id' => $tourist->id,
                    'interactable_id' => $location->id,
                    'interactable_type' => Location::class,
                    'interaction_type' => 'bookmarked',
                ]);

                Review::create([
                    'user_id' => $tourist->id,
                    'reviewable_id' => $location->id,
                    'reviewable_type' => Location::class,
                    'rating' => rand(4, 5),
                    'comment' => 'Absolutely breathtaking scenery.',
                ]);
            }

            // ---------------------------------------------------------
            // 3. GUIDE INTERACTIONS (75% Phone, 10% Bookmark, 12% WhatsApp, 3% Email)
            // ---------------------------------------------------------
            if ($tourist->prefers_guided_tours && $guides->count() > 0) {
                $selectedGuides = $guides->random(min(2, $guides->count()));
                foreach ($selectedGuides as $guide) {
                    UserInteraction::updateOrCreate([
                        'user_id' => $tourist->id,
                        'interactable_id' => $guide->id,
                        'interactable_type' => Guide::class,
                        'interaction_type' => $this->getWeightedInteraction([
                            'phone_revealed' => 75, 'whatsapp_clicked' => 12, 'bookmarked' => 10, 'email_clicked' => 3
                        ]),
                    ]);

                    Review::create([
                        'user_id' => $tourist->id,
                        'reviewable_id' => $guide->id,
                        'reviewable_type' => Guide::class,
                        'rating' => 5,
                        'comment' => 'Very knowledgeable and friendly!',
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 4. VEHICLE INTERACTIONS (75% Phone, 13% Bookmark, 10% WhatsApp, 2% Email)
            // ---------------------------------------------------------
            $targetVehicleType = ($tourist->preferred_budget_tier === 'budget') 
                ? ['scooter', 'tuk_tuk', 'motorbike'] 
                : ['sedan_car', 'suv_4x4', 'passenger_van'];

            $matchedVehicles = clone $vehicles;
            $matchedVehicles = $matchedVehicles->flatten()->whereIn('vehicle_type', $targetVehicleType);
            
            if ($matchedVehicles->count() > 0) {
                foreach ($matchedVehicles->random(min(2, $matchedVehicles->count())) as $vehicle) {
                    UserInteraction::updateOrCreate([
                        'user_id' => $tourist->id,
                        'interactable_id' => $vehicle->id,
                        'interactable_type' => Vehicle::class,
                        'interaction_type' => $this->getWeightedInteraction([
                            'phone_revealed' => 75, 'bookmarked' => 13, 'whatsapp_clicked' => 10, 'email_clicked' => 2
                        ]),
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 5. SHOP & SHOP ITEM INTERACTIONS (Item = 100% Bookmark | Shop = 60% Phone, 35% WA, 5% Email)
            // ---------------------------------------------------------
            if ($shops->count() > 0) {
                $shop = $shops->random();
                
                // 1. Bookmark a specific item from the shop (if inventory exists)
                if ($shop->items->count() > 0) {
                    $item = $shop->items->random();
                    UserInteraction::updateOrCreate([
                        'user_id' => $tourist->id,
                        'interactable_id' => $item->id,
                        'interactable_type' => ShopItem::class,
                        'interaction_type' => 'bookmarked',
                    ]);
                }

                // 2. Contact the shop owner to rent the gear
                UserInteraction::updateOrCreate([
                    'user_id' => $tourist->id,
                    'interactable_id' => $shop->id,
                    'interactable_type' => Shop::class,
                    'interaction_type' => $this->getWeightedInteraction([
                        'phone_revealed' => 60, 'whatsapp_clicked' => 35, 'email_clicked' => 5
                    ]),
                ]);
            }
        }
    }

    /**
     * Helper to return an interaction type based on percentage weights.
     */
    private function getWeightedInteraction(array $distributions): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($distributions as $type => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return 'bookmarked'; // Safe fallback
    }
}