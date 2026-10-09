<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-secondary">Daftar Mahasiswa</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Program Studi</th>
                            <th>IPK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $mhs)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $mhs->nim }}</td>
                                <td>{{ $mhs->nama }}</td>
                                <td>{{ $mhs->email }}</td>
                                <td><span class="badge bg-secondary">{{ $mhs->programStudi->nama ?? '-' }}</span></td>
                                <td>{{ number_format($mhs->ipk, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $mahasiswas->links() }}
    </div>
</x-layout>