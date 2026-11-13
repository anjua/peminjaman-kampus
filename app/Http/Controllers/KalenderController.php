<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Ruang;
use App\Models\StatusPeminjaman;
use Illuminate\Http\Request;

class KalenderController extends Controller
{
    public function index()
    {
        return view('kalender.index', [
            'ruang' => Ruang::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'status' => StatusPeminjaman::whereIn('kode', Peminjaman::AKTIF)->orderBy('urutan')->get(),
        ]);
    }

    public function acara(Request $request)
    {
        $request->validate(['start' => ['required', 'date'], 'end' => ['required', 'date']]);
        $lihatSemua = $request->user()->can('lihat-semua-peminjaman');

        return Peminjaman::with('ruang', 'statusRef', 'pengguna')
            ->whereIn('status', Peminjaman::AKTIF)
            ->whereNotNull('ruang_id')
            ->where('waktu_mulai', '<', $request->end)
            ->where('waktu_selesai', '>', $request->start)
            ->when($request->ruang_id, fn($q, $r) => $q->where('ruang_id', $r))
            ->get()
            ->map(fn(Peminjaman $p) => [
                'id' => $p->id,
                // Peminjam lain hanya melihat "Terpakai", petugas melihat nama peminjam
                'title' => $p->ruang->nama . ($lihatSemua || $p->pengguna_id === $request->user()->id ? ' · ' . $p->pengguna->name : ''),
                'start' => $p->waktu_mulai->format('Y-m-d\TH:i:s'), // tanpa zona waktu: tampil sesuai jam kampus
                'end' => $p->waktu_selesai->format('Y-m-d\TH:i:s'),
                'url' => $lihatSemua || $p->pengguna_id === $request->user()->id ? route('peminjaman.show', $p) : null,
                'extendedProps' => ['calendar' => $p->statusRef->warna, 'status' => $p->statusRef->label],
            ]);
    }
}
