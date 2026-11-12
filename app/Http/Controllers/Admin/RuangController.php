<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RuangRequest;
use App\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RuangController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('admin.ruang.index');
        }

        return response()->json(['data' => Ruang::withCount('peminjaman')->orderBy('nama')->get()]);
    }

    public function store(RuangRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('ruang', 'public');
        }
        Ruang::create($data);

        return response()->json(['pesan' => 'Ruang ditambahkan.'], 201);
    }

    public function update(RuangRequest $request, Ruang $ruang)
    {
        $data = $request->validated();
        if ($request->hasFile('foto')) {
            if ($ruang->foto) {
                Storage::disk('public')->delete($ruang->foto);
            }
            $data['foto'] = $request->file('foto')->store('ruang', 'public');
        }
        $ruang->update($data);

        return response()->json(['pesan' => 'Ruang diperbarui.']);
    }

    public function aktif(Ruang $ruang)
    {
        $ruang->update(['aktif' => !$ruang->aktif]);

        return response()->json(['pesan' => $ruang->aktif ? 'Ruang diaktifkan.' : 'Ruang dinonaktifkan.']);
    }
}
