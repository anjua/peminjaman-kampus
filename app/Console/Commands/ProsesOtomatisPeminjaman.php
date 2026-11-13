<?php

namespace App\Console\Commands;

use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Services\Notifikasi;
use Illuminate\Console\Command;

class ProsesOtomatisPeminjaman extends Command
{
    protected $signature = 'peminjaman:proses-otomatis';

    protected $description = 'Tolak pengajuan kedaluwarsa, selesaikan peminjaman ruang, kirim pengingat dan peringatan terlambat';

    public function handle(): int
    {
        if (Pengaturan::ambil('peminjaman.kedaluwarsa_otomatis')) {
            Peminjaman::where('status', 'menunggu')->where('waktu_mulai', '<=', now())->get()
                ->each(function (Peminjaman $p) {
                    $p->update(['status' => 'ditolak', 'catatan_admin' => 'Ditolak otomatis: tidak diproses sampai waktu mulai.']);
                    Notifikasi::kirim('ditolak', $p);
                    $this->line("Kedaluwarsa: #{$p->id}");
                });
        }

        $selesai = Peminjaman::where('status', 'disetujui')->where('waktu_selesai', '<=', now())
            ->whereDoesntHave('detail')->update(['status' => 'selesai']);
        $this->line("Selesai otomatis: {$selesai}");

        $menit = Pengaturan::ambil('notifikasi.pengingat_sebelum_mulai_menit');
        Peminjaman::where('status', 'disetujui')->whereNull('pengingat_dikirim_pada')
            ->whereBetween('waktu_mulai', [now(), now()->addMinutes($menit)])->get()
            ->each(function (Peminjaman $p) {
                Notifikasi::kirim('pengingat_mulai', $p);
                $p->update(['pengingat_dikirim_pada' => now()]);
                $this->line("Pengingat: #{$p->id}");
            });

        $jeda = Pengaturan::ambil('notifikasi.interval_terlambat_jam');
        Peminjaman::where('status', 'disetujui')->whereNull('dikembalikan_pada')->whereHas('detail')
            ->where('waktu_selesai', '<', now())
            ->where(fn($q) => $q->whereNull('terakhir_diingatkan_pada')
                ->orWhere('terakhir_diingatkan_pada', '<=', now()->subHours($jeda)))
            ->get()
            ->each(function (Peminjaman $p) {
                Notifikasi::kirim('alat_terlambat', $p);
                $p->update(['terakhir_diingatkan_pada' => now()]);
                $this->line("Terlambat: #{$p->id}");
            });

        return self::SUCCESS;
    }
}
