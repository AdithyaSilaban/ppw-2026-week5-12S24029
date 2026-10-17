<x-layout>
    <div class="row justify-content-center my-5">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center py-4 rounded-top-4" style="background-color: #4C1D95 !important;">
                    <h4 class="fw-bold mb-1">SIPUS-Del</h4>
                    <p class="mb-0 small text-white-50">Portal Masuk Khusus Pustakawan Kampus</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email Pustakawan</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $savedEmail ?? '') }}" required autofocus 
                                   placeholder="pustakawan@del.ac.id">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   required placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label class="form-check-label small text-muted" for="remember">Ingat Sesi Saya</label>
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-3 shadow-sm" style="background-color: #4C1D95;">
                            Masuk ke Sistem
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3 rounded-bottom-4">
                    <small class="text-muted">Gunakan akun: <code>pustakawan@del.ac.id</code> / <code>password123</code></small>
                </div>
            </div>
        </div>
    </div>
</x-layout>
