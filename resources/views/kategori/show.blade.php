<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Kategori: {{ $kategori->nama_kategori }}</h2>
            <p class="text-muted mb-0">
                Kode: <span class="badge bg-secondary">{{ $kategori->kode_kategori }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary me-2">← Kembali ke Daftar</a>
            <a href="{{ route('buku.create') }}" class="btn btn-del">+ Tambah Buku</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Daftar Koleksi Buku pada Kategori Ini</h5>
        </div>
        <div class="card-body p-0">
            <!-- Pembungkus responsif tabel -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ISBN</th>
                            <th>Judul Buku</th>
                            <th>Penulis / Penerbit</th>
                            <th>Tahun</th>
                            <th class="text-center">Stok</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bukus as $buku)
                            <tr>
                                <td class="ps-3"><span class="text-muted font-monospace">{{ $buku->isbn }}</span></td>
                                <td>
                                    <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $buku->judul }}
                                    </a>
                                </td>
                                <td>
                                    {{ $buku->penulis }}<br>
                                    <small class="text-muted">{{ $buku->penerbit }}</small>
                                </td>
                                <td>{{ $buku->tahun_terbit }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $buku->stok > 0 ? 'success' : 'danger' }}">
                                        {{ $buku->stok }} eks
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-info">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada buku yang terdaftar di kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $bukus->links() }}
    </div>
</x-layout>