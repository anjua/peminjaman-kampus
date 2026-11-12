<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlatRequest;
use App\Models\Alat;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.alat.index', [
                'kondisi' => Pengaturan::ambil('pengembalian.pilihan_kondisi'),
                'tarifUmum' => Pengaturan::ambil('denda.per_jam'),
            ]);
        }

        return response()->json(['data' => Alat::orderBy('nama')->get()->map(fn(Alat $a) => $a->toArray() + [
            'dipinjam_sekarang' => $a->terpakaiPada(now(), now()->addMinute()),
        ])]);
    }

    public function store(AlatRequest $request)
    {
        Alat::create($request->validated());

        return response()->json(['pesan' => 'Alat ditambahkan.'], 201);
    }

    public function update(AlatRequest $request, Alat $alat)
    {
        $alat->update($request->validated());

        return response()->json(['pesan' => 'Alat diperbarui.']);
    }

    public function aktif(Alat $alat)
    {
        $alat->update(['aktif' => !$alat->aktif]);

        return response()->json(['pesan' => $alat->aktif ? 'Alat diaktifkan.' : 'Alat dinonaktifkan.']);
    }
}
