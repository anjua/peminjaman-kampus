<?php

namespace App\Console\Commands;

use App\Models\Alat;
use App\Models\HariLibur;
use App\Models\JamOperasional;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\Peran;
use App\Models\Ruang;
use App\Models\User;
use App\Services\DendaService;
use App\Services\Notifikasi;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IsiDataDemo extends Command
{
    protected $signature = 'demo:isi
        {--pengguna=20 : jumlah pengguna dummy}
        {--peminjaman=80 : jumlah peminjaman}
        {--bulan-lalu=3 : rentang data ke belakang (bulan)}
        {--minggu-depan=2 : rentang data ke depan (minggu)}
        {--acak= : angka seed agar hasil selalu sama}';

    protected $description = 'Isi peminjaman demo yang mengikuti semua aturan di database';

    public function handle(DendaService $dendaService): int
    {
        $faker = Faker::create(config('app.faker_locale'));
        if ($this->option('acak') !== null) {
            $faker->seed((int) $this->option('acak'));
            mt_srand((int) $this->option('acak'));
        }

        $peranId = Peran::where('kode', Pengaturan::ambil('aplikasi.peran_registrasi'))->value('id');
        for ($i = 0; $i < (int) $this->option('pengguna'); $i++) {
            User::firstOrCreate(['email' => 'demo' . ($i + 1) . '@kampus.test'], [
                'name' => $faker->name(),
                'password' => 'password',
                'nim_nip' => $faker->numerify('23110#####'),
                'telepon' => $faker->numerify('08##########'),
                'peran_id' => $peranId,
            ]);
        }
        $peminjam = User::where('peran_id', $peranId)->get();
        $ruang = Ruang::where('aktif', true)->get();
        $alat = Alat::where('aktif', true)->get();
        $jam = JamOperasional::where('aktif', true)->get()->keyBy('hari');
        $libur = HariLibur::pluck('tanggal')->map(fn($t) => $t->toDateString())->all();
        $min = Pengaturan::ambil('peminjaman.durasi_min_menit');
        $maks = Pengaturan::ambil('peminjaman.durasi_maks_jam') * 60;
        $petugas = User::whereHas('peran.hakAkses', fn($q) => $q->where('kode', 'proses-persetujuan'))->first();
        $keperluan = [
            'Rapat himpunan',
            'Seminar nasional',
            'Praktikum pengganti',
            'Latihan paduan suara',
            'Kuliah tamu',
            'Workshop desain',
            'Sidang skripsi',
            'Pelatihan UKM',
            'Lomba debat',
            'Dokumentasi acara'
        ];

        $awal = now()->subMonths((int) $this->option('bulan-lalu'))->startOfDay();
        $akhir = now()->addWeeks((int) $this->option('minggu-depan'))->endOfDay();
        $dibuat = 0;
        $percobaan = 0;
        $bar = $this->output->createProgressBar((int) $this->option('peminjaman'));

        while ($dibuat < (int) $this->option('peminjaman') && $percobaan++ < $this->option('peminjaman') * 20) {
            $tanggal = Carbon::createFromTimestamp(mt_rand($awal->timestamp, $akhir->timestamp))->startOfDay();
            $j = $jam->get($tanggal->dayOfWeek);
            if (!$j || in_array($tanggal->toDateString(), $libur)) {
                continue;
            }
            $buka = $tanggal->copy()->setTimeFromTimeString($j->buka);
            $tutup = $tanggal->copy()->setTimeFromTimeString($j->tutup);
            $durasi = intdiv(mt_rand($min, min($maks, $buka->diffInMinutes($tutup))), 30) * 30 ?: $min;
            $slot = intdiv((int) $buka->diffInMinutes($tutup) - $durasi, 30);
            if ($slot < 0) {
                continue;
            }
            $mulai = $buka->copy()->addMinutes(30 * mt_rand(0, $slot));
            $selesai = $mulai->copy()->addMinutes($durasi);

            $r = mt_rand(1, 10) <= 8 ? $ruang->random() : null;
            if ($r && Peminjaman::where('ruang_id', $r->id)->beririsan($mulai, $selesai)->exists()) {
                continue;
            }
            $detail = [];
            if (!$r || mt_rand(1, 2) === 1) {
                foreach ($alat->random(mt_rand(1, 3)) as $a) {
                    $sisa = $a->stok - $a->terpakaiPada($mulai, $selesai);
                    if ($sisa > 0) {
                        $detail[] = ['alat_id' => $a->id, 'jumlah' => mt_rand(1, min(3, $sisa))];
                    }
                }
            }
            if (!$r && !$detail) {
                continue;
            }

            $lewat = $selesai->isPast();
            $status = $lewat
                ? $faker->randomElement(['selesai', 'selesai', 'selesai', 'selesai', 'ditolak', 'dibatalkan'])
                : $faker->randomElement(['menunggu', 'menunggu', 'disetujui', 'disetujui', 'disetujui', 'ditolak']);
            if ($mulai->isPast() && !$lewat) {
                $status = 'disetujui';
            }

            DB::transaction(function () use ($peminjam, $r, $mulai, $selesai, $faker, $keperluan, $status, $petugas, $detail, $lewat, $dendaService) {
                $p = Peminjaman::create([
                    'pengguna_id' => $peminjam->random()->id,
                    'ruang_id' => $r?->id,
                    'waktu_mulai' => $mulai,
                    'waktu_selesai' => $selesai,
                    'jumlah_peserta' => $r ? mt_rand(1, $r->kapasitas) : 1,
                    'keperluan' => $faker->randomElement($keperluan),
                    'status' => $status === 'selesai' ? 'disetujui' : $status,
                    'catatan_admin' => $status === 'ditolak' ? 'Ruang dipakai kegiatan fakultas.' : null,
                    'disetujui_oleh' => in_array($status, ['disetujui', 'selesai', 'ditolak']) ? $petugas?->id : null,
                    'disetujui_pada' => in_array($status, ['disetujui', 'selesai', 'ditolak']) ? $mulai->copy()->subDays(1) : null,
                    'created_at' => $mulai->copy()->subDays(mt_rand(2, 7)),
                ]);
                $p->detail()->createMany($detail);

                if ($status === 'selesai') {
                    $kembali = $detail && mt_rand(1, 5) === 1
                        ? $selesai->copy()->addMinutes(mt_rand(30, 300))
                        : $selesai->copy()->subMinutes(mt_rand(0, 20));
                    $denda = $detail ? $dendaService->hitung($p->load('detail.alat'), $kembali) : 0;
                    $p->update([
                        'status' => 'selesai',
                        'dikembalikan_pada' => $detail ? $kembali : null,
                        'kondisi_kembali' => $detail ? 'baik' : null,
                        'denda' => $denda,
                        'denda_lunas' => $denda === 0 || mt_rand(0, 1) === 1,
                    ]);
                }
                if ($status === 'menunggu') {
                    Notifikasi::kirim('pengajuan_baru', $p);
                } elseif (!$lewat && $status !== 'selesai') {
                    Notifikasi::kirim($status, $p);
                }
            });
            $dibuat++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $akunDenda = User::where('email', 'denda@kampus.test')->first();
        $contoh = Peminjaman::where('status', 'selesai')->where('denda', '>', 0)->first();
        if ($akunDenda && $contoh) {
            $contoh->update(['pengguna_id' => $akunDenda->id, 'denda_lunas' => false]);
        }

        $this->info("{$dibuat} peminjaman demo dibuat.");

        return self::SUCCESS;
    }
}
