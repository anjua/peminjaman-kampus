<!DOCTYPE html>
<html lang="id" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-starter">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="url-notifikasi" content="{{ route('notifikasi.index') }}" />
    <meta name="url-baca-semua" content="{{ route('notifikasi.baca-semua') }}" />
    <meta name="polling-detik" content="{{ \App\Models\Pengaturan::ambil('notifikasi.polling_detik') }}" />
    <title>@yield('title') | {{ $namaAplikasi }}</title>
    <link rel="icon" href="{{ $logoUrl }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS (dipakai semua halaman) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />

    <!-- CSS khusus halaman -->
    @stack('page-css')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('layouts.partials.menu')

            <div class="layout-page">
                @include('layouts.partials.navbar')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        {{-- Breadcrumb dan judul halaman --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                            <div>
                                <h4 class="fw-bold mb-1">@yield('title')</h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                                        @yield('breadcrumb')
                                        <li class="breadcrumb-item active">@yield('title')</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="d-flex gap-2">@yield('aksi')</div>
                        </div>

                        @yield('content')
                    </div>

                    @include('layouts.partials.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="wadah-toast" style="z-index: 1100"></div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>

    @stack('vendor-js')

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => toast('success', @json(session('success'))));
    </script>
    @endif

    @stack('page-js')
</body>

</html>