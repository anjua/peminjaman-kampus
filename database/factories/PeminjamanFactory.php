<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeminjamanFactory extends Factory
{
    public function definition(): array
    {
        $mulai = now()->addDays(3)->setTime(9, 0);

        return [
            'pengguna_id' => User::factory(),
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $mulai->copy()->addHours(2),
            'jumlah_peserta' => 10,
            'keperluan' => fake()->sentence(),
            'status' => 'menunggu',
        ];
    }
}
