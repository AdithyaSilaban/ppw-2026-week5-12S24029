<x-layout>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0">Edit Data Buku</h3>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('buku.update', $buku->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor ISBN</label>
                                <input type="text" name="isbn" value="{{ old('isbn', $buku->isbn) }}" class="form-control @error('isbn') is-invalid @enderror">
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori Buku</label>
                                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ old('kategori_id', $buku->kategori_id) == $kat->id ? 'selected' : '' }}>
                                            {{ $kat->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Judul Buku</label>
                                <input type="text" name="judul" value="{{ old('judul', $buku->judul) }}" class="form-control @error('judul') is-invalid @enderror">
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penulis</label>
                                <input type="text" name="penulis" value="{{ old('penulis', $buku->penulis) }}" class="form-control @error('penulis') is-invalid @enderror">
                                @error('penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penerbit</label>
                                <input type="text" name="penerbit" value="{{ old('penerbit', $buku->penerbit) }}" class="form-control @error('penerbit') is-invalid @enderror">
                                @error('penerbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" class="form-control @error('tahun_terbit') is-invalid @enderror">
                                @error('tahun_terbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Stok</label>
                                <input type="number" name="stok" value="{{ old('stok', $buku->stok) }}" class="form-control @error('stok') is-invalid @enderror" min="0">
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Sinopsis / Deskripsi Singkat</label>
                                <textarea name="sinopsis" rows="3" class="form-control @error('sinopsis') is-invalid @enderror">{{ old('sinopsis', $buku->sinopsis) }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn text-white w-100 py-2 fw-semibold" style="background-color: #4C1D95;">
                                    Perbarui Data Buku
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>