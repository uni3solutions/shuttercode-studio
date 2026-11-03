<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Photography Package',
            'category' => fake()->randomElement(['Wedding', 'Pre-Wedding', 'Maternity', 'Birthday']),
            'price' => fake()->randomElement([45000, 75000, 120000, 200000]),
            'advance_amount' => 15000,
            'duration_hours' => fake()->randomElement([4, 8, 12]),
            'photographers_count' => fake()->randomElement([1, 2, 3]),
            'videographers_count' => fake()->randomElement([0, 1, 2]),
            'drone_included' => fake()->boolean(),
            'album_count' => fake()->randomElement([1, 2]),
            'deliverables' => 'Full HD Edited Photos, Teaser Reel, Leather Photobook',
            'is_active' => true,
        ];
    }
}
