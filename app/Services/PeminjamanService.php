<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanService
{
    public function ajukan(User $pengguna, array $data): Peminjaman
    {
        try {
            $peminjaman = DB::transaction(function () use ($pengguna, $data) {
                if (!empty($data['ruang_id']) && Peminjaman::where('ruang_id', $data['ruang_id'])
                    ->beririsan($data['waktu_mulai'], $data['waktu_selesai'])->exists()
                ) {
                    throw ValidationException::withMessages(['ruang_id' => 'Ruang sudah dipinjam pada jam tersebut.']);
                }

                foreach ($data['detail'] ?? [] as $i => $baris) {
                    $alat = Alat::lockForUpdate()->findOrFail($baris['alat_id']);
                    $sisa = $alat->stok - $alat->terpakaiPada($data['waktu_mulai'], $data['waktu_selesai']);
                    if ($baris['jumlah'] > $sisa) {
                        throw ValidationException::withMessages([
                            "detail.{$i}.jumlah" => "Stok {$alat->nama} pada jam itu tersisa {$sisa} unit.",
                        ]);
                    }
                }

                $peminjaman = $pengguna->peminjaman()->create([
                    'ruang_id' => $data['ruang_id'] ?? null,
                    'waktu_mulai' => $data['waktu_mulai'],
                    'waktu_selesai' => $data['waktu_selesai'],
                    'jumlah_peserta' => $data['jumlah_peserta'],
                    'keperluan' => $data['keperluan'],
                    'status' => 'menunggu',
                ]);
                $peminjaman->detail()->createMany($data['detail'] ?? []);

                return $peminjaman;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23P01') {
                throw ValidationException::withMessages(['ruang_id' => 'Ruang baru saja dipesan orang lain. Pilih jam lain.']);
            }
            throw $e;
        }

        Notifikasi::kirim('pengajuan_baru', $peminjaman);

        return $peminjaman;
    }
}
