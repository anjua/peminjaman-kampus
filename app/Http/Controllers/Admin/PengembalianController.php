<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PeminjamanController;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Services\DendaService;
use App\Services\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengembalianController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.pengembalian.index', ['kondisi' => Pengaturan::ambil('pengembalian.pilihan_kondisi')]);
        }

        $q = Peminjaman::with('pengguna', 'ruang', 'detail.alat', 'statusRef')->whereHas('detail');

        $q = match ($request->tab) {
            'belum' => $q->where('status', 'disetujui')->whereNull('dikembalikan_pada')
                ->where('waktu_mulai', '<=', now())->orderBy('waktu_selesai'),
            'denda' => $q->where('denda_lunas', false)->latest('dikembalikan_pada'),
            default => $q->whereNotNull('dikembalikan_pada')->latest('dikembalikan_pada'),
        };

        return response()->json(['data' => $q->get()->map(fn(Peminjaman $p) => PeminjamanController::baris($p) + [
            'waktu_selesai' => $p->waktu_selesai->toIso8601String(),
            'terlambat_menit' => $p->terlambat ? (int) $p->waktu_selesai->diffInMinutes(now()) : 0,
            'dikembalikan_pada' => $p->dikembalikan_pada?->translatedFormat('d M Y H:i'),
            'kondisi_kembali' => $p->kondisi_kembali,
            'url_perkiraan' => route('admin.pengembalian.perkiraan', $p),
            'url_simpan' => route('admin.pengembalian.simpan', $p),
            'url_lunas' => route('admin.denda.lunas', $p),
        ])]);
    }

    public function perkiraan(Peminjaman $peminjaman, DendaService $denda)
    {
        return response()->json(['denda' => $denda->hitung($peminjaman, now())]);
    }

    public function simpan(Request $request, Peminjaman $peminjaman, DendaService $denda)
    {
        abort_unless($peminjaman->status === 'disetujui' && !$peminjaman->dikembalikan_pada, 422, 'Peminjaman ini tidak bisa dicatat pengembaliannya.');
        $request->validate([
            'kondisi_kembali' => ['required', Rule::in(array_keys(Pengaturan::ambil('pengembalian.pilihan_kondisi')))],
            'catatan_admin' => ['nullable', 'string', 'max:500'],
        ], [], ['kondisi_kembali' => 'kondisi']);

        $nilai = $denda->hitung($peminjaman, now());
        $peminjaman->update([
            'status' => 'selesai',
            'dikembalikan_pada' => now(),
            'kondisi_kembali' => $request->kondisi_kembali,
            'catatan_admin' => $request->catatan_admin ?? $peminjaman->catatan_admin,
            'denda' => $nilai,
            'denda_lunas' => $nilai === 0,
        ]);
        if ($nilai > 0) {
            Notifikasi::kirim('denda_dicatat', $peminjaman);
        }

        return response()->json([
            'pesan' => $nilai > 0 ? 'Pengembalian dicatat dengan denda Rp' . number_format($nilai, 0, ',', '.') . '.' : 'Pengembalian dicatat.',
        ]);
    }

    public function lunas(Peminjaman $peminjaman)
    {
        $peminjaman->update(['denda_lunas' => true]);

        return response()->json(['pesan' => 'Denda ditandai lunas.']);
    }
}
