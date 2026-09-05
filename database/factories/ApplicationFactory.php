<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'student']),
            'offer_id' => Offer::factory(),
            'status' => 'pending',
            'cv_path' => 'cvs/fake_cv_test.pdf',
        ];
    }
}
