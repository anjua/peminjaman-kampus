<?php

namespace App\Providers;

use App\Models\Pengaturan;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Gate::before(fn(User $user, string $hak) => $user->punyaHakAkses($hak) ? true : null);

        View::composer('layouts.partials.menu', function ($view) {
            $view->with('jumlahMenunggu', auth()->user()?->can('proses-persetujuan')
                ? Peminjaman::where('status', 'menunggu')->count() : 0);
        });
        View::composer(['layouts.app', 'layouts.auth', 'auth.*'], function ($view) {
            $logoAplikasi = Pengaturan::ambil('aplikasi.logo_aplikasi');
            $view->with('namaInstansi', Pengaturan::ambil('aplikasi.nama_instansi'))
                ->with('namaAplikasi', Pengaturan::ambil('aplikasi.nama_aplikasi'))
                ->with('logoUrl', $logoAplikasi ? asset('storage/' . $logoAplikasi) : asset('img/logo.svg'));
        });
    }
}
