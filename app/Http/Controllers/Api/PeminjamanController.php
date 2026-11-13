<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeminjamanRequest;
use App\Http\Resources\PeminjamanResource;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        return PeminjamanResource::collection(
            $request->user()->peminjaman()->with('ruang', 'detail.alat', 'statusRef')->latest('waktu_mulai')->paginate(20)
        );
    }

    public function store(PeminjamanRequest $request, PeminjamanService $service)
    {
        $p = $service->ajukan($request->user(), $request->validated());

        return (new PeminjamanResource($p->load('ruang', 'detail.alat', 'statusRef')))->response()->setStatusCode(201);
    }

    public function show(Peminjaman $peminjaman)
    {
        Gate::authorize('view', $peminjaman);

        return new PeminjamanResource($peminjaman->load('ruang', 'detail.alat', 'statusRef'));
    }
}
