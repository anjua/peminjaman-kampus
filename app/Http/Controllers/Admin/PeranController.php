<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Pengaturan;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeranController extends Controller
{
    public function index()
    {
        return view('admin.peran.index', [
            'peran' => Peran::with('hakAkses:id')->withCount('pengguna')
                ->with(['pengguna' => fn($q) => $q->select('id', 'name', 'peran_id')->latest()->take(4)])
                ->orderBy('nama')->get(),
            'hakAkses' => HakAkses::orderBy('grup')->orderBy('nama')->get()->groupBy('grup'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $peran = Peran::create($data);
        $peran->hakAkses()->sync($request->input('hak_akses', []));

        return response()->json(['pesan' => "Peran {$peran->nama} ditambahkan."], 201);
    }

    public function update(Request $request, Peran $peran)
    {
        $data = $this->validasi($request, $peran);
        $peran->update($data);
        $peran->hakAkses()->sync($request->input('hak_akses', []));

        return response()->json(['pesan' => "Peran {$peran->nama} diperbarui."]);
    }

    public function destroy(Peran $peran)
    {
        abort_if($peran->pengguna()->exists(), 422, 'Peran masih dipakai pengguna. Pindahkan penggunanya dulu.');
        abort_if($peran->kode === Pengaturan::ambil('aplikasi.peran_registrasi'), 422, 'Peran ini dipakai untuk akun baru (lihat Pengaturan).');
        $peran->delete();

        return response()->json(['pesan' => 'Peran dihapus.']);
    }

    private function validasi(Request $request, ?Peran $peran = null): array
    {
        return $request->validate([
            'kode' => ['required', 'alpha_dash', 'max:50', Rule::unique('peran', 'kode')->ignore($peran)],
            'nama' => ['required', 'max:100'],
            'keterangan' => ['nullable', 'max:255'],
            'hak_akses' => ['array'],
            'hak_akses.*' => ['exists:hak_akses,id'],
        ]);
    }
}
