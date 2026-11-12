@extends('layouts.auth')

@section('title', 'Tidak ditemukan')

@section('content')
<div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center py-5">
        <h2 class="mb-1 mx-2">Halaman tidak ditemukan ⚠️</h2>
        <p class="mb-4 mx-2">Alamat yang kamu buka tidak ada atau sudah dipindah.</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary mb-4">Kembali ke dashboard</a>
        <div class="mt-4">
            <img src="{{ asset('assets/img/illustrations/page-misc-error.png') }}" alt="" width="225"
                class="img-fluid" />
        </div>
    </div>
</div>
@endsection