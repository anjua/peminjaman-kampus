<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ $logoUrl }}" alt="" width="30" height="30" style="object-fit: contain">
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3"
                  title="{{ $namaAplikasi }}">{{ $namaAplikasi }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Peminjaman</span></li>
        @can('ajukan-peminjaman')
        <li class="menu-item {{ request()->routeIs('peminjaman.create') ? 'active' : '' }}">
            <a href="{{ route('peminjaman.create') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-calendar-plus"></i>
                <div>Ajukan Peminjaman</div>
            </a>
        </li>
        @endcan
        <li class="menu-item {{ request()->routeIs('peminjaman.index', 'peminjaman.show') ? 'active' : '' }}">
            <a href="{{ route('peminjaman.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-history"></i>
                <div>Peminjaman Saya</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('kalender.*') ? 'active' : '' }}">
            <a href="{{ route('kalender.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-calendar"></i>
                <div>Kalender Ruang</div>
            </a>
        </li>

        @can('akses-panel-admin')
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengelolaan</span></li>
        @can('proses-persetujuan')
        <li class="menu-item {{ request()->routeIs('admin.persetujuan.*') ? 'active' : '' }}">
            <a href="{{ route('admin.persetujuan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-checklist"></i>
                <div>Persetujuan</div>
                @if ($jumlahMenunggu)
                <div class="badge bg-label-warning rounded-pill ms-auto" id="badge-menunggu">{{ $jumlahMenunggu }}</div>
                @endif
            </a>
        </li>
        @endcan
        @can('catat-pengembalian')
        <li class="menu-item {{ request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">
            <a href="{{ route('admin.pengembalian.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-arrow-back-up"></i>
                <div>Pengembalian &amp; Denda</div>
            </a>
        </li>
        @endcan
        @can('lihat-semua-peminjaman')
        <li class="menu-item {{ request()->routeIs('admin.riwayat.*') ? 'active' : '' }}">
            <a href="{{ route('admin.riwayat.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-list-details"></i>
                <div>Semua Peminjaman</div>
            </a>
        </li>
        @endcan
        @can('lihat-laporan')
        <li class="menu-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <a href="{{ route('admin.laporan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-report-analytics"></i>
                <div>Laporan</div>
            </a>
        </li>
        @endcan
        @can('kelola-ruang')
        <li class="menu-item {{ request()->routeIs('admin.ruang.*') ? 'active' : '' }}">
            <a href="{{ route('admin.ruang.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-door"></i>
                <div>Ruang</div>
            </a>
        </li>
        @endcan
        @can('kelola-alat')
        <li class="menu-item {{ request()->routeIs('admin.alat.*') ? 'active' : '' }}">
            <a href="{{ route('admin.alat.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-presentation"></i>
                <div>Alat</div>
            </a>
        </li>
        @endcan

        @if (auth()->user()->can('kelola-pengaturan') || auth()->user()->can('kelola-peran') ||
        auth()->user()->can('kelola-pengguna') || auth()->user()->can('kelola-template-notifikasi'))
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Sistem</span></li>
        @endif
        @can('kelola-pengguna')
        <li class="menu-item {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
            <a href="{{ route('admin.pengguna.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>Pengguna</div>
            </a>
        </li>
        @endcan
        @can('kelola-peran')
        <li class="menu-item {{ request()->routeIs('admin.peran.*') ? 'active' : '' }}">
            <a href="{{ route('admin.peran.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-shield-lock"></i>
                <div>Peran &amp; Hak Akses</div>
            </a>
        </li>
        @endcan
        @can('kelola-template-notifikasi')
        <li class="menu-item {{ request()->routeIs('admin.template.*') ? 'active' : '' }}">
            <a href="{{ route('admin.template.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-bell-ringing"></i>
                <div>Template Notifikasi</div>
            </a>
        </li>
        @endcan
        @can('kelola-pengaturan')
        <li class="menu-item {{ request()->routeIs('admin.periode.*') ? 'active' : '' }}">
            <a href="{{ route('admin.periode.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-calendar-stats"></i>
                <div>Periode Akademik</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}">
            <a href="{{ route('admin.pengaturan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>Pengaturan</div>
            </a>
        </li>
        @endcan
        @endcan
    </ul>
</aside>