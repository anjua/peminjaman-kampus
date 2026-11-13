<?php

namespace App\Http\Requests;

use App\Models\HariLibur;
use App\Models\JamOperasional;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\Ruang;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'detail' => collect($this->input('detail', []))
                ->filter(fn($d) => !empty($d['alat_id']) && (int) ($d['jumlah'] ?? 0) > 0)
                ->values()->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'ruang_id' => ['nullable', Rule::exists('ruang', 'id')->where('aktif', true)],
            'waktu_mulai' => ['required', 'date'],
            'waktu_selesai' => ['required', 'date', 'after:waktu_mulai'],
            'jumlah_peserta' => ['required', 'integer', 'min:1'],
            'keperluan' => ['required', 'string', 'max:500'],
            'detail' => ['array'],
            'detail.*.alat_id' => ['required', 'distinct', Rule::exists('alat', 'id')->where('aktif', true)],
            'detail.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ruang_id' => 'ruang',
            'waktu_mulai' => 'waktu mulai',
            'waktu_selesai' => 'waktu selesai',
            'jumlah_peserta' => 'jumlah peserta',
            'detail.*.alat_id' => 'alat',
            'detail.*.jumlah' => 'jumlah alat',
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            if (!$this->ruang_id && empty($this->detail)) {
                $v->errors()->add('ruang_id', 'Pilih ruang atau minimal satu alat.');
                return;
            }

            $mulai = Carbon::parse($this->waktu_mulai);
            $selesai = Carbon::parse($this->waktu_selesai);
            $durasi = $mulai->diffInMinutes($selesai);
            $jeda = Pengaturan::ambil('peminjaman.jeda_pengajuan_jam');
            $maksHari = Pengaturan::ambil('peminjaman.maks_hari_ke_depan');
            $min = Pengaturan::ambil('peminjaman.durasi_min_menit');
            $maks = Pengaturan::ambil('peminjaman.durasi_maks_jam');
            $maksAktif = Pengaturan::ambil('peminjaman.maks_aktif');
            $jam = JamOperasional::where('hari', $mulai->dayOfWeek)->where('aktif', true)->first();
            $libur = HariLibur::whereDate('tanggal', $mulai->toDateString())->first();

            if ($mulai->lt(now()->addHours($jeda))) {
                $v->errors()->add('waktu_mulai', "Pengajuan paling lambat {$jeda} jam sebelum mulai.");
            }
            if ($mulai->gt(now()->addDays($maksHari))) {
                $v->errors()->add('waktu_mulai', "Pengajuan paling jauh {$maksHari} hari ke depan.");
            }
            if ($libur) {
                $v->errors()->add('waktu_mulai', "Tanggal ini libur: {$libur->keterangan}.");
            } elseif (!$jam) {
                $v->errors()->add('waktu_mulai', 'Hari ' . $mulai->translatedFormat('l') . ' tidak melayani peminjaman.');
            } elseif (!$mulai->isSameDay($selesai) || $mulai->format('H:i:s') < $jam->buka || $selesai->format('H:i:s') > $jam->tutup) {
                $v->errors()->add('waktu_mulai', "Hari {$jam->nama_hari} hanya melayani " . substr($jam->buka, 0, 5) . '–' . substr($jam->tutup, 0, 5) . '.');
            }
            if ($durasi < $min || $durasi > $maks * 60) {
                $v->errors()->add('waktu_selesai', "Durasi peminjaman {$min} menit sampai {$maks} jam.");
            }
            if ($this->ruang_id && $this->jumlah_peserta > Ruang::find($this->ruang_id)->kapasitas) {
                $v->errors()->add('jumlah_peserta', 'Jumlah peserta melebihi kapasitas ruang (' . Ruang::find($this->ruang_id)->kapasitas . ' orang).');
            }
            $aktif = $this->user()->peminjaman()->whereIn('status', Peminjaman::AKTIF)->where('waktu_selesai', '>', now())->count();
            if ($aktif >= $maksAktif) {
                $v->errors()->add('ruang_id', "Kamu sudah punya {$aktif} pengajuan aktif (maksimal {$maksAktif}).");
            }
            if (Pengaturan::ambil('denda.blokir_jika_belum_lunas') && $this->user()->peminjaman()->where('denda_lunas', false)->exists()) {
                $v->errors()->add('ruang_id', 'Selesaikan denda keterlambatan dulu sebelum mengajukan lagi.');
            }
        }];
    }
}
