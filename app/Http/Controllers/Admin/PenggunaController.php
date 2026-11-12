<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.pengguna.index', ['peran' => Peran::orderBy('nama')->get()]);
        }

        return response()->json(['data' => User::with('peran')->withCount('peminjaman')
            ->when($request->peran_id, fn($q, $p) => $q->where('peran_id', $p))
            ->orderBy('name')->get()
            ->map(fn(User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'nim_nip' => $u->nim_nip,
                'telepon' => $u->telepon,
                'inisial' => $u->inisial,
                'aktif' => $u->aktif,
                'peran_id' => $u->peran_id,
                'peran' => $u->peran?->nama,
                'peminjaman_count' => $u->peminjaman_count,
                'url' => route('admin.pengguna.update', $u),
            ])]);
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $request->validate([
            'peran_id' => ['required', 'exists:peran,id'],
            'aktif' => ['required', 'boolean'],
            'telepon' => ['nullable', 'max:20'],
            'nim_nip' => ['nullable', 'max:30'],
        ]);
        abort_if($pengguna->is($request->user()) && !$data['aktif'], 422, 'Kamu tidak bisa menonaktifkan akunmu sendiri.');
        $pengguna->update($data);

        return response()->json(['pesan' => "Data {$pengguna->name} disimpan."]);
    }
}
