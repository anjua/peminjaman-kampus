<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AlatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('A-###??')),
            'nama' => 'Alat ' . fake()->unique()->numerify('###'),
            'stok' => 3,
            'kondisi' => 'baik',
            'aktif' => true,
        ];
    }
}
