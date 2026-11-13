<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodeAkademikController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.periode.index');
        }

        return response()->json(['data' => PeriodeAkademik::orderByDesc('tanggal_mulai')->get()]);
    }

    public function store(Request $request)
    {
        PeriodeAkademik::create($this->validasi($request));

        return response()->json(['pesan' => 'Periode ditambahkan.'], 201);
    }

    public function update(Request $request, PeriodeAkademik $periode)
    {
        $periode->update($this->validasi($request, $periode));

        return response()->json(['pesan' => 'Periode diperbarui.']);
    }

    public function destroy(PeriodeAkademik $periode)
    {
        $periode->delete();

        return response()->json(['pesan' => 'Periode dihapus.']);
    }

    private function validasi(Request $request, ?PeriodeAkademik $periode = null): array
    {
        return $request->validate([
            'nama' => ['required', 'max:100', Rule::unique('periode_akademik', 'nama')->ignore($periode)],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'aktif' => ['boolean'],
        ]);
    }
}
