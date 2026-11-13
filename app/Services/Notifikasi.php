<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\TemplateNotifikasi;
use App\Models\User;
use App\Notifications\NotifikasiTemplate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Notifikasi
{
    public static function kirim(string $kode, Peminjaman $p): void
    {
        $t = TemplateNotifikasi::where('kode', $kode)->where('aktif', true)->first();
        if (!$t) {
            return;
        }

        $penerima = $t->penerima === 'peminjam'
            ? collect([$p->pengguna])
            : User::where('aktif', true)
            ->whereHas('peran.hakAkses', fn($q) => $q->where('kode', Str::after($t->penerima, 'hak:')))
            ->get();

        Notification::send(
            $penerima,
            new NotifikasiTemplate($t, $p->loadMissing('pengguna', 'ruang', 'detail.alat', 'statusRef'))
        );
    }
}
