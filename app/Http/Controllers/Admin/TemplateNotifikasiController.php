<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Peminjaman;
use App\Notifications\NotifikasiTemplate;
use App\Models\TemplateNotifikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateNotifikasiController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            $contoh = Peminjaman::with('pengguna', 'ruang', 'detail.alat', 'statusRef')->latest()->first();

            return view('admin.template.index', [
                'contoh' => $contoh ? NotifikasiTemplate::nilaiPenanda($contoh) : null,
                'penerima' => collect(['peminjam' => 'Peminjam yang bersangkutan'])
                    ->merge(HakAkses::orderBy('nama')->get()->mapWithKeys(fn($h) => ["hak:{$h->kode}" => "Semua yang punya hak: {$h->nama}"])),
            ]);
        }

        return response()->json(['data' => TemplateNotifikasi::orderBy('id')->get()]);
    }

    public function update(Request $request, TemplateNotifikasi $template)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:1000'],
            'ikon' => ['required', 'string', 'max:50'],
            'warna' => ['required', Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info'])],
            'kanal' => ['required', 'array', 'min:1'],
            'kanal.*' => [Rule::in(['database', 'mail'])],
            'penerima' => ['required', 'string', function ($attr, $nilai, $gagal) {
                if ($nilai !== 'peminjam' && !HakAkses::where('kode', str_replace('hak:', '', $nilai))->exists()) {
                    $gagal('Penerima tidak dikenal.');
                }
            }],
            'aktif' => ['required', 'boolean'],
        ]);
        $template->update($data);

        return response()->json(['pesan' => 'Template disimpan.']);
    }
}
