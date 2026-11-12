<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\HakAkses;
use App\Models\HariLibur;
use App\Models\JamOperasional;
use App\Models\Pengaturan;
use App\Models\Peran;
use App\Models\PeriodeAkademik;
use App\Models\Ruang;
use App\Models\StatusPeminjaman;
use App\Models\TemplateNotifikasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = fn(string $file) => json_decode(File::get(database_path("data/{$file}.json")), true);

        $isi = function (string $model, string $file, string $kunci) use ($data) {
            foreach ($data($file) as $baris) {
                $model::updateOrCreate([$kunci => $baris[$kunci]], $baris);
            }
        };

        $isi(HakAkses::class, 'hak_akses', 'kode');

        foreach ($data('peran') as $p) {
            $peran = Peran::updateOrCreate(['kode' => $p['kode']], Arr::except($p, 'hak_akses'));
            $kode = $p['hak_akses'] === '*' ? HakAkses::pluck('kode') : $p['hak_akses'];
            $peran->hakAkses()->sync(HakAkses::whereIn('kode', $kode)->pluck('id'));
        }

        foreach ($data('akun_demo') as $a) {
            User::updateOrCreate(['email' => $a['email']], [
                'name' => $a['name'],
                'password' => $a['password'],          
                'nim_nip' => $a['nim_nip'] ?? null,
                'peran_id' => Peran::where('kode', $a['peran'])->value('id'),
                'aktif' => true,
            ]);
        }

        foreach ($data('pengaturan') as $baris) {
            $p = Pengaturan::firstOrNew(['kunci' => $baris['kunci']]);
            $p->fill($p->exists ? Arr::except($baris, 'nilai') : $baris)->save();
        }

        $isi(JamOperasional::class, 'jam_operasional', 'hari');
        $isi(HariLibur::class, 'hari_libur', 'tanggal');
        $isi(StatusPeminjaman::class, 'status_peminjaman', 'kode');
        $isi(TemplateNotifikasi::class, 'template_notifikasi', 'kode');
        $isi(PeriodeAkademik::class, 'periode_akademik', 'nama');
        $isi(Ruang::class, 'ruang', 'kode');
        $isi(Alat::class, 'alat', 'kode');
    }
}
