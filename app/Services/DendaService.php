<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\Peminjaman;
use Carbon\Carbon;

class DendaService
{
    public function hitung(Peminjaman $p, Carbon $waktuKembali): int
    {
        if (!Pengaturan::ambil('denda.aktif')) {
            return 0;
        }

        $menit = (int) $p->waktu_selesai->diffInMinutes($waktuKembali, false) - Pengaturan::ambil('denda.toleransi_menit');
        if ($menit <= 0) {
            return 0;
        }

        $jam = (int) ceil($menit / 60);
        $tarifUmum = Pengaturan::ambil('denda.per_jam');

        return (int) $p->loadMissing('detail.alat')->detail
            ->sum(fn($d) => $jam * $d->jumlah * ($d->alat->denda_per_jam ?? $tarifUmum));
    }
}
