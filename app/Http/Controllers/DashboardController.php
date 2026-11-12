<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Ruang;
use App\Models\StatusPeminjaman;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $data = ['status' => StatusPeminjaman::orderBy('urutan')->get()];

        $data['milikku'] = $user->peminjaman()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $data['terdekat'] = $user->peminjaman()->with('ruang', 'detail.alat', 'statusRef')
            ->whereIn('status', Peminjaman::AKTIF)->where('waktu_selesai', '>', now())
            ->orderBy('waktu_mulai')->take(5)->get();
        $data['dendaku'] = $user->peminjaman()->where('denda_lunas', false)->sum('denda');

        if ($user->can('akses-panel-admin')) {
            $hariIni = [now()->startOfDay(), now()->endOfDay()];
            $data['admin'] = [
                'menunggu' => Peminjaman::where('status', 'menunggu')->count(),
                'hari_ini' => Peminjaman::where('status', 'disetujui')->whereBetween('waktu_mulai', $hariIni)->count(),
                'ruang_dipakai' => Peminjaman::beririsan(now(), now()->addMinute())->whereNotNull('ruang_id')->count(),
                'ruang_total' => Ruang::where('aktif', true)->count(),
                'terlambat' => Peminjaman::where('status', 'disetujui')->whereNull('dikembalikan_pada')
                    ->where('waktu_selesai', '<', now())->whereHas('detail')->count(),
                'denda_belum_lunas' => Peminjaman::where('denda_lunas', false)->sum('denda'),
                'alat_total' => Alat::where('aktif', true)->sum('stok'),
            ];

            $awal = now()->subMonths(5)->startOfMonth();
            $baris = Peminjaman::where('waktu_mulai', '>=', $awal)
                ->selectRaw("to_char(waktu_mulai, 'YYYY-MM') as bulan, status, count(*) as jumlah")
                ->groupBy('bulan', 'status')->get();
            $bulan = collect(range(0, 5))->map(fn($i) => $awal->copy()->addMonths($i));
            $data['grafik'] = [
                'kategori' => $bulan->map(fn($b) => $b->translatedFormat('M Y')),
                'seri' => $data['status']->map(fn($s) => [
                    'name' => $s->label,
                    'data' => $bulan->map(fn($b) => (int) $baris->where('bulan', $b->format('Y-m'))->where('status', $s->kode)->sum('jumlah')),
                ]),
                'warna' => $data['status']->pluck('warna'),
            ];
            $data['ruang_populer'] = Ruang::withCount(['peminjaman' => fn($q) => $q->where('waktu_mulai', '>=', $awal)])
                ->orderByDesc('peminjaman_count')->take(5)->get();
        }

        return view('dashboard', $data);
    }
}
