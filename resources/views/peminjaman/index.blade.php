<x-layout title="Sirkulasi Peminjaman">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0" style="color: #4C1D95;">Daftar Transaksi Sirkulasi</h3>
            <p class="text-muted small mb-0">Manajemen pencatatan peminjaman dan pengembalian koleksi buku</p>
        </div>
        <a href="{{ route('peminjaman.create') }}" class="btn text-white fw-semibold px-3 shadow-sm" style="background-color: #4C1D95;">
            + Catat Peminjaman Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3">Kode Pinjam</th>
                        <th>Buku Dipinjam</th>
                        <th>Peminjam (NIM)</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $pinjam)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                <span class="badge bg-light text-dark border">{{ $pinjam->kode_pinjam }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $pinjam->buku->judul ?? '-' }}</div>
                                <small class="text-muted">ISBN: {{ $pinjam->buku->isbn ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $pinjam->nama_peminjam }}</div>
                                <small class="text-muted">{{ $pinjam->nim_peminjam }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d M Y') }}</td>
                            <td>
                                <span class="{{ $pinjam->status === 'dipinjam' && now()->gt($pinjam->tanggal_jatuh_tempo) ? 'text-danger fw-bold' : '' }}">
                                    {{ \Carbon\Carbon::parse($pinjam->tanggal_jatuh_tempo)->format('d M Y') }}
                                </span>
                            </td>
                            <td>
                                @if($pinjam->status === 'dipinjam')
                                    <span class="badge bg-warning text-dark px-2 py-1">Sedang Dipinjam</span>
                                @else
                                    <span class="badge bg-success px-2 py-1">Telah Kembali</span>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        {{ \Carbon\Carbon::parse($pinjam->tanggal_kembali)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if($pinjam->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembalikan', $pinjam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi pengembalian buku ini? Stok akan otomatis bertambah.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                            Pengembalian
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Belum ada riwayat transaksi peminjaman buku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($peminjamans->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</x-layout>