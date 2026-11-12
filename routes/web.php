<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('peminjaman/cek-ketersediaan', [PeminjamanController::class, 'cekKetersediaan'])->name('peminjaman.cek');
    Route::get('peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create')->middleware('hak:ajukan-peminjaman');
    Route::post('peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store')->middleware('hak:ajukan-peminjaman');
    Route::get('peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');
    Route::patch('peminjaman/{peminjaman}/batal', [PeminjamanController::class, 'batal'])->name('peminjaman.batal');
    Route::get('peminjaman/{peminjaman}/bukti', [PeminjamanController::class, 'bukti'])->name('peminjaman.bukti');

    Route::get('kalender', [KalenderController::class, 'index'])->name('kalender.index');
    Route::get('kalender/acara', [KalenderController::class, 'acara'])->name('kalender.acara');

    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::get('notifikasi/{id}', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');

    Route::prefix('admin')->name('admin.')->middleware('hak:akses-panel-admin')->group(function () {
        Route::middleware('hak:kelola-ruang')->group(function () {
            Route::get('ruang', [Admin\RuangController::class, 'index'])->name('ruang.index');
            Route::post('ruang', [Admin\RuangController::class, 'store'])->name('ruang.store');
            Route::post('ruang/{ruang}', [Admin\RuangController::class, 'update'])->name('ruang.update'); // POST karena membawa file
            Route::patch('ruang/{ruang}/aktif', [Admin\RuangController::class, 'aktif'])->name('ruang.aktif');
        });

        Route::middleware('hak:kelola-alat')->group(function () {
            Route::get('alat', [Admin\AlatController::class, 'index'])->name('alat.index');
            Route::post('alat', [Admin\AlatController::class, 'store'])->name('alat.store');
            Route::put('alat/{alat}', [Admin\AlatController::class, 'update'])->name('alat.update');
            Route::patch('alat/{alat}/aktif', [Admin\AlatController::class, 'aktif'])->name('alat.aktif');
        });

        Route::middleware('hak:proses-persetujuan')->group(function () {
            Route::get('persetujuan', [Admin\PersetujuanController::class, 'index'])->name('persetujuan.index');
            Route::patch('persetujuan/{peminjaman}/setujui', [Admin\PersetujuanController::class, 'setujui'])->name('persetujuan.setujui');
            Route::patch('persetujuan/{peminjaman}/tolak', [Admin\PersetujuanController::class, 'tolak'])->name('persetujuan.tolak');
        });

        Route::get('riwayat', [Admin\RiwayatController::class, 'index'])->name('riwayat.index')->middleware('hak:lihat-semua-peminjaman');

        Route::middleware('hak:catat-pengembalian')->group(function () {
            Route::get('pengembalian', [Admin\PengembalianController::class, 'index'])->name('pengembalian.index');
            Route::get('pengembalian/{peminjaman}/perkiraan', [Admin\PengembalianController::class, 'perkiraan'])->name('pengembalian.perkiraan');
            Route::patch('pengembalian/{peminjaman}', [Admin\PengembalianController::class, 'simpan'])->name('pengembalian.simpan');
        });
        Route::patch('denda/{peminjaman}/lunas', [Admin\PengembalianController::class, 'lunas'])->name('denda.lunas')->middleware('hak:kelola-denda');

        Route::middleware('hak:lihat-laporan')->group(function () {
            Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
            Route::get('laporan/pdf', [Admin\LaporanController::class, 'pdf'])->name('laporan.pdf');
            Route::get('laporan/excel', [Admin\LaporanController::class, 'excel'])->name('laporan.excel');
        });

        Route::middleware('hak:kelola-pengaturan')->group(function () {
            Route::get('pengaturan', [Admin\PengaturanController::class, 'index'])->name('pengaturan.index');
            Route::post('pengaturan', [Admin\PengaturanController::class, 'simpan'])->name('pengaturan.simpan');
            Route::put('jam-operasional', [Admin\PengaturanController::class, 'jamOperasional'])->name('jam-operasional.simpan');
            Route::post('hari-libur', [Admin\PengaturanController::class, 'tambahLibur'])->name('hari-libur.store');
            Route::delete('hari-libur/{hariLibur}', [Admin\PengaturanController::class, 'hapusLibur'])->name('hari-libur.destroy');
            Route::put('status-peminjaman', [Admin\PengaturanController::class, 'status'])->name('status.simpan');
            Route::get('periode', [Admin\PeriodeAkademikController::class, 'index'])->name('periode.index');
            Route::post('periode', [Admin\PeriodeAkademikController::class, 'store'])->name('periode.store');
            Route::put('periode/{periode}', [Admin\PeriodeAkademikController::class, 'update'])->name('periode.update');
            Route::delete('periode/{periode}', [Admin\PeriodeAkademikController::class, 'destroy'])->name('periode.destroy');
        });

        Route::middleware('hak:kelola-template-notifikasi')->group(function () {
            Route::get('template-notifikasi', [Admin\TemplateNotifikasiController::class, 'index'])->name('template.index');
            Route::put('template-notifikasi/{template}', [Admin\TemplateNotifikasiController::class, 'update'])->name('template.update');
        });

        Route::middleware('hak:kelola-peran')->group(function () {
            Route::get('peran', [Admin\PeranController::class, 'index'])->name('peran.index');
            Route::post('peran', [Admin\PeranController::class, 'store'])->name('peran.store');
            Route::put('peran/{peran}', [Admin\PeranController::class, 'update'])->name('peran.update');
            Route::delete('peran/{peran}', [Admin\PeranController::class, 'destroy'])->name('peran.destroy');
        });

        Route::middleware('hak:kelola-pengguna')->group(function () {
            Route::get('pengguna', [Admin\PenggunaController::class, 'index'])->name('pengguna.index');
            Route::put('pengguna/{pengguna}', [Admin\PenggunaController::class, 'update'])->name('pengguna.update');
        });
    });
});
