<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // ================= ELLA HUB =================
            [
                'name' => 'Nine Arches Bridge',
                'description' => 'A massive colonial-era railway viaduct built entirely of brick, rock, and cement without a single piece of steel. Surrounded by lush jungle and tea fields, it is a marvel of early 20th-century engineering and a favorite spot for photography.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'ancient_ruins',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 1041,
                'latitude' => 6.8767,
                'longitude' => 81.0606,
                'images' => []
            ],
            [
                'name' => 'Little Adam\'s Peak',
                'description' => 'A moderate, incredibly scenic hike through rolling tea estates leading to a spectacular viewpoint. It offers sweeping views of the Ella Gap and is perfect for casual hikers and sunset watchers.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'mountain_trek',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 1141,
                'latitude' => 6.8742,
                'longitude' => 81.0601,
                'images' => []
            ],
            [
                'name' => 'Ella Rock',
                'description' => 'A challenging, steep hike that takes travelers along railway tracks and up through dense eucalyptus forests. The summit rewards trekkers with one of the highest and most dramatic panoramic views in the central highlands.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'mountain_trek',
                'terrain_difficulty' => 'challenging',
                'requires_4x4' => false,
                'requires_guide' => true, 
                'elevation_meters' => 1350,
                'latitude' => 6.8617,
                'longitude' => 81.0450,
                'images' => []
            ],
            [
                'name' => 'Ravana Falls',
                'description' => 'A stunning, tiered waterfall cascading down an oval-shaped concave rock outcrop. Located right by the main road, it is tied to the ancient Ramayana epic and is one of the widest falls in the country.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'waterfall',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 850,
                'latitude' => 6.8411,
                'longitude' => 81.0549,
                'images' => []
            ],

            // ================= SOUTH COAST HUB =================
            [
                'name' => 'Mirissa Beach',
                'description' => 'A breathtaking crescent of golden sand famous for surfing, beachfront seafood cafes, and acting as the main harbor for blue whale watching expeditions in the Indian Ocean.',
                'province' => 'Southern Province',
                'district' => 'Matara',
                'location_type' => 'beach_coastal',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 5,
                'latitude' => 5.9470,
                'longitude' => 80.4572,
                'images' => []
            ],
            [
                'name' => 'Coconut Tree Hill',
                'description' => 'A picturesque, dome-shaped coastal headland covered entirely in soaring palm trees. It juts out into the ocean, providing uninterrupted panoramic views of the crashing waves and spectacular sunsets.',
                'province' => 'Southern Province',
                'district' => 'Matara',
                'location_type' => 'beach_coastal',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 15,
                'latitude' => 5.9427,
                'longitude' => 80.4633,
                'images' => []
            ],
            [
                'name' => 'Yala National Park',
                'description' => 'A sprawling wilderness of dry woodland and open patches of grasslands. It is globally renowned for possessing one of the highest densities of leopards in the world, alongside elephants and sloth bears.',
                'province' => 'Southern Province',
                'district' => 'Hambantota',
                'location_type' => 'wildlife_safari',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => true,
                'requires_guide' => true,
                'elevation_meters' => 30,
                'latitude' => 6.3683,
                'longitude' => 81.5186,
                'images' => []
            ],
            [
                'name' => 'Galle Dutch Fort',
                'description' => 'A UNESCO World Heritage site, this massive 16th-century coastal fort is a living historical monument. Its cobblestone streets are lined with Dutch-colonial buildings, boutique shops, and cafes overlooking the ocean.',
                'province' => 'Southern Province',
                'district' => 'Galle',
                'location_type' => 'urban_city',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 10,
                'latitude' => 6.0270,
                'longitude' => 80.2139,
                'images' => []
            ],

            // ================= CULTURAL TRIANGLE =================
            [
                'name' => 'Sigiriya Lion Rock',
                'description' => 'An ancient 5th-century rock fortress and palace ruin. Visitors ascend through giant carved lion paws to reach the summit, which features water gardens and 360-degree views of the jungle.',
                'province' => 'Central Province',
                'district' => 'Matale',
                'location_type' => 'ancient_ruins',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 349,
                'latitude' => 7.9570,
                'longitude' => 80.7603,
                'images' => []
            ],
            [
                'name' => 'Pidurangala Rock',
                'description' => 'A massive rock formation directly opposite Sigiriya. Hiking to the top is physically demanding and involves rock scrambling, but it offers the absolute best vantage point to view the Sigiriya Lion Rock itself.',
                'province' => 'Central Province',
                'district' => 'Matale',
                'location_type' => 'mountain_trek',
                'terrain_difficulty' => 'challenging',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 340,
                'latitude' => 7.9650,
                'longitude' => 80.7630,
                'images' => []
            ],
            [
                'name' => 'Minneriya National Park',
                'description' => 'A dry-season feeding ground that hosts "The Gathering," where hundreds of wild Asian elephants congregate around the Minneriya tank. A spectacular wildlife phenomenon.',
                'province' => 'North Central Province',
                'district' => 'Polonnaruwa',
                'location_type' => 'wildlife_safari',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => true,
                'requires_guide' => true,
                'elevation_meters' => 100,
                'latitude' => 8.0347,
                'longitude' => 80.8258,
                'images' => []
            ],
            [
                'name' => 'Dambulla Royal Cave Temple',
                'description' => 'The largest and best-preserved cave temple complex in Sri Lanka. The rock towers 160 meters over the plains, housing ancient Buddhist murals and over 150 stunning statues inside five distinct cave shrines.',
                'province' => 'Central Province',
                'district' => 'Matale',
                'location_type' => 'religious_site',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 350,
                'latitude' => 7.8566,
                'longitude' => 80.6485,
                'images' => []
            ],

            // ================= CENTRAL & FORESTS =================
            [
                'name' => 'Meemure Village (Knuckles)',
                'description' => 'One of the most remote villages in Sri Lanka, nestled deep within the Knuckles Mountain Range. Access requires navigating extremely rugged off-road terrain. A sanctuary for hardcore trekkers.',
                'province' => 'Central Province',
                'district' => 'Kandy',
                'location_type' => 'village_getaway',
                'terrain_difficulty' => 'extreme',
                'requires_4x4' => true,
                'requires_guide' => true,
                'elevation_meters' => 1200,
                'latitude' => 7.4333,
                'longitude' => 80.8461,
                'images' => []
            ],
            [
                'name' => 'Temple of the Sacred Tooth Relic',
                'description' => 'Located in the royal palace complex of the former Kingdom of Kandy, this deeply venerated Buddhist temple houses the relic of the tooth of the Buddha and features intricate Kandyan architecture.',
                'province' => 'Central Province',
                'district' => 'Kandy',
                'location_type' => 'religious_site',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 500,
                'latitude' => 7.2936,
                'longitude' => 80.6413,
                'images' => []
            ],
            [
                'name' => 'Sinharaja Forest Reserve',
                'description' => 'A UNESCO World Heritage site and the last viable area of primary tropical rainforest in Sri Lanka. It is a treasure trove of endemic species, towering trees, and hidden waterfalls.',
                'province' => 'Sabaragamuwa Province',
                'district' => 'Ratnapura',
                'location_type' => 'rainforest',
                'terrain_difficulty' => 'challenging',
                'requires_4x4' => true,
                'requires_guide' => true,
                'elevation_meters' => 500,
                'latitude' => 6.4019,
                'longitude' => 80.4187,
                'images' => []
            ],

            // ================= CAMPSITES & MOUNTAINS =================
            [
                'name' => 'Haritha Kanda Campsite',
                'description' => 'A picturesque, misty mountain peak in Bogawantalawa known for its stunning green valleys. A perfect, serene spot for highland camping and waking up above the clouds.',
                'province' => 'Central Province',
                'district' => 'Nuwara Eliya',
                'location_type' => 'campsite',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 1400,
                'latitude' => 6.8115,
                'longitude' => 80.7013,
                'images' => []
            ],
            [
                'name' => 'Narangala Peak',
                'description' => 'The second highest mountain in the Uva Province, featuring a unique rectangular shape. Known for golden grass slopes, it offers a challenging hike and breathtaking campsite winds.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'campsite',
                'terrain_difficulty' => 'challenging',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 1527,
                'latitude' => 7.0289,
                'longitude' => 80.9967,
                'images' => [] 
            ],
            [
                'name' => 'Baththalangunduwa Island',
                'description' => 'An elongated sandy island located off the coast of Kalpitiya in the Portugal Bay. Reached by a 3-hour boat ride, it is a remote, windy destination famous for raw beach camping and incredible seafood.',
                'province' => 'North Western Province',
                'district' => 'Puttalam',
                'location_type' => 'campsite',
                'terrain_difficulty' => 'extreme',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 2,
                'latitude' => 8.4942,
                'longitude' => 79.7919,
                'images' => []
            ],

            // ================= WATERFALLS =================
            [
                'name' => 'Bambarakanda Falls',
                'description' => 'The tallest waterfall in Sri Lanka, plummeting 263 meters down a sheer pine-forested cliff. Tucked away off the main highway, it features a rugged approach and a serene plunge pool.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'waterfall',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => true, // Tough road approach
                'requires_guide' => false,
                'elevation_meters' => 1000,
                'latitude' => 6.7668,
                'longitude' => 80.8265,
                'images' => []
            ],
            [
                'name' => 'Diyaluma Falls',
                'description' => 'Sri Lanka\'s second-highest waterfall cascading dramatically over 220 meters. A trek to the upper falls reveals natural infinity pools overlooking the southern plains.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'waterfall',
                'terrain_difficulty' => 'challenging',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 700,
                'latitude' => 6.7330,
                'longitude' => 81.0314,
                'images' => []
            ],

            // ================= BEACHES =================
            [
                'name' => 'Nilaveli Beach',
                'description' => 'A pristine, white-sand stretch on the East Coast with incredibly clear, calm waters. It acts as the gateway to Pigeon Island National Park for diving and snorkeling.',
                'province' => 'Eastern Province',
                'district' => 'Trincomalee',
                'location_type' => 'beach_coastal',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 3,
                'latitude' => 8.6833,
                'longitude' => 81.1833,
                'images' => []
            ],
            [
                'name' => 'Arugam Bay',
                'description' => 'The surfing capital of Sri Lanka. A laid-back, crescent-shaped bay surrounded by dry zones, wildlife, and arguably the best point breaks in the Indian Ocean.',
                'province' => 'Eastern Province',
                'district' => 'Ampara',
                'location_type' => 'beach_coastal',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 5,
                'latitude' => 6.8436,
                'longitude' => 81.8267,
                'images' => []
            ],
            [
                'name' => 'Unawatuna Beach',
                'description' => 'A vibrant, horseshoe-shaped beach lined with palm trees, bustling cafes, and coral reefs. Safe for swimming year-round and famous for its lively nightlife.',
                'province' => 'Southern Province',
                'district' => 'Galle',
                'location_type' => 'beach_coastal',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 2,
                'latitude' => 6.0125,
                'longitude' => 80.2483,
                'images' => []
            ],

            // ================= NORTHERN ATTRACTIONS =================
            [
                'name' => 'Nallur Kandaswamy Kovil',
                'description' => 'The most significant Hindu temple complex in the Northern Province. Famous for its golden arch, ornate gopuram, and deeply spiritual annual summer festival.',
                'province' => 'Northern Province',
                'district' => 'Jaffna',
                'location_type' => 'religious_site',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 10,
                'latitude' => 9.6745,
                'longitude' => 80.0293,
                'images' => []
            ],

            // ================= COLONIAL & HIGHLAND ESTATES =================
            [
                'name' => 'Adisham Bungalow',
                'description' => 'An impeccably preserved Tudor-style stone mansion built by a British colonial planter. Now a Benedictine monastery, it sits amidst tranquil gardens and orchards with sweeping highland views.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'ancient_ruins', // Used as proxy for colonial architecture
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 1500,
                'latitude' => 6.7654,
                'longitude' => 80.9621,
                'images' => []
            ],
            [
                'name' => 'Lipton\'s Seat',
                'description' => 'A famous high-altitude observation point where Sir Thomas Lipton used to survey his vast tea empire. It offers a commanding view across multiple provinces on a clear day.',
                'province' => 'Uva Province',
                'district' => 'Badulla',
                'location_type' => 'mountain_trek',
                'terrain_difficulty' => 'moderate',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 1970,
                'latitude' => 6.7806,
                'longitude' => 81.0155,
                'images' => []
            ],

            // ================= MAJOR CITIES & ANCIENT KINGDOMS =================
            [
                'name' => 'Anuradhapura',
                'description' => 'The first ancient capital of Sri Lanka, filled with massive brick dagobas, crumbling palaces, and the sacred Sri Maha Bodhi tree. A pinnacle of ancient hydraulic engineering and spirituality.',
                'province' => 'North Central Province',
                'district' => 'Anuradhapura',
                'location_type' => 'ancient_ruins',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 80,
                'latitude' => 8.3114,
                'longitude' => 80.4037,
                'images' => []
            ],
            [
                'name' => 'Polonnaruwa',
                'description' => 'The second royal capital, featuring incredibly well-preserved ruins. The highlight is the Gal Viharaya, a group of magnificent Buddha statues carved directly into a granite rock face.',
                'province' => 'North Central Province',
                'district' => 'Polonnaruwa',
                'location_type' => 'ancient_ruins',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => true,
                'elevation_meters' => 60,
                'latitude' => 7.9403,
                'longitude' => 81.0188,
                'images' => []
            ],
            [
                'name' => 'Colombo City',
                'description' => 'The commercial capital of Sri Lanka. A bustling metropolis blending colonial architecture in the Fort district with modern skyscrapers and the iconic Galle Face Green ocean promenade.',
                'province' => 'Western Province',
                'district' => 'Colombo',
                'location_type' => 'urban_city',
                'terrain_difficulty' => 'easy',
                'requires_4x4' => false,
                'requires_guide' => false,
                'elevation_meters' => 5,
                'latitude' => 6.9271,
                'longitude' => 79.8438,
                'images' => []
            ]
        ];

        foreach ($locations as $locData) {
            $lat = $locData['latitude'];
            $lng = $locData['longitude'];
            
            unset($locData['images'], $locData['latitude'], $locData['longitude']);

            Location::updateOrCreate(
                ['name' => $locData['name']], // Match condition
                array_merge($locData, [
                    'coordinates' => DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography")
                ])
            );
        }
    }
}