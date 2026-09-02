<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        Guide::query()->delete();

        $guides = [
            // ================= ELLA & HIGHLANDS =================
            [
                'owner_name' => 'Saman Kumara',
                'owner_email' => 'saman.kumara@example.com',
                'contact_number' => '+94771122334',
                'whatsapp_number' => '+94771122334',
                'bio' => 'Born and raised in Ella. I know every hidden trail around Ella Rock and Little Adam\'s Peak. Safety and scenic photography are my priorities.',
                'specialty' => 'Mountain Trekking',
                'languages_spoken' => ['English', 'German'],
                'daily_rate' => 8500.00,
                'experience_years' => 6,
                'latitude' => 6.8700,
                'longitude' => 81.0500,
                'associated_locations' => ['Ella Rock', 'Little Adam\'s Peak', 'Nine Arches Bridge']
            ],
            [
                'owner_name' => 'Roshan Fernando',
                'owner_email' => 'roshan.fernando@example.com',
                'contact_number' => '+94719988776',
                'whatsapp_number' => '+94719988776',
                'bio' => 'Expert highland guide specializing in the Horton Plains ecology and Lipton\'s Seat trails. Great with families and slow-paced scenic walks.',
                'specialty' => 'Nature & Hiking',
                'languages_spoken' => ['English', 'Arabic'],
                'daily_rate' => 9000.00,
                'experience_years' => 10,
                'latitude' => 6.8000,
                'longitude' => 80.8100,
                'associated_locations' => ['Horton Plains & World\'s End', 'Lipton\'s Seat']
            ],
            [
                'owner_name' => 'Dilshan Weerasinghe',
                'owner_email' => 'dilshan.weer@example.com',
                'contact_number' => '+94771112223',
                'whatsapp_number' => '+94771112223',
                'bio' => 'Budget-friendly local student offering walking tours to Ravana Falls and Nine Arches. Fun, energetic, and knows the best local food spots.',
                'specialty' => 'Walking Tours & Local Food',
                'languages_spoken' => ['English'],
                'daily_rate' => 4500.00,
                'experience_years' => 2,
                'latitude' => 6.8650,
                'longitude' => 81.0550,
                'associated_locations' => ['Ravana Falls', 'Nine Arches Bridge']
            ],
            [
                'owner_name' => 'Anton Silva',
                'owner_email' => 'anton.silva@example.com',
                'contact_number' => '+94762223334',
                'whatsapp_number' => '+94762223334',
                'bio' => 'Professional botanist turned guide. I offer highly detailed ecological tours of the central highlands and cloud forests.',
                'specialty' => 'Botany & Eco-Tours',
                'languages_spoken' => ['English', 'French'],
                'daily_rate' => 12000.00,
                'experience_years' => 15,
                'latitude' => 6.9690,
                'longitude' => 80.7670,
                'associated_locations' => ['Horton Plains & World\'s End', 'Haritha Kanda Campsite']
            ],
            [
                'owner_name' => 'Nadeesha Perera',
                'owner_email' => 'nadeesha.p@example.com',
                'contact_number' => '+94714445556',
                'whatsapp_number' => '+94714445556',
                'bio' => 'Female trekking guide specializing in safe, empowering hikes for solo female travelers and families in the Ella region.',
                'specialty' => 'Family & Solo Female Treks',
                'languages_spoken' => ['English', 'Mandarin'],
                'daily_rate' => 8000.00,
                'experience_years' => 5,
                'latitude' => 6.8720,
                'longitude' => 81.0480,
                'associated_locations' => ['Little Adam\'s Peak', 'Ella Rock']
            ],

            // ================= CULTURAL TRIANGLE =================
            [
                'owner_name' => 'Kamal Perera',
                'owner_email' => 'kamal.perera@example.com',
                'contact_number' => '+94765544332',
                'whatsapp_number' => '+94765544332',
                'bio' => 'Licensed tourist board guide with deep knowledge of Sri Lankan ancient history, Buddhism, and archaeology. I bring the ruins of Sigiriya to life.',
                'specialty' => 'Cultural Heritage',
                'languages_spoken' => ['English', 'French'],
                'daily_rate' => 12000.00,
                'experience_years' => 15,
                'latitude' => 7.9500,
                'longitude' => 80.7500,
                'associated_locations' => ['Sigiriya Lion Rock', 'Dambulla Royal Cave Temple']
            ],
            [
                'owner_name' => 'Nuwan Pradeep',
                'owner_email' => 'nuwan.pradeep@example.com',
                'contact_number' => '+94773322110',
                'whatsapp_number' => '+94773322110',
                'bio' => 'Specialized in the ancient kingdoms of Anuradhapura and Polonnaruwa. I provide historical context, cycle tours through the ruins, and local dining experiences.',
                'specialty' => 'History & Cycling Tours',
                'languages_spoken' => ['English', 'Japanese'],
                'daily_rate' => 10000.00,
                'experience_years' => 8,
                'latitude' => 8.3100,
                'longitude' => 80.4000,
                'associated_locations' => ['Anuradhapura Sacred City', 'Polonnaruwa Ancient City']
            ],
            [
                'owner_name' => 'Chathura Senanayake',
                'owner_email' => 'chathura.s@example.com',
                'contact_number' => '+94715556677',
                'whatsapp_number' => '+94715556677',
                'bio' => 'Pidurangala sunrise hike expert. I know the fastest and safest ways up the boulders in the dark for the perfect dawn photography session.',
                'specialty' => 'Sunrise Hikes & Photography',
                'languages_spoken' => ['English', 'Russian'],
                'daily_rate' => 6000.00,
                'experience_years' => 4,
                'latitude' => 7.9650,
                'longitude' => 80.7630,
                'associated_locations' => ['Pidurangala Rock', 'Sigiriya Lion Rock']
            ],
            [
                'owner_name' => 'Wimal Dharmasiri',
                'owner_email' => 'wimal.d@example.com',
                'contact_number' => '+94776667788',
                'whatsapp_number' => '+94776667788',
                'bio' => 'Senior lecturer in history offering premium, highly educational walking tours of Polonnaruwa and Dambulla.',
                'specialty' => 'Archaeology & Buddhism',
                'languages_spoken' => ['English', 'German', 'Italian'],
                'daily_rate' => 15000.00,
                'experience_years' => 25,
                'latitude' => 7.9400,
                'longitude' => 81.0180,
                'associated_locations' => ['Polonnaruwa Ancient City', 'Dambulla Royal Cave Temple']
            ],

            // ================= WILDLIFE (Safaris & Rainforests) =================
            [
                'owner_name' => 'Dinesh Silva',
                'owner_email' => 'dinesh.silva@example.com',
                'contact_number' => '+94712233445',
                'whatsapp_number' => '+94712233445',
                'bio' => 'Professional wildlife tracker and naturalist. If there is a leopard in Yala or Minneriya, I will find it. I focus on ethical wildlife observation.',
                'specialty' => 'Wildlife Safari',
                'languages_spoken' => ['English', 'Russian'],
                'daily_rate' => 15000.00,
                'experience_years' => 12,
                'latitude' => 6.2800,
                'longitude' => 81.3900,
                'associated_locations' => ['Yala National Park', 'Minneriya National Park']
            ],
            [
                'owner_name' => 'Sunil Rathnayake',
                'owner_email' => 'sunil.rathnayake@example.com',
                'contact_number' => '+94774455667',
                'whatsapp_number' => '+94774455667',
                'bio' => 'Expert ornithologist (bird watcher) and rainforest guide. Leading deep treks into Sinharaja to find endemic birds and reptiles.',
                'specialty' => 'Bird Watching & Rainforest',
                'languages_spoken' => ['English'],
                'daily_rate' => 8000.00,
                'experience_years' => 20,
                'latitude' => 6.4000,
                'longitude' => 80.4100,
                'associated_locations' => ['Sinharaja Forest Reserve']
            ],
            [
                'owner_name' => 'Lahiru Kumara',
                'owner_email' => 'lahiru.k@example.com',
                'contact_number' => '+94712223334',
                'whatsapp_number' => '+94712223334',
                'bio' => 'Budget safari guide for Yala. I provide shared jeep experiences and know the best times to avoid the crowds.',
                'specialty' => 'Budget Safari',
                'languages_spoken' => ['English'],
                'daily_rate' => 5000.00,
                'experience_years' => 3,
                'latitude' => 6.2850,
                'longitude' => 81.3950,
                'associated_locations' => ['Yala National Park']
            ],
            [
                'owner_name' => 'Nadeem Fazal',
                'owner_email' => 'nadeem.f@example.com',
                'contact_number' => '+94768889900',
                'whatsapp_number' => '+94768889900',
                'bio' => 'Specialist in elephant behavior. I track the herds at Minneriya during "The Gathering" and provide photography positioning.',
                'specialty' => 'Elephant Tracking',
                'languages_spoken' => ['English', 'Arabic'],
                'daily_rate' => 11000.00,
                'experience_years' => 9,
                'latitude' => 8.0300,
                'longitude' => 80.8200,
                'associated_locations' => ['Minneriya National Park']
            ],

            // ================= EXTREME TERRAIN (Knuckles / Meemure) =================
            [
                'owner_name' => 'Wasantha Bandara',
                'owner_email' => 'wasantha.bandara@example.com',
                'contact_number' => '+94719900888',
                'whatsapp_number' => '+94719900888',
                'bio' => 'Hardcore survival guide and Knuckles native. I lead extreme camping, waterfall abseiling, and multi-day treks through Meemure and Narangala.',
                'specialty' => 'Extreme Survival & Camping',
                'languages_spoken' => ['English', 'Sinhala'],
                'daily_rate' => 11000.00,
                'experience_years' => 14,
                'latitude' => 7.4300,
                'longitude' => 80.8400,
                'associated_locations' => ['Meemure Village (Knuckles)', 'Narangala Peak', 'Haritha Kanda Campsite']
            ],
            [
                'owner_name' => 'Kasun Chamara',
                'owner_email' => 'kasun.c@example.com',
                'contact_number' => '+94775556677',
                'whatsapp_number' => '+94775556677',
                'bio' => 'Waterfall hunter and extreme trekker. I guide adventurous groups to the peaks of Bambarakanda and Diyaluma for wild swimming.',
                'specialty' => 'Waterfall Trekking',
                'languages_spoken' => ['English'],
                'daily_rate' => 7500.00,
                'experience_years' => 5,
                'latitude' => 6.7600,
                'longitude' => 80.8200,
                'associated_locations' => ['Bambarakanda Falls', 'Diyaluma Falls']
            ],
            [
                'owner_name' => 'Upali Gunawardena',
                'owner_email' => 'upali.g@example.com',
                'contact_number' => '+94713334455',
                'whatsapp_number' => '+94713334455',
                'bio' => 'Former military mountaineer. I provide secure, highly structured climbing and trekking expeditions to Narangala and Knuckles.',
                'specialty' => 'Mountaineering',
                'languages_spoken' => ['English', 'Russian'],
                'daily_rate' => 14000.00,
                'experience_years' => 20,
                'latitude' => 7.0200,
                'longitude' => 80.9900,
                'associated_locations' => ['Narangala Peak', 'Meemure Village (Knuckles)']
            ],

            // ================= CITY & COASTAL =================
            [
                'owner_name' => 'Ajith De Zoysa',
                'owner_email' => 'ajith.dezoysa@example.com',
                'contact_number' => '+94776655443',
                'whatsapp_number' => '+94776655443',
                'bio' => 'Galle local with a passion for colonial history, culinary tours, and hidden street art within the Dutch Fort.',
                'specialty' => 'City & Culinary Tours',
                'languages_spoken' => ['English', 'Dutch'],
                'daily_rate' => 7500.00,
                'experience_years' => 18,
                'latitude' => 6.0250,
                'longitude' => 80.2150,
                'associated_locations' => ['Galle Dutch Fort', 'Unawatuna Beach']
            ],
            [
                'owner_name' => 'Praveen Mendis',
                'owner_email' => 'praveen.mendis@example.com',
                'contact_number' => '+94718889999',
                'whatsapp_number' => '+94718889999',
                'bio' => 'Colombo night-life and street food guide. I take you through the hidden alleys of Pettah and the best local bars.',
                'specialty' => 'Street Food & Nightlife',
                'languages_spoken' => ['English', 'Spanish'],
                'daily_rate' => 6500.00,
                'experience_years' => 4,
                'latitude' => 6.9271,
                'longitude' => 79.8438,
                'associated_locations' => ['Colombo City Center']
            ],
            [
                'owner_name' => 'Tharindu Fernando',
                'owner_email' => 'tharindu.f@example.com',
                'contact_number' => '+94774443322',
                'whatsapp_number' => '+94774443322',
                'bio' => 'Surfing instructor and coastal guide in Mirissa and Unawatuna. I arrange beach parties, snorkeling, and surf lessons.',
                'specialty' => 'Surfing & Beach Activities',
                'languages_spoken' => ['English', 'German'],
                'daily_rate' => 9000.00,
                'experience_years' => 7,
                'latitude' => 5.9450,
                'longitude' => 80.4550,
                'associated_locations' => ['Mirissa Beach', 'Coconut Tree Hill', 'Unawatuna Beach']
            ],
            [
                'owner_name' => 'Sameera Jayasuriya',
                'owner_email' => 'sameera.j@example.com',
                'contact_number' => '+94716665544',
                'whatsapp_number' => '+94716665544',
                'bio' => 'Whale watching coordinator and marine guide based in Mirissa. Let me guide you to the best ethical boat operators.',
                'specialty' => 'Marine Life & Whale Watching',
                'languages_spoken' => ['English', 'French'],
                'daily_rate' => 8500.00,
                'experience_years' => 6,
                'latitude' => 5.9480,
                'longitude' => 80.4600,
                'associated_locations' => ['Mirissa Beach']
            ],

            // ================= NORTHERN & EASTERN =================
            [
                'owner_name' => 'Sivakumar Rajan',
                'owner_email' => 'siva.rajan@example.com',
                'contact_number' => '+94775551122',
                'whatsapp_number' => '+94775551122',
                'bio' => 'Jaffna local historian. I guide visitors through the Nallur Kovil, local markets, and the tragic yet beautiful history of the North.',
                'specialty' => 'Northern Culture & Temples',
                'languages_spoken' => ['English', 'Tamil'],
                'daily_rate' => 7000.00,
                'experience_years' => 11,
                'latitude' => 9.6700,
                'longitude' => 80.0250,
                'associated_locations' => ['Nallur Kandaswamy Kovil']
            ],
            [
                'owner_name' => 'Azeez Rahman',
                'owner_email' => 'azeez.r@example.com',
                'contact_number' => '+94713332211',
                'whatsapp_number' => '+94713332211',
                'bio' => 'Arugam Bay surf veteran. Whether you want to catch your first wave or find secret point breaks, I am your guide.',
                'specialty' => 'Surf Guiding',
                'languages_spoken' => ['English', 'Hebrew'],
                'daily_rate' => 8000.00,
                'experience_years' => 9,
                'latitude' => 6.8400,
                'longitude' => 81.8250,
                'associated_locations' => ['Arugam Bay']
            ],
            [
                'owner_name' => 'Karthik Nathan',
                'owner_email' => 'karthik.n@example.com',
                'contact_number' => '+94762221199',
                'whatsapp_number' => '+94762221199',
                'bio' => 'Diving master in Nilaveli. I take certified and non-certified divers to Pigeon Island to swim with reef sharks and turtles.',
                'specialty' => 'Scuba Diving & Snorkeling',
                'languages_spoken' => ['English', 'Tamil', 'German'],
                'daily_rate' => 13000.00,
                'experience_years' => 12,
                'latitude' => 8.6800,
                'longitude' => 81.1800,
                'associated_locations' => ['Nilaveli Beach']
            ],
            [
                'owner_name' => 'Dayananda Peris',
                'owner_email' => 'daya.p@example.com',
                'contact_number' => '+94719998877',
                'whatsapp_number' => '+94719998877',
                'bio' => 'Kalpitiya and Baththalangunduwa boat guide. I arrange extreme beach camping and dolphin watching tours.',
                'specialty' => 'Boat Tours & Island Camping',
                'languages_spoken' => ['English'],
                'daily_rate' => 10000.00,
                'experience_years' => 15,
                'latitude' => 8.4900,
                'longitude' => 79.7900,
                'associated_locations' => ['Baththalangunduwa Island']
            ]
        ];

        foreach ($guides as $data) {
            // 1. Create or retrieve the Guide's User Account
            $owner = User::updateOrCreate(
                ['email' => $data['owner_email']],
                [
                    'name' => $data['owner_name'],
                    'password' => Hash::make('password123'),
                    'role' => 'guide',
                    'email_verified_at' => now(),
                ]
            );

            // Extract spatial and relational data before inserting to the DB
            $lat = $data['latitude'];
            $lng = $data['longitude'];
            $associatedLocations = $data['associated_locations'];

            unset($data['owner_name'], $data['owner_email'], $data['latitude'], $data['longitude'], $data['associated_locations']);

            // 2. Create the Guide profile with PostGIS coordinates
            $guide = tap(new Guide($data), function ($g) use ($owner, $lat, $lng) {
                $g->user_id = $owner->id;
                $g->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
                $g->save();
            });

            // 3. Sync the Pivot Table (Guide <-> Locations)
            // We look up the IDs of the locations by their exact names from the LocationSeeder
            $locationIds = Location::whereIn('name', $associatedLocations)->pluck('id')->toArray();
            
            if (!empty($locationIds)) {
                $guide->locations()->sync($locationIds);
            }
        }
    }
}