<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\JamOperasional;
use App\Models\Pengaturan;
use App\Models\Peran;
use App\Models\StatusPeminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan.index', [
            'grup' => Pengaturan::orderBy('grup')->orderBy('urutan')->get()->groupBy('grup'),
            'jam' => JamOperasional::orderBy('hari')->get(),
            'libur' => HariLibur::orderBy('tanggal')->get(),
            'status' => StatusPeminjaman::orderBy('urutan')->get(),
            'peran' => Peran::orderBy('nama')->get(),
        ]);
    }

    public function simpan(Request $request)
    {
        $id = array_unique(array_merge(array_keys($request->input('nilai', [])), array_keys($request->file('nilai', []))));
        $daftar = Pengaturan::whereIn('id', $id)->get();

        $aturan = $daftar->mapWithKeys(fn(Pengaturan $p) => ["nilai.{$p->id}" => match ($p->tipe) {
            'integer' => ['required', 'integer', 'min:0'],
            'boolean' => ['required', 'boolean'],
            'time' => ['required', 'date_format:H:i'],
            'json' => ['required', 'json'],
            'file' => ['nullable', 'image', 'max:1024'],
            default => $p->kunci === 'aplikasi.peran_registrasi'
                ? ['required', Rule::exists('peran', 'kode')]
                : ['required', 'string', 'max:255'],
        }])->all();
        $label = $daftar->mapWithKeys(fn($p) => ["nilai.{$p->id}" => strtolower($p->label)])->all();
        $request->validate($aturan, [], $label);

        foreach ($daftar as $p) {
            if ($p->tipe === 'file') {
                if (!$request->hasFile("nilai.{$p->id}")) {
                    continue;
                }
                if ($p->nilai) {
                    Storage::disk('public')->delete($p->nilai);
                }
                $p->nilai = $request->file("nilai.{$p->id}")->store('pengaturan', 'public');
            } else {
                $p->nilai = $request->input("nilai.{$p->id}");
            }
            $p->save(); 
        }

        return response()->json(['pesan' => 'Pengaturan disimpan.']);
    }

    public function jamOperasional(Request $request)
    {
        $request->validate([
            'jam' => ['required', 'array'],
            'jam.*.buka' => ['required', 'date_format:H:i'],
            'jam.*.tutup' => ['required', 'date_format:H:i'],
            'jam.*.aktif' => ['required', 'boolean'],
        ]);
        foreach ($request->jam as $hari => $baris) {
            if ($baris['tutup'] <= $baris['buka']) {
                return response()->json(['message' => 'Jam tutup harus setelah jam buka.', 'errors' => ["jam.{$hari}.tutup" => ['Jam tutup harus setelah jam buka.']]], 422);
            }
            JamOperasional::where('hari', $hari)->update($baris);
        }

        return response()->json(['pesan' => 'Jam operasional disimpan.']);
    }

    public function tambahLibur(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'unique:hari_libur,tanggal'],
            'keterangan' => ['required', 'string', 'max:150'],
        ]);
        $libur = HariLibur::create($data);

        return response()->json(['pesan' => 'Hari libur ditambahkan.', 'data' => $libur], 201);
    }

    public function hapusLibur(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return response()->json(['pesan' => 'Hari libur dihapus.']);
    }

    public function status(Request $request)
    {
        $request->validate([
            'status' => ['required', 'array'],
            'status.*.label' => ['required', 'string', 'max:50'],
            'status.*.warna' => ['required', Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'])],
        ]);
        foreach ($request->status as $kode => $baris) {
            StatusPeminjaman::where('kode', $kode)->update($baris);
        }

        return response()->json(['pesan' => 'Label dan warna status disimpan.']);
    }
}
