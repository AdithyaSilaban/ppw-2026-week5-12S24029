<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Daftar Kategori Buku</h2>
            <p class="text-muted mb-0">SIPUS-Del — Pengelolaan Kategori Koleksi</p>
        </div>
        <div>
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary me-2 shadow-sm">
                Ke Katalog Buku
            </a>
            <a href="{{ route('kategori.create') }}" class="btn btn-del shadow-sm">
                + Tambah Kategori
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 20%;">Kode Kategori</th>
                            <th>Nama Kategori</th>
                            <th class="text-center" style="width: 20%;">Total Koleksi</th>
                            <th class="text-end pe-3" style="width: 25%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kat)
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold text-secondary">
                                    {{ $kat->kode_kategori }}
                                </td>
                                <td>
                                    <!-- Nama kategori dapat diklik langsung menuju detail -->
                                    <a href="{{ route('kategori.show', $kat) }}" class="fw-bold text-decoration-none text-dark d-block">
                                        {{ $kat->nama_kategori }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-light text-dark border">
                                        {{ $kat->bukus_count ?? 0 }} Judul
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <!-- Tombol Detail untuk akses eksplisit ke kategori.show -->
                                    <a href="{{ route('kategori.show', $kat) }}" class="btn btn-sm btn-outline-info me-1" title="Lihat Detail Koleksi">
                                        Detail
                                    </a>
                                    <a href="{{ route('kategori.edit', $kat) }}" class="btn btn-sm btn-outline-warning me-1">
                                        Edit
                                    </a>
                                    <form action="{{ route('kategori.destroy', $kat) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
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
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada kategori buku yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $kategoris->links() }}
    </div>
</x-layout>