<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        Shop::query()->delete();

        $shops = [
            // ================= ELLA & HIGHLANDS (Hiking, Trekking, General) =================
            [
                'owner_name' => 'Dinuka Liyanage',
                'owner_email' => 'dinuka.liyanage@example.com',
                'shop_name' => 'Ella Alpine Gear Rentals',
                'address' => 'Passara Road, Ella',
                'contact_number' => '+94772233445',
                'whatsapp_number' => '+94772233445',
                'latitude' => 6.8710,
                'longitude' => 81.0480,
                'items' => [
                    ['item_name' => '2-Person Camping Tent', 'item_category' => 'camping_gear', 'description' => 'Waterproof dome tent, easy setup.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 10],
                    ['item_name' => 'Trekking Poles (Pair)', 'item_category' => 'hiking_trekking', 'description' => 'Adjustable aluminum walking sticks.', 'rental_price_per_day' => 500.00, 'stock_quantity' => 15],
                    ['item_name' => 'Winter Sleeping Bag', 'item_category' => 'camping_gear', 'description' => 'Thermal sleeping bag suitable for mountain climates.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                    ['item_name' => 'GoPro Hero 11 Black', 'item_category' => 'general_travel', 'description' => 'Action camera with head mount for trail recording.', 'rental_price_per_day' => 4500.00, 'stock_quantity' => 4],
                    ['item_name' => 'Portable Power Bank (20,000mAh)', 'item_category' => 'general_travel', 'description' => 'High-capacity power bank to keep gadgets charged on long hikes.', 'rental_price_per_day' => 350.00, 'stock_quantity' => 30],
                    ['item_name' => 'Portable 4G Wi-Fi Router', 'item_category' => 'general_travel', 'description' => 'Pocket router with unlimited daily data.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                    ['item_name' => 'Rechargeable Hand Warmer', 'item_category' => 'general_travel', 'description' => 'Compact, USB-rechargeable hand warmer for cold mornings.', 'rental_price_per_day' => 250.00, 'stock_quantity' => 18],
                ]
            ],
            [
                'owner_name' => 'Malintha Peiris',
                'owner_email' => 'malintha.peiris@example.com',
                'shop_name' => 'Ravana Outdoor Supplies',
                'address' => 'Wellawaya Road, Ella',
                'contact_number' => '+94719988776',
                'whatsapp_number' => '+94719988776',
                'latitude' => 6.8650,
                'longitude' => 81.0550,
                'items' => [
                    ['item_name' => '65L Hiking Backpack', 'item_category' => 'hiking_trekking', 'description' => 'Large capacity waterproof backpack.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 8],
                    ['item_name' => 'High-Capacity Power Bank', 'item_category' => 'general_travel', 'description' => '20000mAh portable charger for long hikes.', 'rental_price_per_day' => 400.00, 'stock_quantity' => 25],
                    ['item_name' => '6-Speed Mountain Bike', 'item_category' => 'general_travel', 'description' => 'Rugged MTB ideal for exploring tea trails around Ella.', 'rental_price_per_day' => 2500.00, 'stock_quantity' => 8],
                    ['item_name' => 'Winter Sleeping Bag', 'item_category' => 'camping_gear', 'description' => 'Thermal sleeping bag suitable for mountain climates.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                    ['item_name' => 'Portable 4G Wi-Fi Router', 'item_category' => 'general_travel', 'description' => 'Pocket router with unlimited daily data.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                ]
            ],

            // ================= SOUTH COAST (Surf, Dive, Travel) =================
            [
                'owner_name' => 'Isuru Madushan',
                'owner_email' => 'isuru.madushan@example.com',
                'shop_name' => 'Mirissa Wave Riders Board Shop',
                'address' => 'Beach Road, Mirissa',
                'contact_number' => '+94718877665',
                'whatsapp_number' => '+94718877665',
                'latitude' => 5.9465,
                'longitude' => 80.4580,
                'items' => [
                    ['item_name' => 'Beginner Foam Surfboard (8ft)', 'item_category' => 'water_sports', 'description' => 'Perfect for learning to surf on gentle waves.', 'rental_price_per_day' => 2000.00, 'stock_quantity' => 12],
                    ['item_name' => 'Pro Fiberglass Shortboard (6ft)', 'item_category' => 'water_sports', 'description' => 'Fast and agile board for experienced surfers only.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 5],
                    ['item_name' => 'Snorkeling Mask & Fins Set', 'item_category' => 'water_sports', 'description' => 'High-visibility mask for exploring coral reefs.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 30],
                    ['item_name' => 'Inflatable Stand Up Paddleboard', 'item_category' => 'water_sports', 'description' => 'Complete SUP kit with pump and paddle, great for calm lagoons.', 'rental_price_per_day' => 3000.00, 'stock_quantity' => 8],
                    ['item_name' => 'Professional Dive Mask & Snorkel', 'item_category' => 'water_sports', 'description' => 'Low-volume silicone mask with dry-top snorkel for clear underwater visibility.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 15],
                    ['item_name' => 'Waterproof Action Camera', 'item_category' => 'general_travel', 'description' => 'Waterproof camera suitable for diving and snorkeling activities.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 8],
                    ['item_name' => 'Polarized Sunglasses', 'item_category' => 'general_travel', 'description' => 'Anti-glare sunglasses for bright beach days.', 'rental_price_per_day' => 250.00, 'stock_quantity' => 25],
                ]
            ],
            [
                'owner_name' => 'Ruwan Kumara',
                'owner_email' => 'ruwan.kumara@example.com',
                'shop_name' => 'Unawatuna Beach Essentials',
                'address' => 'Wella Dewalaya Road, Unawatuna',
                'contact_number' => '+94775566778',
                'whatsapp_number' => '+94775566778',
                'latitude' => 6.0120,
                'longitude' => 80.2480,
                'items' => [
                    ['item_name' => 'Large Beach Umbrella', 'item_category' => 'general_travel', 'description' => 'UV protection beach umbrella with sand anchor.', 'rental_price_per_day' => 600.00, 'stock_quantity' => 20],
                    ['item_name' => 'Stand Up Paddleboard (SUP)', 'item_category' => 'water_sports', 'description' => 'Inflatable SUP with paddle and ankle leash.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 5],
                    ['item_name' => 'DJI Mini 3 Pro Drone', 'item_category' => 'general_travel', 'description' => 'Lightweight drone for capturing cinematic coastal footage.', 'rental_price_per_day' => 8500.00, 'stock_quantity' => 2],
                    ['item_name' => 'Photography Tripod (Travel Size)', 'item_category' => 'general_travel', 'description' => 'Compact carbon fiber tripod for stable landscape shots.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 10],
                    ['item_name' => 'Portable Bluetooth Speaker', 'item_category' => 'general_travel', 'description' => 'Water-resistant speaker for beach relaxation.', 'rental_price_per_day' => 500.00, 'stock_quantity' => 15],
                    ['item_name' => 'Inflatable Beach Sofa', 'item_category' => 'general_travel', 'description' => 'Comfortable air sofa for lounging on the sand.', 'rental_price_per_day' => 1000.00, 'stock_quantity' => 12],
                    ['item_name' => 'Dry Bag Backpack (20L)', 'item_category' => 'water_sports', 'description' => 'Waterproof backpack to keep electronics safe near the water.', 'rental_price_per_day' => 600.00, 'stock_quantity' => 20],
                    ['item_name' => 'Scuba Diving Mask & Snorkel Set', 'item_category' => 'water_sports', 'description' => 'Professional-grade diving gear for underwater exploration.', 'rental_price_per_day' => 1800.00, 'stock_quantity' => 8],
                    ['item_name' => 'Full-Face Snorkeling Mask', 'item_category' => 'water_sports', 'description' => 'Advanced mask providing 180-degree views without fog.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 12],
                    ['item_name' => 'Inflatable Kayak (2-Person)', 'item_category' => 'water_sports', 'description' => 'Durable inflatable kayak with paddles and pump for coastal trips.', 'rental_price_per_day' => 5000.00, 'stock_quantity' => 3],
                ]
            ],

            // ================= WILDERNESS & EXTREME (Knuckles / Yala) =================
            [
                'owner_name' => 'Sampath Ekanayake',
                'owner_email' => 'sampath.ekanayake@example.com',
                'shop_name' => 'Meemure Survival Outpost',
                'address' => 'Meemure Village Center, Knuckles Range',
                'contact_number' => '+94762233441',
                'whatsapp_number' => '+94762233441',
                'latitude' => 7.4335,
                'longitude' => 80.8455,
                'items' => [
                    ['item_name' => 'Heavy-Duty 6-Person Tent', 'item_category' => 'camping_gear', 'description' => 'Spacious family/group tent built for rough weather.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 4],
                    ['item_name' => 'Portable Butane Camp Stove', 'item_category' => 'camping_gear', 'description' => 'Compact burner for cooking in the wild (gas not included).', 'rental_price_per_day' => 900.00, 'stock_quantity' => 10],
                    ['item_name' => 'High-Lumen Headlamp', 'item_category' => 'general_travel', 'description' => 'Essential for night trekking and cave exploration.', 'rental_price_per_day' => 400.00, 'stock_quantity' => 15],
                    ['item_name' => 'Portable BBQ Grill Set', 'item_category' => 'camping_gear', 'description' => 'Compact folding grill with charcoal starter.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 8],
                    ['item_name' => 'Electric Cool Box (40L)', 'item_category' => 'camping_gear', 'description' => '12V electric cooler to keep food and drinks chilled.', 'rental_price_per_day' => 2500.00, 'stock_quantity' => 12],
                    ['item_name' => 'Portable Solar Power Bank (25000mAh)', 'item_category' => 'general_travel', 'description' => 'High-capacity solar power bank with USB outputs, ideal for off-grid camping.', 'rental_price_per_day' => 700.00, 'stock_quantity' => 15],
                    ['item_name' => 'Portable 4G Wi-Fi Router', 'item_category' => 'general_travel', 'description' => 'Pocket router with unlimited daily data.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                ]
            ],
            [
                'owner_name' => 'Nalin Jayatilleke',
                'owner_email' => 'nalin.jayatilleke@example.com',
                'shop_name' => 'Yala Bushcraft Supplies',
                'address' => 'Palatupana Junction, Yala',
                'contact_number' => '+94713332211',
                'whatsapp_number' => '+94713332211',
                'latitude' => 6.2850,
                'longitude' => 81.3900,
                'items' => [
                    ['item_name' => 'Binoculars (10x50)', 'item_category' => 'general_travel', 'description' => 'High-magnification binoculars for wildlife spotting.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 12],
                    ['item_name' => 'Camouflage Tarp & Rain Cover', 'item_category' => 'camping_gear', 'description' => 'Heavy-duty waterproof tarp for safari camping.', 'rental_price_per_day' => 700.00, 'stock_quantity' => 20],
                    ['item_name' => 'Sony 200-600mm Telephoto Lens', 'item_category' => 'general_travel', 'description' => 'E-Mount lens for professional wildlife photography.', 'rental_price_per_day' => 7500.00, 'stock_quantity' => 3],
                    ['item_name' => 'Canon EOS R5 Body', 'item_category' => 'general_travel', 'description' => 'Full-frame mirrorless camera body (lens rented separately).', 'rental_price_per_day' => 12000.00, 'stock_quantity' => 2],
                    ['item_name' => 'Portable Power Station (500Wh)', 'item_category' => 'camping_gear', 'description' => 'Solar-compatible power bank for charging equipment in the wild.', 'rental_price_per_day' => 4000.00, 'stock_quantity' => 8],
                    ['item_name' => 'Safari-Grade Sleeping Bag', 'item_category' => 'camping_gear', 'description' => 'Comfort-rated 0°C sleeping bag for jungle stays.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 15],
                    ['item_name' => 'Hammock with Mosquito Net', 'item_category' => 'camping_gear', 'description' => 'Lightweight jungle hammock for comfortable sleeping.', 'rental_price_per_day' => 600.00, 'stock_quantity' => 20],
                ]
            ],

            // ================= EAST COAST (Surf & Dive) =================
            [
                'owner_name' => 'Rashid Farook',
                'owner_email' => 'rashid.farook@example.com',
                'shop_name' => 'Arugam Bay Surf Shack',
                'address' => 'Main Street, Arugam Bay',
                'contact_number' => '+94775566779',
                'whatsapp_number' => '+94775566779',
                'latitude' => 6.8425,
                'longitude' => 81.8270,
                'items' => [
                    ['item_name' => 'Fiberglass Shortboard (6ft)', 'item_category' => 'water_sports', 'description' => 'High-performance board for experienced surfers.', 'rental_price_per_day' => 2500.00, 'stock_quantity' => 8],
                    ['item_name' => 'Longboard (9ft)', 'item_category' => 'water_sports', 'description' => 'Classic longboard for point breaks.', 'rental_price_per_day' => 3000.00, 'stock_quantity' => 6],
                    ['item_name' => 'Electric Bicycle', 'item_category' => 'general_travel', 'description' => 'Fat-tire e-bike with a surf rack to reach remote spots.', 'rental_price_per_day' => 4500.00, 'stock_quantity' => 4],
                    ['item_name' => 'Waterproof Action Camera', 'item_category' => 'general_travel', 'description' => 'Waterproof camera suitable for diving and snorkeling activities.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 8],
                    ['item_name' => 'Inflatable Stand Up Paddleboard', 'item_category' => 'water_sports', 'description' => 'Complete SUP kit with pump and paddle, great for calm lagoons.', 'rental_price_per_day' => 3000.00, 'stock_quantity' => 8],
                    ['item_name' => 'Polarized Sunglasses', 'item_category' => 'general_travel', 'description' => 'Anti-glare sunglasses for bright beach days.', 'rental_price_per_day' => 250.00, 'stock_quantity' => 25],
                    ['item_name' => 'Dry Bag Backpack (20L)', 'item_category' => 'water_sports', 'description' => 'Waterproof backpack to keep electronics safe near the water.', 'rental_price_per_day' => 600.00, 'stock_quantity' => 20],
                ]
            ],
            [
                'owner_name' => 'Kugathasan Raj',
                'owner_email' => 'kugathasan.raj@example.com',
                'shop_name' => 'Nilaveli Dive & Swim',
                'address' => 'Nilaveli Beach Road, Trincomalee',
                'contact_number' => '+94762221188',
                'whatsapp_number' => '+94762221188',
                'latitude' => 8.6850,
                'longitude' => 81.1850,
                'items' => [
                    ['item_name' => 'Full Scuba Gear Set', 'item_category' => 'water_sports', 'description' => 'Includes BCD, regulator, wetsuit, and fins (tank separate).', 'rental_price_per_day' => 6500.00, 'stock_quantity' => 10],
                    ['item_name' => 'Waterproof Dry Bag (20L)', 'item_category' => 'general_travel', 'description' => 'Keeps valuables safe during boat rides to Pigeon Island.', 'rental_price_per_day' => 450.00, 'stock_quantity' => 25],
                    ['item_name' => 'Underwater Camera Housing', 'item_category' => 'general_travel', 'description' => 'Universal waterproof housing for smartphones.', 'rental_price_per_day' => 1000.00, 'stock_quantity' => 15],
                    ['item_name' => 'Inflatable Stand-Up Paddleboard', 'item_category' => 'water_sports', 'description' => 'Complete SUP kit with pump and paddle.', 'rental_price_per_day' => 3500.00, 'stock_quantity' => 6],
                    ['item_name' => 'Snorkeling Set (Mask, Snorkel, Fins)', 'item_category' => 'water_sports', 'description' => 'Budget-friendly set for casual snorkeling.', 'rental_price_per_day' => 500.00, 'stock_quantity' => 30],
                    ['item_name' => 'Polarized Sunglasses', 'item_category' => 'general_travel', 'description' => 'Anti-glare sunglasses for bright beach days.', 'rental_price_per_day' => 250.00, 'stock_quantity' => 25],
                ]
            ],

            // ================= CULTURAL TRIANGLE & NORTH =================
            [
                'owner_name' => 'Chinthana Bandara',
                'owner_email' => 'chinthana.bandara@example.com',
                'shop_name' => 'Lion Rock Hiking Post',
                'address' => 'Inamaluwa Road, Sigiriya',
                'contact_number' => '+94714443322',
                'whatsapp_number' => '+94714443322',
                'latitude' => 7.9550,
                'longitude' => 80.7550,
                'items' => [
                    ['item_name' => 'Daypack (25L)', 'item_category' => 'hiking_trekking', 'description' => 'Lightweight backpack for carrying water and cameras up the rock.', 'rental_price_per_day' => 600.00, 'stock_quantity' => 15],
                    ['item_name' => 'City Bicycle', 'item_category' => 'general_travel', 'description' => 'Comfortable bike with a basket, perfect for navigating the ancient ruins.', 'rental_price_per_day' => 1000.00, 'stock_quantity' => 25],
                    ['item_name' => 'Portable 4G Wi-Fi Router', 'item_category' => 'general_travel', 'description' => 'Pocket router with unlimited daily data.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                    ['item_name' => 'Professional Camera Tripod', 'item_category' => 'general_travel', 'description' => 'Stable tripod for photography and videography.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 10],
                ]
            ],
            [
                'owner_name' => 'Subramaniam Vithiya',
                'owner_email' => 'subramaniam.v@example.com',
                'shop_name' => 'Jaffna Travel Logistics',
                'address' => 'Hospital Road, Jaffna',
                'contact_number' => '+94778899112',
                'whatsapp_number' => '+94778899112',
                'latitude' => 9.6680,
                'longitude' => 80.0200,
                'items' => [
                    ['item_name' => 'Travel Umbrella', 'item_category' => 'general_travel', 'description' => 'Compact umbrella for sun and sudden rain.', 'rental_price_per_day' => 150.00, 'stock_quantity' => 30],
                    ['item_name' => 'Portable 4G Wi-Fi Router', 'item_category' => 'general_travel', 'description' => 'Pocket router with unlimited daily data.', 'rental_price_per_day' => 800.00, 'stock_quantity' => 20],
                ]
            ],

            // ================= HIGHLANDS (Nuwara Eliya / Horton Plains) =================
            [
                'owner_name' => 'Sarath Weerakoon',
                'owner_email' => 'sarath.weerakoon@example.com',
                'shop_name' => 'Highland Camping Depot',
                'address' => 'Gregory Lake Road, Nuwara Eliya',
                'contact_number' => '+94715566778',
                'whatsapp_number' => '+94715566778',
                'latitude' => 6.9680,
                'longitude' => 80.7680,
                'items' => [
                    ['item_name' => 'Extreme Cold Sleeping Bag', 'item_category' => 'camping_gear', 'description' => 'Rated for sub-zero temperatures.', 'rental_price_per_day' => 1200.00, 'stock_quantity' => 15],
                    ['item_name' => 'Thermal Jacket', 'item_category' => 'hiking_trekking', 'description' => 'Windproof and insulated for Horton Plains mornings.', 'rental_price_per_day' => 1000.00, 'stock_quantity' => 20],
                    ['item_name' => 'Insulated Hiking Boots', 'item_category' => 'hiking_trekking', 'description' => 'Waterproof and warm boots for muddy trails.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 20],
                ]
            ],
            [
                'owner_name' => 'Gayantha Silva',
                'owner_email' => 'gayantha.silva@example.com',
                'shop_name' => 'Haputale Trail Outfitters',
                'address' => 'Station Road, Haputale',
                'contact_number' => '+94779988221',
                'whatsapp_number' => '+94779988221',
                'latitude' => 6.7680,
                'longitude' => 80.9500,
                'items' => [
                    ['item_name' => 'Hiking Boots (Various Sizes)', 'item_category' => 'hiking_trekking', 'description' => 'Sturdy, ankle-supporting boots for tea estate trails.', 'rental_price_per_day' => 1500.00, 'stock_quantity' => 30],
                    ['item_name' => 'First Aid Kit (Comprehensive)', 'item_category' => 'general_travel', 'description' => 'Fully stocked kit for minor trail injuries.', 'rental_price_per_day' => 300.00, 'stock_quantity' => 10],
                ]
            ]
        ];

        foreach ($shops as $data) {
            $owner = User::updateOrCreate(
                ['email' => $data['owner_email']],
                [
                    'name' => $data['owner_name'],
                    'password' => Hash::make('password123'),
                    'role' => 'shop_owner',
                    'email_verified_at' => now(),
                ]
            );

            $items = $data['items'];
            $lat = $data['latitude'];
            $lng = $data['longitude'];

            unset($data['owner_name'], $data['owner_email'], $data['latitude'], $data['longitude'], $data['items']);

            $shop = tap(new Shop($data), function ($s) use ($owner, $lat, $lng) {
                $s->user_id = $owner->id;
                $s->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
                $s->save();
            });

            foreach ($items as $itemData) {
                $shop->items()->create($itemData);
            }
        }
    }
}