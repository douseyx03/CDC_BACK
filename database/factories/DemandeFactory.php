<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Demande>
 */
class DemandeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type_demande' => 'Particulier',
            'description' => $this->faker->paragraph(),
            'urgent' => $this->faker->boolean(),
            'status' => 'soumission',
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
        ];
    }
}
