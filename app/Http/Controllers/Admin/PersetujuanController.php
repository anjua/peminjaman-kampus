<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PeminjamanController;
use App\Models\Peminjaman;
use App\Services\Notifikasi;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.persetujuan.index');
        }

        $data = Peminjaman::with('pengguna', 'ruang', 'detail.alat', 'statusRef')
            ->where('status', 'menunggu')->orderBy('waktu_mulai')->get()
            ->map(fn(Peminjaman $p) => PeminjamanController::baris($p) + [
                'url_setujui' => route('admin.persetujuan.setujui', $p),
                'url_tolak' => route('admin.persetujuan.tolak', $p),
            ]);

        return response()->json(['data' => $data]);
    }

    public function setujui(Peminjaman $peminjaman)
    {
        abort_unless($peminjaman->status === 'menunggu', 422, 'Pengajuan ini sudah diproses.');
        $peminjaman->update(['status' => 'disetujui', 'disetujui_oleh' => auth()->id(), 'disetujui_pada' => now()]);
        Notifikasi::kirim('disetujui', $peminjaman);

        return response()->json(['pesan' => 'Peminjaman disetujui.']);
    }

    public function tolak(Request $request, Peminjaman $peminjaman)
    {
        abort_unless($peminjaman->status === 'menunggu', 422, 'Pengajuan ini sudah diproses.');
        $request->validate(['catatan_admin' => ['required', 'string', 'max:500']], [], ['catatan_admin' => 'alasan']);
        $peminjaman->update([
            'status' => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);
        Notifikasi::kirim('ditolak', $peminjaman);

        return response()->json(['pesan' => 'Peminjaman ditolak.']);
    }
}
