<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanPeminjamanExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PeminjamanController;
use App\Models\Alat;
use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\Peran;
use App\Models\PeriodeAkademik;
use App\Models\Ruang;
use App\Models\StatusPeminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.laporan.index', [
                'periode' => PeriodeAkademik::where('aktif', true)->orderByDesc('tanggal_mulai')->get(),
                'ruang' => Ruang::orderBy('nama')->get(['id', 'nama']),
                'alat' => Alat::orderBy('nama')->get(['id', 'nama']),
                'status' => StatusPeminjaman::orderBy('urutan')->get(),
                'peran' => Peran::orderBy('nama')->get(['id', 'nama']),
                'katalogKolom' => LaporanPeminjamanExport::katalogJudul(),
                'kolomDefault' => Pengaturan::ambil('laporan.kolom_ekspor'),
            ]);
        }

        [$dari, $sampai] = $this->rentang($request);
        $q = fn() => $this->query($request);
        $status = StatusPeminjaman::orderBy('urutan')->get();
        $perStatus = $q()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $perHari = $q()->selectRaw('waktu_mulai::date as tanggal, count(*) as jumlah')
            ->groupBy('tanggal')->orderBy('tanggal')->pluck('jumlah', 'tanggal');

        return response()->json([
            'judul' => $dari->translatedFormat('d M Y') . ' – ' . $sampai->translatedFormat('d M Y'),
            'total' => $perStatus->sum(),
            'per_status' => $status->map(fn($s) => ['label' => $s->label, 'warna' => $s->warna, 'jumlah' => (int) ($perStatus[$s->kode] ?? 0)]),
            'per_hari' => ['tanggal' => $perHari->keys(), 'jumlah' => $perHari->values()],
            'ruang_teratas' => $q()->whereNotNull('ruang_id')->selectRaw('ruang_id, count(*) as jumlah')
                ->groupBy('ruang_id')->orderByDesc('jumlah')->take(5)->get()
                ->map(fn($r) => ['nama' => Ruang::find($r->ruang_id)->nama, 'jumlah' => $r->jumlah]),
            'total_denda' => (int) $q()->sum('denda'),
            'baris' => $q()->with('pengguna', 'ruang', 'detail.alat', 'statusRef')->orderBy('waktu_mulai')->get()
                ->map(fn($p) => PeminjamanController::baris($p)),
        ]);
    }

    public function excel(Request $request)
    {
        return Excel::download(
            new LaporanPeminjamanExport($this->query($request), $this->kolom($request)),
            'laporan-peminjaman-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
        $export = new LaporanPeminjamanExport($this->query($request), $this->kolom($request));

        return Pdf::loadView('pdf.laporan', [
            'kop' => PeminjamanController::kop(),
            'periode' => $dari->translatedFormat('d F Y') . ' s.d. ' . $sampai->translatedFormat('d F Y'),
            'judul' => $export->headings(),
            'baris' => $export->collection(),
        ])->setPaper('a4', 'landscape')->download('laporan-peminjaman-' . now()->format('Ymd-His') . '.pdf');
    }

    private function rentang(Request $r): array
    {
        $periode = $r->periode_id ? PeriodeAkademik::findOrFail($r->periode_id) : null;
        $dari = Carbon::parse($periode?->tanggal_mulai ?? $r->input('dari') ?: now()->startOfMonth())->startOfDay();
        $sampai = Carbon::parse($periode?->tanggal_selesai ?? $r->input('sampai') ?: now()->endOfMonth())->endOfDay();

        return [$dari, $sampai];
    }

    private function query(Request $r)
    {
        [$dari, $sampai] = $this->rentang($r);

        return Peminjaman::whereBetween('waktu_mulai', [$dari, $sampai])
            ->when($r->ruang_id, fn($q, $v) => $q->whereIn('ruang_id', (array) $v))
            ->when($r->alat_id, fn($q, $v) => $q->whereHas('detail', fn($d) => $d->whereIn('alat_id', (array) $v)))
            ->when($r->status, fn($q, $v) => $q->whereIn('status', (array) $v))
            ->when($r->peran_id, fn($q, $v) => $q->whereHas('pengguna', fn($p) => $p->whereIn('peran_id', (array) $v)));
    }

    private function kolom(Request $r): array
    {
        $kolom = (array) $r->input('kolom', Pengaturan::ambil('laporan.kolom_ekspor'));

        return array_values(array_intersect($kolom, array_keys(LaporanPeminjamanExport::katalogJudul())));
    }
}
