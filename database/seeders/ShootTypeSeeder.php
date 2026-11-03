<?php

namespace Database\Seeders;

use App\Models\ShootType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShootTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Wedding', 'description' => 'Traditional, royal, and destination wedding events'],
            ['name' => 'Pre-Wedding', 'description' => 'Couples portrait shoots, cinematic teasers, and outdoor sessions'],
            ['name' => 'Maternity', 'description' => 'Maternity, pregnancy glow, and baby bump portraits'],
            ['name' => 'Baby Shoot', 'description' => 'Newborn, infant milestones, cake smash, and toddler portraits'],
            ['name' => 'Corporate', 'description' => 'Corporate summits, headshots, conferences, and office events'],
            ['name' => 'Birthday', 'description' => 'Birthday parties, anniversaries, and family celebrations'],
            ['name' => 'Fashion', 'description' => 'Editorial, portfolio, runway, and commercial model shoots'],
            ['name' => 'Other', 'description' => 'Custom and specialized photography requirements'],
        ];

        foreach ($types as $type) {
            ShootType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'slug' => Str::slug($type['name']),
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
