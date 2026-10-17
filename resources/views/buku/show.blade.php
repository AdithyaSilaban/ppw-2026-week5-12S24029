<x-layout title="Detail Buku - {{ $buku->judul }}">
    <div class="row g-4">
        <!-- Informasi Koleksi Buku -->
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

                    <div class="mb-3">
                        <span class="text-muted small d-block">Frekuensi Sirkulasi:</span>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            🔄 Telah dipinjam sebanyak {{ $buku->peminjamans->count() }} kali
                        </span>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted small d-block">Sinopsis:</span>
                        <p class="text-secondary small mt-1">{{ $buku->sinopsis ?? 'Tidak ada sinopsis untuk koleksi ini.' }}</p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Katalog</a>
                        @auth
                            <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-warning">Edit Data</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Sirkulasi: Proteksi Data Pribadi (Hanya Pustakawan) -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color: #4C1D95;">Riwayat Sirkulasi Koleksi</h5>
                    <span class="badge bg-light text-muted border">Total: {{ $buku->peminjamans->count() }}</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    @auth
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-3">Kode Pinjam</th>
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
                                            Belum ada riwayat transaksi sirkulasi untuk buku ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <!-- Tampilan untuk Pengunjung Publik (Non-Login) -->
                        <div class="text-center py-5 px-4">
                            <div class="display-6 mb-2">🔒</div>
                            <h6 class="fw-bold text-dark">Informasi Sirkulasi Terbatas</h6>
                            <p class="text-muted small mb-3">
                                Rincian data peminjam dan identitas mahasiswa diproteksi demi privasi.<br>
                                Silakan masuk sebagai pustakawan untuk melihat log transaksi lengkap.
                            </p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary px-3">
                                Masuk Pustakawan
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-layout>