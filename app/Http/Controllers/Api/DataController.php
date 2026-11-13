<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Ruang;

class DataController extends Controller
{
    public function ruang()
    {
        return response()->json(['data' => Ruang::where('aktif', true)->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'lokasi', 'kapasitas', 'fasilitas'])]);
    }

    public function alat()
    {
        return response()->json(['data' => Alat::where('aktif', true)->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'stok', 'kondisi'])]);
    }
}
