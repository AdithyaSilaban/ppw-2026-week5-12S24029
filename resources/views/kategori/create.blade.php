<x-layout>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0">Tambah Kategori Baru</h3>
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Kategori</label>
                            <input type="text" name="kode_kategori" value="{{ old('kode_kategori') }}" class="form-control @error('kode_kategori') is-invalid @enderror" placeholder="Contoh: KAT-06">
                            @error('kode_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nama Kategori</label>
                            <input type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" class="form-control @error('nama_kategori') is-invalid @enderror" placeholder="Contoh: Kecerdasan Buatan & Data">
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2 fw-semibold" style="background-color: #4C1D95;">
                            Simpan Kategori
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
