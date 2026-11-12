<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RuangFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('R-###??')),
            'nama' => 'Ruang ' . fake()->unique()->numerify('###'),
            'lokasi' => 'Gedung ' . fake()->randomLetter() . ' Lt. ' . fake()->numberBetween(1, 4),
            'kapasitas' => fake()->numberBetween(20, 100),
            'aktif' => true,
        ];
    }
}
