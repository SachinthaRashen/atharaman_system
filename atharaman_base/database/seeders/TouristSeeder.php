<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TouristSeeder extends Seeder
{
    public function run(): void
    {
        User::where('role', 'tourist')->delete();

        $faker = Faker::create();
        $tourists = [];

        // Authentic Sri Lankan Name Pools
        $slFirstNames = [
            'Kasun', 'Nuwan', 'Lahiru', 'Dinesh', 'Tharindu', 'Amila', 'Gayan', 
            'Nipun', 'Sandun', 'Chathura', 'Piyumi', 'Nethmi', 'Sanduni', 
            'Tharushi', 'Kavindi', 'Oshadi', 'Hashini', 'Sachini', 'Nadeesha', 'Dilshan'
        ];
        
        $slLastNames = [
            'Perera', 'Silva', 'Fernando', 'Bandara', 'Rathnayake', 'Dissanayake', 
            'Jayasuriya', 'Gunawardena', 'Senanayake', 'Liyanage', 'Wijesinghe', 
            'Jayawardena', 'Samarasinghe', 'Ekanayake', 'Weerasinghe', 'Herath'
        ];

        // Define our 5 behavioral ML Personas
        // Country-specific language maps are defined per persona to align non-English foreign speakers accurately
        $personas = [
            // 1. Budget Adventure Backpackers
            [
                'count' => 30,
                'style' => 'adventure',
                'budget' => 'budget',
                'guide' => false,
                'age_min' => 19, 'age_max' => 29,
                'foreign_countries' => [
                    'Germany' => 'German',
                    'Australia' => 'English',
                    'United Kingdom' => 'English',
                    'Netherlands' => 'German', // Regional fallback / European
                ],
            ],
            // 2. Luxury Leisure & Wellness
            [
                'count' => 25,
                'style' => 'leisure_wellness',
                'budget' => 'luxury',
                'guide' => true,
                'age_min' => 38, 'age_max' => 68,
                'foreign_countries' => [
                    'United States' => 'English',
                    'United Arab Emirates' => 'Arabic',
                    'Singapore' => 'English',
                    'Switzerland' => 'French',
                    'France' => 'French',
                ],
            ],
            // 3. Wildlife & Deep Nature Naturalists
            [
                'count' => 25,
                'style' => 'nature_wildlife',
                'budget' => 'mid_range',
                'guide' => true,
                'age_min' => 27, 'age_max' => 55,
                'foreign_countries' => [
                    'France' => 'French',
                    'Canada' => 'English',
                    'Japan' => 'Japanese',
                    'Germany' => 'German',
                ],
            ],
            // 4. Cultural & Archaeological Seekers
            [
                'count' => 25,
                'style' => 'cultural_historic',
                'budget' => 'mid_range',
                'guide' => true,
                'age_min' => 32, 'age_max' => 70,
                'foreign_countries' => [
                    'Italy' => 'Italian',
                    'India' => 'Hindi',
                    'Spain' => 'Spanish',
                    'China' => 'Mandarin',
                    'United Kingdom' => 'English',
                ],
            ],
            // 5. Coastal Surf & Digital Nomads
            [
                'count' => 20,
                'style' => 'adventure',
                'budget' => 'mid_range',
                'guide' => false,
                'age_min' => 24, 'age_max' => 36,
                'foreign_countries' => [
                    'Russia' => 'Russian',
                    'Israel' => 'Hebrew',
                    'Sweden' => 'English',
                    'Poland' => 'Russian',
                ],
            ]
        ];

        foreach ($personas as $persona) {
            for ($i = 0; $i < $persona['count']; $i++) {
                // Introduce 10% variance to guide preference so clusters aren't completely deterministic
                $prefersGuide = (rand(1, 100) <= 90) ? $persona['guide'] : !$persona['guide'];

                // 1. Precise 50% Domestic vs 50% Foreign Split
                $isDomestic = (rand(1, 100) <= 50);

                if ($isDomestic) {
                    $country = 'Sri Lanka';
                    // 50% of Sri Lankans choose English as their tour communication language, 50% Sinhala/Tamil
                    $language = (rand(1, 100) <= 50) ? 'English' : ((rand(1, 100) <= 85) ? 'Sinhala' : 'Tamil');

                    // Generate a random Sri Lankan name
                    $name = $faker->randomElement($slFirstNames) . ' ' . $faker->randomElement($slLastNames);
                } else {
                    $foreignCountryList = array_keys($persona['foreign_countries']);
                    $country = $faker->randomElement($foreignCountryList);

                    // 65% of foreign tourists prefer English; 35% prefer their native country language
                    $prefersEnglish = (rand(1, 100) <= 65);
                    $language = $prefersEnglish ? 'English' : $persona['foreign_countries'][$country];

                    // Generate a standard foreign name
                    $name = $faker->name;
                }

                $tourists[] = [
                    'name' => $name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password123'),
                    'role' => 'tourist',
                    'date_of_birth' => Carbon::now()->subYears(rand($persona['age_min'], $persona['age_max']))->subDays(rand(1, 365)),
                    'country' => $country,
                    'prefers_guided_tours' => $prefersGuide,
                    'requires_accessibility' => (rand(1, 100) > 94), // ~6% require wheelchair/step-free access
                    'native_language' => $language,
                    'preferred_travel_style' => $persona['style'],
                    'preferred_budget_tier' => $persona['budget'],
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        User::insert($tourists);
    }
}