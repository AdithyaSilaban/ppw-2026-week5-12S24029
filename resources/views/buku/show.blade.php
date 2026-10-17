<x-layout title="Detail Buku - {{ $buku->judul }}">
    <div class="row g-4">
        <!-- Informasi Detail Buku -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <span class="badge bg-light text-dark border mb-2">{{ $buku->kategori->nama_kategori ?? '-' }}</span>
                    <h4 class="fw-bold text-dark mb-1">{{ $buku->judul }}</h4>
                    <p class="text-muted small font-monospace mb-3">ISBN: {{ $buku->isbn }}</p>

                    <div class="mb-3">
                        <span class="text-muted small d-block">Penulis & Penerbit:</span>
                        <strong>{{ $buku->penulis }}</strong> — {{ $buku->penerbit }} ({{ $buku->tahun_terbit }})
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block">Sisa Stok Fisik:</span>
                        <span class="badge {{ $buku->stok > 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                            {{ $buku->stok }} eksemplar
                        </span>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted small d-block">Sinopsis:</span>
                        <p class="text-secondary small mt-1">{{ $buku->sinopsis ?? 'Tidak ada sinopsis.' }}</p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
                        @auth
                            <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Ubah Data</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Sirkulasi Buku -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0" style="color: #4C1D95;">Riwayat Sirkulasi Buku</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Kode</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buku->peminjamans as $pinjam)
                                <tr>
                                    <td class="ps-3 font-monospace small">{{ $pinjam->kode_pinjam }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $pinjam->nama_peminjam }}</div>
                                        <small class="text-muted">{{ $pinjam->nim_peminjam }}</small>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d/m/Y') }}</td>
                                    <td>
                                        @if($pinjam->status === 'dipinjam')
                                            <span class="badge bg-warning text-dark">Dipinjam</span>
                                        @else
                                            <span class="badge bg-success">Kembali</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        Buku ini belum pernah dipinjam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout>