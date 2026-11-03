<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'lead_number' => 'LD-'.date('Y').'-'.fake()->unique()->numerify('###'),
            'customer_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'event_type' => fake()->randomElement(['Wedding', 'Pre-Wedding', 'Maternity', 'Engagement']),
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'venue' => fake()->city().' Palace',
            'estimated_budget' => fake()->randomElement([60000, 100000, 180000]),
            'source' => fake()->randomElement(['WhatsApp Meta', 'Website Form', 'Instagram DM', 'Walk-in']),
            'status' => 'new',
            'notes' => fake()->sentence(),
        ];
    }
}
