<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'jumlah' => $user->unreadNotifications()->count(),
            'data' => $user->notifications()->latest()->take(8)->get()->map(fn($n) => [
                'id' => $n->id,
                'judul' => $n->data['judul'],
                'pesan' => $n->data['pesan'],
                'ikon' => $n->data['ikon'],
                'warna' => $n->data['warna'],
                'waktu' => $n->created_at->diffForHumans(),
                'dibaca' => (bool) $n->read_at,
                'url' => route('notifikasi.buka', $n->id),
            ]),
        ]);
    }

    public function buka(Request $request, string $id)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return redirect($n->data['url']);
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['pesan' => 'Semua notifikasi ditandai dibaca.']);
    }
}
