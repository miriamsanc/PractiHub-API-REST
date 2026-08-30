<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'company']), 
            'category_id' => Category::factory(), 
            //'category_id' => Category::inRandomOrder()->first()->id,
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'is_active' => fake()->boolean(80),
        ];
    }
}
