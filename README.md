# SIPUS-Del Tahap 3: Implementasi Web Services Terdistribusi (SOAP Legacy & RESTful Web API v1)
> **Mata Kuliah:** Pemrograman dan Pengujian Aplikasi Web (1253101)  
> **Institusi:** Program Studi Sarjana Sistem Informasi / Informatika, Fakultas Informatika dan Teknik Elektro, Institut Teknologi Del  
> **Tahun Ajaran:** Semester Genap 2026/2027

---

## 1. Identitas Mahasiswa
* **Nama:** Adithya Silaban
* **NIM:** 12S24029
* **Branch Pengerjaan:** `week9-rest-api`

---

## 2. Analisis Kebutuhan & Kelayakan (Feasibility) Web Services

### 2.1 Transformasi Arsitektur Monolitik Menuju Mesin-ke-Mesin (M2M)
Pada tahapan Minggu 1 hingga Minggu 6, sistem SIPUS-Del beroperasi sebagai aplikasi monolitik berorientasi visual (*Human-to-Machine*). Server Laravel merender dokumen utuh HTML + CSS Blade seberat puluhan kilobyte untuk ditampilkan pada peramban web pengguna.
Memasuki kebutuhan integrasi lintas sistem modern (aplikasi mobile mahasiswa, mesin kiosk peminjaman mandiri perpustakaan, dan integrasi yudisium kampus), sistem bertransformasi menjadi **Web Services**. Komunikasi bertumpu pada pertukaran representasi data murni (JSON dan XML) nir-antarmuka manusia yang ringkas, terstruktur, dan terikat kontrak formal.

### 2.2 Pemetaan Dua Peran Ekosistem pada SIPUS-Del
1. **SIPUS-Del sebagai Service Provider:**
   Sistem membuka katalog buku dan transaksi sirkulasi perpustakaan melalui kanal RESTful API nir-status (`/api/v1/...`) agar dapat dikonsumsi oleh aplikasi Mobile Mahasiswa dan Kiosk Mandiri secara aman tanpa keterikatan sesi browser.
2. **SIPUS-Del sebagai Service Consumer:**
   Ketika menyajikan detail buku, sistem secara cerdas memperkaya informasi pustaka (jumlah halaman, nama penerbit internasional, tautan pratinjau, dan berkas sampul buku) dengan mengonsumsi layanan publik eksternal (OpenLibrary / Google Books REST API) melalui fasad `Illuminate\Support\Facades\Http`.

### 2.3 Komparasi Kelayakan Teknis: SOAP vs RESTful API
| Parameter Analisis | Layanan SOAP Legacy (Bebas Pustaka) | Layanan RESTful Web API v1 (Katalog & Sirkulasi) |
| :--- | :--- | :--- |
| **Format Pertukaran Pesan** | Wajib format XML baku berbungkus `<soap:Envelope>`. | Format media ringkas, dominan JSON (`application/json`). |
| **Metode Protokol HTTP** | Hanya memakai `POST` sebagai terowongan transmisi (*tunneling*). | Mengoptimalkan semantik resmi HTTP Verbs (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`). |
| **Ukuran & Efisiensi Bandwidth** | *Verbose* dan memakan kuota jaringan lebih besar akibat tag XML. | Sangat ringan (5–8x lebih kecil dibanding XML SOAP), native didukung aplikasi mobile. |
| **Manajemen Status (State)** | Terikat pada kontrak kaku WSDL (*Web Services Description Language*). | Menerapkan *Statelessness* penuh dengan *Sanctum Bearer Token*. |
| **Justifikasi Kelayakan Kasus** | Dipilih khusus untuk integrasi sistem administrasi yudisium kampus lama (*core legacy*). | Dipilih mutlak untuk aplikasi Mobile dan Kiosk Kutu Buku karena respons cepat dan hemat daya. |

---

## 3. Matriks Katalog Endpoint Web Services (SOAP & REST API v1)

| No | Protokol / Arsitektur | Metode | Endpoint URI | Otorisasi / Header | Deskripsi Fungsi Bisnis | Kode Status Respons |
| :---: | :---: | :---: | :--- | :--- | :--- | :---: |
| 1 | SOAP Legacy | `GET` | `/soap/sipus.wsdl` | Publik | Menyajikan dokumen kontrak formal XML WSDL | `200 OK` |
| 2 | SOAP Legacy | `POST` | `/soap/sipus` | `Content-Type: text/xml` | Pengecekan status bebas pustaka mahasiswa via XML Envelope | `200 OK`, `500 Fault` |
| 3 | REST API v1 | `POST` | `/api/v1/auth/login` | `Accept: application/json` | Verifikasi kredensial pustakawan & penerbitan Bearer Token | `200 OK`, `401 Unauthorized` |
| 4 | REST API v1 | `GET` | `/api/v1/auth/me` | Bearer Token | Mengambil profil pustakawan pemilik token aktif | `200 OK`, `401 Unauthorized` |
| 5 | REST API v1 | `POST` | `/api/v1/auth/logout` | Bearer Token | Mencabut (*revoke*) token aktif dan mengakhiri sesi stateless | `200 OK`, `401 Unauthorized` |
| 6 | REST API v1 | `GET` | `/api/v1/bukus` | Publik | Katalog buku terpaginasi + HATEOAS, filter, search, & sort | `200 OK` |
| 7 | REST API v1 | `GET` | `/api/v1/bukus/{id}` | Publik | Mengambil detail satu buku beserta relasi riwayat peminjaman | `200 OK`, `404 Not Found` |
| 8 | REST API v1 | `POST` | `/api/v1/bukus` | Bearer Token | Menambahkan buku baru ke katalog perpustakaan | `201 Created` + Header Location |
| 9 | REST API v1 | `PUT` | `/api/v1/bukus/{id}` | Bearer Token | Mengganti seluruh atribut representasi buku (Full Replacement) | `200 OK`, `404 Not Found` |
| 10 | REST API v1 | `PATCH` | `/api/v1/bukus/{id}` | Bearer Token | Memperbarui sebagian atribut buku (misal: stok) | `200 OK`, `404 Not Found` |
| 11 | REST API v1 | `DELETE` | `/api/v1/bukus/{id}` | Bearer Token | Menghapus buku (ditolak 409 jika sedang dipinjam aktif) | `204 No Content`, `409 Conflict` |
| 12 | REST API v1 | `GET` | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | Mengambil metadata buku tambahan via API eksternal (Consumer) | `200 OK`, `404`, `504` |
| 13 | REST API v1 | `GET` | `/api/v1/peminjamans` | Bearer Token | Mengambil daftar riwayat transaksi peminjaman terpaginasi | `200 OK`, `401 Unauthorized` |
| 14 | REST API v1 | `POST` | `/api/v1/peminjamans` | Bearer Token | Peminjaman buku baru via transaksi ACID + `lockForUpdate()` | `201 Created`, `409`, `422` |
| 15 | REST API v1 | `PATCH` | `/api/v1/peminjamans/{id}/kembalikan` | Bearer Token | Memproses pengembalian buku dan menambah stok atomik (+1) | `200 OK`, `404`, `409` |

---

## 4. Panduan Instalasi & Pengujian Cepat

1. **Clone dan Pindah ke Branch Pengerjaan:**
   ```bash
   git checkout week9-rest-api