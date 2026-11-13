<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanRequest;
use App\Models\Alat;
use App\Models\HariLibur;
use App\Models\JamOperasional;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\Ruang;
use App\Models\StatusPeminjaman;
use App\Services\Notifikasi;
use App\Services\PeminjamanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('peminjaman.index', ['status' => StatusPeminjaman::orderBy('urutan')->get()]);
        }

        $data = $request->user()->peminjaman()
            ->with('ruang', 'detail.alat', 'statusRef')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->dari, fn($q, $d) => $q->whereDate('waktu_mulai', '>=', $d))
            ->when($request->sampai, fn($q, $d) => $q->whereDate('waktu_mulai', '<=', $d))
            ->latest('waktu_mulai')->get()
            ->map(fn(Peminjaman $p) => $this->baris($p) + [
                'boleh_batal' => $request->user()->can('batal', $p),
                'url_batal' => route('peminjaman.batal', $p),
            ]);

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        return view('peminjaman.create', [
            'ruang' => Ruang::where('aktif', true)->orderBy('nama')->get(),
            'alat' => Alat::where('aktif', true)->where('stok', '>', 0)->orderBy('nama')->get(),
            // Nilai ini dikirim ke JavaScript untuk mengatur Flatpickr
            'aturan' => [
                'hari_aktif' => JamOperasional::where('aktif', true)->pluck('hari'),
                'libur' => HariLibur::pluck('tanggal')->map(fn($t) => $t->toDateString()),
                'jeda_jam' => Pengaturan::ambil('peminjaman.jeda_pengajuan_jam'),
                'maks_hari' => Pengaturan::ambil('peminjaman.maks_hari_ke_depan'),
                'durasi_min' => Pengaturan::ambil('peminjaman.durasi_min_menit'),
            ],
        ]);
    }

    public function store(PeminjamanRequest $request, PeminjamanService $service)
    {
        $peminjaman = $service->ajukan($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'pesan' => 'Pengajuan terkirim, menunggu persetujuan petugas.',
                'url' => route('peminjaman.show', $peminjaman),
            ], 201);
        }

        return redirect()->route('peminjaman.show', $peminjaman)->with('success', 'Pengajuan terkirim.');
    }

    public function show(Peminjaman $peminjaman)
    {
        Gate::authorize('view', $peminjaman);
        $peminjaman->load('pengguna.peran', 'ruang', 'detail.alat', 'statusRef', 'penyetuju');

        return view('peminjaman.show', [
            'p' => $peminjaman,
            'kondisi' => Pengaturan::ambil('pengembalian.pilihan_kondisi'),
        ]);
    }

    public function batal(Request $request, Peminjaman $peminjaman)
    {
        Gate::authorize('batal', $peminjaman);
        $peminjaman->update(['status' => 'dibatalkan']);
        Notifikasi::kirim('dibatalkan', $peminjaman);

        return $request->expectsJson()
            ? response()->json(['pesan' => 'Pengajuan dibatalkan.'])
            : back()->with('success', 'Pengajuan dibatalkan.');
    }

    public function bukti(Peminjaman $peminjaman)
    {
        Gate::authorize('bukti', $peminjaman);
        $peminjaman->load('pengguna', 'ruang', 'detail.alat', 'statusRef', 'penyetuju');

        return Pdf::loadView('pdf.bukti', ['p' => $peminjaman, 'kop' => $this->kop()])
            ->download("bukti-peminjaman-{$peminjaman->id}.pdf");
    }

    public function cekKetersediaan(Request $request)
    {
        $request->validate([
            'waktu_mulai' => ['required', 'date'],
            'waktu_selesai' => ['required', 'date', 'after:waktu_mulai'],
            'ruang_id' => ['nullable', 'integer'],
        ]);
        $mulai = $request->waktu_mulai;
        $selesai = $request->waktu_selesai;

        $bentrok = $request->ruang_id
            ? Peminjaman::with('statusRef')->where('ruang_id', $request->ruang_id)->beririsan($mulai, $selesai)
            ->orderBy('waktu_mulai')->get()
            ->map(fn($p) => [
                'mulai' => $p->waktu_mulai->format('H:i'),
                'selesai' => $p->waktu_selesai->format('H:i'),
                'status' => $p->statusRef->label,
            ])
            : collect();

        $ruangTerpakai = Peminjaman::beririsan($mulai, $selesai)->whereNotNull('ruang_id')->pluck('ruang_id');

        $alat = Alat::where('aktif', true)->orderBy('nama')->get()->map(fn(Alat $a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'stok' => $a->stok,
            'tersisa' => max(0, $a->stok - $a->terpakaiPada($mulai, $selesai)),
        ]);

        $tanggal = Carbon::parse($mulai);
        $jam = JamOperasional::where('hari', $tanggal->dayOfWeek)->first();

        return response()->json([
            'ruang_tersedia' => $bentrok->isEmpty(),
            'bentrok' => $bentrok,
            'ruang_terpakai' => $ruangTerpakai,
            'alat' => $alat,
            'jam_operasional' => $jam?->aktif ? ['buka' => substr($jam->buka, 0, 5), 'tutup' => substr($jam->tutup, 0, 5)] : null,
            'libur' => HariLibur::whereDate('tanggal', $tanggal->toDateString())->value('keterangan'),
        ]);
    }

    public static function baris(Peminjaman $p): array
    {
        return [
            'id' => $p->id,
            'peminjam' => $p->pengguna?->name,
            'nim_nip' => $p->pengguna?->nim_nip,
            'ruang' => $p->ruang?->nama,
            'alat' => $p->ringkasan_alat,
            'tanggal' => $p->waktu_mulai->translatedFormat('D, d M Y'),
            'jam' => $p->waktu_mulai->format('H:i') . '–' . $p->waktu_selesai->format('H:i'),
            'waktu_mulai' => $p->waktu_mulai->toIso8601String(),
            'jumlah_peserta' => $p->jumlah_peserta,
            'keperluan' => $p->keperluan,
            'status' => $p->status,
            'status_label' => $p->statusRef->label,
            'status_warna' => $p->statusRef->warna,
            'denda' => $p->denda,
            'denda_lunas' => $p->denda_lunas,
            'url' => route('peminjaman.show', $p),
        ];
    }

    public static function kop(): array
    {
        $logo = Pengaturan::ambil('aplikasi.logo');

        return [
            'instansi' => Pengaturan::ambil('aplikasi.nama_instansi'),
            'unit' => Pengaturan::ambil('aplikasi.unit'),
            'logo' => $logo ? storage_path('app/public/' . $logo) : null,
        ];
    }
}
