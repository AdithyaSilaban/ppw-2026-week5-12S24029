<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIPUS-Del' }} | Institut Teknologi Del</title>
    
    <!-- Favicon Buku -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .navbar-del { 
            background-color: #4C1D95; 
        }
        .bg-del {
            background-color: #4C1D95 !important;
            color: #ffffff !important;
        }
        .btn-del { 
            background-color: #4C1D95; 
            color: #ffffff; 
            border: 1px solid #4C1D95;
        }
        .btn-del:hover { 
            background-color: #3B0764; 
            border-color: #3B0764;
            color: #ffffff; 
        }
        .nav-link.active {
            font-weight: 600;
            border-bottom: 2px solid #ffffff;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Bilah Navigasi Del Purple -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">
                📚 SIPUS-Del
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buku.*') ? 'active text-white' : 'text-white-50' }}" href="{{ route('buku.index') }}">
                            Katalog Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active text-white' : 'text-white-50' }}" href="{{ route('kategori.index') }}">
                            Kelola Kategori
                        </a>
                    </li>
                    @if(Route::has('peminjaman.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('peminjaman.*') ? 'active text-white' : 'text-white-50' }}" href="{{ route('peminjaman.index') }}">
                                Sirkulasi Peminjaman
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- Panel Autentikasi Sesi Pustakawan -->
                <div class="d-flex align-items-center gap-3">
                    @auth
                        <span class="badge bg-light text-dark px-3 py-2 shadow-sm border">
                            👤 {{ Auth::user()->name }} 
                            <span class="text-muted ms-1" style="font-size: 0.8rem;">({{ session('login_timestamp', 'Online') }})</span>
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm fw-semibold">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-light btn-sm fw-bold px-3 shadow-sm" style="color: #4C1D95;">
                            Masuk Pustakawan
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Kontainer Konten Utama & Flash Messages -->
    <main class="container pb-5">
        <!-- Notifikasi Sukses -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        <!-- Notifikasi Gagal / Ditolak -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>