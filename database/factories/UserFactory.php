<?php

namespace Database\Factories;

use App\Models\Peran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'nim_nip' => fake()->numerify('23110#####'),
            'aktif' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function peran(string $kode): static
    {
        return $this->state(fn() => ['peran_id' => Peran::where('kode', $kode)->value('id')]);
    }
}
