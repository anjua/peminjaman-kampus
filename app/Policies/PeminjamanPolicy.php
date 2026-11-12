<?php

namespace App\Policies;

use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    public function view(User $user, Peminjaman $p): bool
    {
        return $p->pengguna_id === $user->id || $user->can('lihat-semua-peminjaman');
    }

    public function batal(User $user, Peminjaman $p): bool
    {
        if ($p->pengguna_id !== $user->id) {
            return false;
        }

        return $p->status === 'menunggu'
            || ($p->status === 'disetujui'
                && $p->waktu_mulai->gt(now()->addHours(Pengaturan::ambil('peminjaman.batas_batal_jam'))));
    }

    public function bukti(User $user, Peminjaman $p): bool
    {
        return in_array($p->status, ['disetujui', 'selesai'])
            && ($p->pengguna_id === $user->id || $user->can('lihat-semua-peminjaman'));
    }
}
