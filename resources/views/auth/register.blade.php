@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
    <div class="authentication-inner row">
        <div class="d-none d-lg-flex col-lg-7 p-0">
            <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                <img src="{{ asset('assets/img/illustrations/auth-register-illustration-light.png') }}" alt="ilustrasi"
                    class="img-fluid my-5 auth-illustration"
                    data-app-light-img="illustrations/auth-register-illustration-light.png"
                    data-app-dark-img="illustrations/auth-register-illustration-dark.png" />
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="" class="platform-bg"
                    data-app-light-img="illustrations/bg-shape-image-light.png"
                    data-app-dark-img="illustrations/bg-shape-image-dark.png" />
            </div>
        </div>

        <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
            <div class="w-px-400 mx-auto">
                <div class="app-brand mb-4">
                    <a href="{{ url('/') }}" class="app-brand-link gap-2">
                        <img src="{{ $logoUrl }}" alt="" width="40" height="40" style="object-fit: contain">
                        <span class="d-flex flex-column lh-sm">
                            <span class="text-body fw-bold fs-4">{{ $namaAplikasi }}</span>
                            <small class="text-muted">{{ $namaInstansi }}</small>
                        </span>
                    </a>
                </div>
                <h3 class="mb-1 fw-bold">Buat akun peminjam 🚀</h3>
                <p class="mb-4">Gunakan email kampus dan NIM/NIP kamu.</p>

                <form class="mb-3" action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name') }}" autofocus required />
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nim_nip" class="form-label">NIM / NIP</label>
                        <input type="text" class="form-control @error('nim_nip') is-invalid @enderror" id="nim_nip"
                            name="nim_nip" value="{{ old('nim_nip') }}" required />
                        @error('nim_nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" required />
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="password_confirmation">Ulangi password</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password_confirmation" class="form-control"
                                name="password_confirmation" required />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100">Daftar</button>
                </form>

                <p class="text-center">
                    <span>Sudah punya akun?</span>
                    <a href="{{ route('login') }}"><span>Masuk</span></a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection