<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'avantage' => $this->faker->sentences(2),
            'delai' => $this->faker->randomElement(['5 jours', '10 jours', '15 jours']),
            'montant_min' => $this->faker->randomFloat(2, 10, 1000),
            'document_requis' => [$this->faker->sentence(3)],
            'user_id' => User::factory(),
        ];
    }
}
