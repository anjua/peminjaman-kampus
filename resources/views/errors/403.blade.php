@extends('layouts.auth')

@section('title', 'Akses ditolak')

@section('content')
<div class="container-xxl container-p-y">
    <div class="misc-wrapper text-center py-5">
        <h2 class="mb-1 mx-2">Akses ditolak 🔐</h2>
        <p class="mb-4 mx-2">{{ $exception->getMessage() ?: 'Kamu tidak punya hak akses untuk halaman ini.' }}</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary mb-4">Kembali ke dashboard</a>
        <div class="mt-4">
            <img src="{{ asset('assets/img/illustrations/page-misc-you-are-not-authorized.png') }}" alt="" width="170"
                class="img-fluid" />
        </div>
    </div>
</div>
@endsection