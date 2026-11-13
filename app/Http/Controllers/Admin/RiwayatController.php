<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PeminjamanController;
use App\Models\Peminjaman;
use App\Models\StatusPeminjaman;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.riwayat.index', ['status' => StatusPeminjaman::orderBy('urutan')->get()]);
        }

        $data = Peminjaman::with('pengguna', 'ruang', 'detail.alat', 'statusRef')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->dari, fn($q, $d) => $q->whereDate('waktu_mulai', '>=', $d))
            ->when($request->sampai, fn($q, $d) => $q->whereDate('waktu_mulai', '<=', $d))
            ->latest('waktu_mulai')->get()
            ->map(fn(Peminjaman $p) => PeminjamanController::baris($p));

        return response()->json(['data' => $data]);
    }
}
