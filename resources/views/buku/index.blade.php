<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Katalog Buku Perpustakaan</h2>
            <p class="text-muted mb-0">SIPUS-Del — Sistem Informasi Perpustakaan Kampus Del</p>
        </div>
        <a href="{{ route('buku.create') }}" class="btn btn-del shadow-sm">
            + Tambah Buku
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ISBN</th>
                            <th>Judul Buku</th>
                            <th>Penulis / Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th class="text-center">Stok</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bukus as $item)
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">{{ $item->isbn }}</td>
                                <td>
                                    <!-- Judul dapat diklik menuju halaman detail buku -->
                                    <a href="{{ route('buku.show', $item) }}" class="fw-bold text-decoration-none text-dark d-block">
                                        {{ $item->judul }}
                                    </a>
                                    <small class="text-muted">{{ Str::limit($item->sinopsis ?? '', 50) }}</small>
                                </td>
                                <td>
                                    <div>{{ $item->penulis }}</div>
                                    <small class="text-muted">{{ $item->penerbit }}</small>
                                </td>
                                <td>{{ $item->tahun_terbit }}</td>
                                <td>
                                    <span class="badge bg-del">
                                        {{ $item->kategori->nama_kategori ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->stok > 0 ? 'success' : 'danger' }}">
                                        {{ $item->stok }} eks
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('buku.show', $item) }}" class="btn btn-sm btn-outline-info me-1" title="Lihat Detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('buku.edit', $item) }}" class="btn btn-sm btn-outline-warning me-1">
                                        Edit
                                    </a>
                                    <form action="{{ route('buku.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada koleksi buku di perpustakaan.</td>
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