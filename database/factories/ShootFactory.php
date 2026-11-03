<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shoot>
 */
class ShootFactory extends Factory
{
    protected $model = Shoot::class;

    public function definition(): array
    {
        $total = fake()->randomElement([50000, 85000, 150000]);
        $discount = 5000;
        $paid = 20000;
        $balance = $total - $discount - $paid;

        return [
            'shoot_number' => 'SHT-'.date('Y').'-'.fake()->unique()->numerify('###'),
            'customer_id' => Customer::factory(),
            'package_id' => Package::factory(),
            'created_by' => User::factory(),
            'title' => fake()->words(3, true).' Shoot',
            'event_type' => fake()->randomElement(['Wedding', 'Pre-Wedding', 'Maternity', 'Corporate']),
            'shoot_date' => fake()->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'venue' => fake()->streetAddress().', '.fake()->city(),
            'location_url' => 'https://maps.google.com/?q=studio',
            'total_amount' => $total,
            'discount_amount' => $discount,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'status' => 'scheduled',
            'requirements' => 'Candid portraits, traditional rituals, drone coverage',
            'notes' => fake()->sentence(),
        ];
    }
}
