<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Detail Buku</h2>
            <p class="text-muted mb-0">Informasi lengkap katalog koleksi perpustakaan</p>
        </div>
        <div>
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary me-2">← Kembali ke Katalog</a>
            <a href="{{ route('buku.edit', $buku) }}" class="btn btn-warning me-2">Edit Data</a>
            <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Hapus</button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Kartu Metadata Utama -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <span class="badge bg-secondary mb-2">{{ $buku->isbn }}</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $buku->judul }}</h3>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-muted mb-3">Sinopsis / Deskripsi</h5>
                    <p class="card-text text-secondary" style="line-height: 1.8;">
                        {{ $buku->sinopsis ?? 'Tidak ada sinopsis yang tersedia untuk buku ini.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Kartu Rincian Spesifikasi -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Spesifikasi Buku</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Kategori</span>
                            <span class="badge text-white" style="background-color: #4C1D95;">
                                {{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                            </span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Penulis</span>
                            <span class="fw-semibold">{{ $buku->penulis }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Penerbit</span>
                            <span class="fw-semibold">{{ $buku->penerbit }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tahun Terbit</span>
                            <span class="fw-semibold">{{ $buku->tahun_terbit }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Ketersediaan Stok</span>
                            <span class="badge {{ $buku->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $buku->stok }} eksemplar
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-layout>