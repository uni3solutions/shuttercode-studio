<?php

namespace Database\Factories;

use App\Models\ShootType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShootType>
 */
class ShootTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Baby Shoot',
            'Maternity Shoot',
            'Corporate Shoot',
            'Destination Wedding',
            'Fashion Portfolio',
            'Birthday Celebration',
            'Anniversary Shoot',
            'Commercial Product',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
