<x-layout title="Katalog Koleksi Buku">
   <!-- 4 Kartu Statistik Agregasi Basis Data (Spesifikasi Modul 3) -->
    <div class="row g-3 mb-4">
        <!-- Kartu 1: Total Judul -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-primary">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Judul Buku</span>
                    <h3 class="fw-bold mt-2 mb-0 text-dark">{{ $stats['total_judul'] }}</h3>
                    <small class="text-muted">Koleksi terdaftar</small>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Total Eksemplar Stok -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-success">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Eksemplar</span>
                    <h3 class="fw-bold mt-2 mb-0 text-success">{{ $stats['total_stok'] }}</h3>
                    <small class="text-muted">Fisik buku tersedia</small>
                </div>
            </div>
        </div>

        <!-- Kartu 3: Sedang Dipinjam -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-warning">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Sedang Dipinjam</span>
                    <h3 class="fw-bold mt-2 mb-0 text-warning">{{ $stats['sedang_dipinjam'] }}</h3>
                    <small class="text-muted">Eksemplar di luar</small>
                </div>
            </div>
        </div>

        <!-- Kartu 4: Telah Dikembalikan -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-info">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Telah Dikembalikan</span>
                    <h3 class="fw-bold mt-2 mb-0 text-info">{{ $stats['dikembalikan'] }}</h3>
                    <small class="text-muted">Riwayat sirkulasi selesai</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Header & Aksi Tambah Buku -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: #4C1D95;">Koleksi Buku Perpustakaan</h4>
            <p class="text-muted small mb-0">Daftar buku dengan penghitungan frekuensi sirkulasi peminjaman</p>
        </div>
        @auth
            <a href="{{ route('buku.create') }}" class="btn text-white fw-semibold px-3 shadow-sm rounded-3" style="background-color: #4C1D95;">
                + Tambah Koleksi Buku
            </a>
        @endauth
    </div>

    <!-- Form Pencarian & Filter Kategori -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('buku.index') }}" method="GET" class="row g-2">
                <div class="col-md-7">
                    <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan judul, penulis, atau ISBN..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn text-white w-100 fw-semibold" style="background-color: #4C1D95;">Filter</button>
                    @if(request()->hasAny(['q', 'kategori_id']))
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Buku -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">ISBN & Judul</th>
                        <th>Kategori</th>
                        <th>Penulis & Penerbit</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Total Dipinjam</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-decoration-none text-dark d-block">
                                    {{ $buku->judul }}
                                </a>
                                <small class="text-muted font-monospace">{{ $buku->isbn }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $buku->kategori->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $buku->penulis }}</div>
                                <small class="text-muted">{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})</small>
                            </td>
                            <td class="text-center">
                                @if($buku->stok > 0)
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                {{ $buku->peminjamans_count }}
                            </td>
                            <td class="text-end pe-4">
                                <!-- Tombol Aksi Disesuaikan: Detail (Info), Edit (Warning), Hapus (Danger) -->
                                <div class="d-inline-flex gap-1 justify-content-end">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-info">
                                        Detail
                                    </a>
                                    @auth
                                        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-warning">
                                            Edit
                                        </a>
                                        <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada data buku yang sesuai dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bukus->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>
</x-layout>