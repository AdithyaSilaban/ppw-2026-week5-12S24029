<x-layout title="Catat Peminjaman Buku">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0" style="color: #4C1D95;">Formulir Peminjaman Koleksi</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('peminjaman.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="buku_id" class="form-label fw-semibold">Pilih Buku</label>
                            <select name="buku_id" id="buku_id" class="form-select @error('buku_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Koleksi Buku --</option>
                                @foreach($bukus as $buku)
                                    <option value="{{ $buku->id }}" {{ old('buku_id') == $buku->id ? 'selected' : '' }} {{ $buku->stok <= 0 ? 'disabled' : '' }}>
                                        {{ $buku->judul }} (Stok Tersedia: {{ $buku->stok }}) {{ $buku->stok <= 0 ? '- [HABIS]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('buku_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nim_peminjam" class="form-label fw-semibold">NIM Mahasiswa</label>
                                <input type="text" name="nim_peminjam" id="nim_peminjam" 
                                       class="form-control @error('nim_peminjam') is-invalid @enderror" 
                                       value="{{ old('nim_peminjam') }}" placeholder="12S24029" required>
                                @error('nim_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_peminjam" class="form-label fw-semibold">Nama Mahasiswa</label>
                                <input type="text" name="nama_peminjam" id="nama_peminjam" 
                                       class="form-control @error('nama_peminjam') is-invalid @enderror" 
                                       value="{{ old('nama_peminjam') }}" placeholder="Nama Lengkap Mahasiswa" required>
                                @error('nama_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_pinjam" class="form-label fw-semibold">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" 
                                       class="form-control @error('tanggal_pinjam') is-invalid @enderror" 
                                       value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                                @error('tanggal_pinjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="tanggal_jatuh_tempo" class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" 
                                       class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" 
                                       value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}" required>
                                @error('tanggal_jatuh_tempo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary px-4">Kembali</a>
                            <button type="submit" class="btn text-white px-4 fw-semibold" style="background-color: #4C1D95;">
                                Konfirmasi & Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>