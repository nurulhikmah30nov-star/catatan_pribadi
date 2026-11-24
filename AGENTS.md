# PANDUAN PENGEMBANGAN AI AGENT (AGENTS.md)
## Sistem Informasi Catatan Pribadi Terenkripsi ("CatatanSecure")
### Tugas Kuliah Keamanan Sistem Komputer - S1 Teknik Informatika (NIM 230170187, Digit 7)

---

> [!IMPORTANT]
> **PETUNJUK BAGI PENGGUNA MANUSIA (USER)**:
> Berkas `AGENTS.md` ini disiapkan di dalam vault Obsidian untuk Anda pindahkan (*copy/move*) sendiri secara manual ke direktori *root* repositori proyek Laravel Anda (`/Users/nurulhikmah/Documents/PROJECT` atau folder proyek baru) saat tahap koding dimulai.
> 
> Seluruh dokumen spesifikasi lainnya (`PRD.md`, `design.md`, `decisions.md`, `traceability.md`, `evidence-plan.md`, `references.md`, `agent-workflow.md`, `BATASAN-SCOPE-AGENT.md`, `README.md`, dan direktori `tickets/`) **TETAP BERADA DI DALAM OBSIDIAN VAULT** sebagai sumber kebenaran tunggal (*Single Source of Truth*).
> 
> **STATUS SAAT INI**: Perencanaan tiket implementasi telah **SELESAI (23 TIKET TERSEDIA)**. Tahap koding aplikasi **SIAP DIMULAI DARI TKT-001** khusus untuk domain `[WEB]` sesuai protokol `agent-workflow.md`.

---

## 1. Lokasi Dasar Dokumentasi dan Peta Navigasi Kanonikal

Ketika AI Agent membaca berkas ini di root proyek Laravel, jangan mengasumsikan berkas dokumentasi lain berada di direktori proyek lokal. Akses seluruh dokumentasi melalui lokasi kanonikal resmi berikut:

- **Path Absolut Folder Dokumentasi**:
  `/Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan`
- **URL / Path Obsidian Vault**:
  `/Users/nurulhikmah/Documents/OBSIDIAN`

> [!WARNING]
> **ATURAN INTEGRITAS AKSES DOKUMENTASI**:
> Jika suatu saat path dokumentasi di atas tidak dapat diakses oleh agent (misalnya karena perubahan direktori atau permission), **AGENT DILARANG KERAS MENGARANG PERSYARATAN BARU**. Agent wajib menghentikan eksekusi dan secara eksplisit meminta lokasi path dokumentasi yang benar kepada pengguna.

### Peta Dokumen dan Kapan Wajib Dibaca

| Nama Dokumen | Lokasi Absolut | Kapan Wajib Dibaca oleh Agent |
| :--- | :--- | :--- |
| **`BATASAN-SCOPE-AGENT.md`** | [BATASAN-SCOPE-AGENT.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/BATASAN-SCOPE-AGENT.md) | **Wajib Pertama Kali**: Memahami batasan wewenang ketat: AI Agent **HANYA** mengerjakan `[WEB]`, dilarang menyentuh Linux. |
| **`README.md`** | [README.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/README.md) | **Konteks Global**: Memahami ringkasan status proyek, urutan pembacaan, dan batasan penugasan. |
| **`agent-workflow.md`** | [agent-workflow.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/agent-workflow.md) | **Sebelum mengerjakan tiket**: Protokol 10 langkah wajib pengerjaan tiket dan template prompt. |
| **`tickets/README.md`** | [tickets/README.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/tickets/README.md) | **Saat memilih tiket**: Daftar 23 tiket (13 tiket `[WEB]` untuk Agent vs 10 tiket non-web untuk Mahasiswa). |
| **`PRD.md`** | [PRD.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/PRD.md) | **Sebelum menulis kode**: Sumber kebenaran arsitektur, rute, model data, aturan bisnis, dan spesifikasi 10 syarat keamanan (SEC-01 s.d. SEC-10). |
| **`decisions.md`**| [decisions.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/decisions.md) | **Saat mengambil keputusan arsitektur**: Memahami latar belakang keputusan ADR, trade-off, dan status keputusan terbuka (DEC-01 s.d. DEC-08). |
| **`design.md`** | [design.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/design.md) | **Saat membuat template Blade & styling**: Mengetahui token warna HEX [PERKIRAAN], pustaka Blade Icons, aturan WCAG AA, dan layout original. |
| **`traceability.md`**| [traceability.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/traceability.md) | **Saat menyusun pengujian**: Memastikan seluruh klausul penugasan dosen terpetakan ke tiket, test plan, dan artefak bukti. |
| **`evidence-plan.md`**| [evidence-plan.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/evidence-plan.md) | **Saat verifikasi bukti**: Mengetahui prosedur pengujian empiris, mitigasi sensor rahasia, caption gambar, dan persiapan tanya jawab lisan. |
| **`references.md`**| [references.md](file:///Users/nurulhikmah/Documents/OBSIDIAN/PRD-Perpustakaan/references.md) | **Sebagai rujukan standar**: Memverifikasi sintaks resmi Laravel 12, PHP 8.3 OpenSSL, NIST SP 800-38D, dan panduan Ubuntu. |

---

## 2. Batasan Wewenang Ketat AI Agent: Website Only (Bukan Linux)

> [!CAUTION]
> **PEMBAGIAN TANGGUNG JAWAB**:
> - **AI Agent HANYA BERWENANG** mengerjakan tiket bertanda **`[WEB]`** yang berkasnya berakhiran **`-WEB.md`** (total 13 tiket: `TKT-001`, `TKT-003` s.d. `TKT-014`). Tiket-tiket ini berfokus pada kode aplikasi Laravel 12, database SQLite, Blade, otentikasi manual, enkripsi PHP, dan tes otomatis PHPUnit.
> - **AI Agent DILARANG KERAS** menyentuh, mengonfigurasi, atau memodifikasi:
>   - Sistem operasi host / VM Ubuntu (`TKT-015`, `TKT-023`).
>   - Konfigurasi daemon OpenSSH (`/etc/ssh/`).
>   - Web server Nginx dan pembuatan sertifikat SSL di Linux (`TKT-016`).
>   - Firewall UFW atau pemindaian Nmap dari luar (`TKT-017`).
>   - Skrip cron shell backup dan pengujian restore di Linux server (`TKT-018`).
>   - Pembuatan artefak tangkapan layar, penyusunan PDF laporan, rekaman video, atau asistensi dosen (`TKT-002`, `TKT-019` s.d. `TKT-022`).
> - Seluruh tugas infrastruktur Linux dan deliverable akademik adalah wewenang penuh **MAHASISWA (MANUSIA)**.

---

## 3. Baseline Teknologi dan Batasan Ruang Lingkup (Scope)

Seluruh pengerjaan kode oleh agent wajib mematuhi batasan teknologi berikut secara ketat:
- **Framework**: Laravel 12 sesuai panduan resmi *Getting Started*.
- **Bahasa & Runtime**: PHP 8.3 dengan ekstensi `pdo_sqlite` dan `openssl`.
- **Mesin Basis Data**: SQLite 3.45+ (file tunggal, zero external network port).
- **Arsitektur Rendering**: Server-Rendered Blade Templates murni.
- **Autentikasi**: Autentikasi manual melalui fungsi inti Laravel (`Auth::attempt`, `Session`, `Hash::make`).
- **Pustaka Ikon**: Wajib menggunakan paket **Blade Icons** konsisten: `blade-ui-kit/blade-heroicons` (`<x-heroicon-o-...>` dan `<x-heroicon-s-...>`).
- **Framework Testing**: PHPUnit 11 (bukan Pest).
- **Asset Bundler**: Vite (untuk CSS/JS lokal).
- **Target Deployment**: Server Linux Ubuntu 24.04 LTS, Nginx Reverse Proxy (TLS 1.3 / Port 443), PHP-FPM, UFW.

### Larangan Mutlak Arsitektur (Anti-Patterns)
- **DILARANG** menginstal atau menggunakan starter kit: Laravel Breeze, Jetstream, Fortify, Nova, atau Filament.
- **DILARANG** menggunakan Single Page Application (SPA), Livewire, Inertia, React, atau Vue.
- **DILARANG** menambahkan fitur di luar scope wajib: sistem pembayaran, denda uang, reservasi antrean, notifikasi email/SMTP/SMS, OAuth/Google Login, atau MFA/TOTP tanpa instruksi eksplisit pengguna.

---

## 4. Protokol Keamanan & Isolasi Versi Rentan

1. **Kriptografi Data Sensitif (Syarat 3)**:
   - Laravel bawaan `Crypt::encrypt()` menggunakan AES-CBC dan **TIDAK** otomatis menggunakan AES-GCM!
   - Agent wajib menggunakan service khusus berbasis `openssl_encrypt($data, 'aes-256-gcm', ...)` dengan nonce 12 byte acak baru per enkripsi, tag autentikasi 16 byte, dan payload envelope versioned Base64 sesuai DEC-02.
2. **Kriptografi Password (Syarat 2)**:
   - Wajib menggunakan `bcrypt` dengan salt otomatis bawaan.
   - Tangani batas maksimal 72 byte UTF-8 pada form request untuk mencegah pemotongan karakter tanpa peringatan (*silent truncation*).
3. **Isolasi Versi Rentan vs. Versi Aman (Syarat 8 s.d. 10)**:
   - Versi rentan (demonstrasi SQLi, XSS, dan IDOR) **HANYA BOLEH DIJALANKAN DI LOOPBACK LOCALHOST** (`127.0.0.1:8001`).
   - Versi rentan **DILARANG KERAS** dipublikasikan ke antarmuka Nginx (port 443), dilarang di-bind ke `0.0.0.0`, dan dilarang diunggah ke internet publik.
   - Versi rentan wajib menggunakan database terpisah (`database_vulnerable.sqlite`) dan nama cookie sesi terpisah (`pustaka_vuln_session`) agar tidak mencemari versi aman.

---

## 5. Perlindungan Pekerjaan Pengguna dan Kebijakan Sensor Rahasia

1. **Perlindungan Berkas Lama**:
   - Dilarang menimpa atau menghapus file dokumentasi atau kode milik pengguna tanpa konfirmasi.
   - Jika terjadi tabrakan nama file, buat di subdirektori berstempel tanggal unik `Draft-YYYY-MM-DD`.
2. **Nol Rahasia di Repositori (Zero Secrets in Repo)**:
   - Pastikan berkas `.env`, berkas kunci privat `.key` / `key.pem`, berkas database fisik `database.sqlite`, dan arsip backup terenkripsi dimasukkan ke dalam `.gitignore`.
3. **Larangan Operasi Destruktif**:
   - Dilarang menjalankan perintah pembersihan disk destruktif (`rm -rf /`, truncate tabel produksi tanpa backup, atau memodifikasi firewall tanpa membuka SSH terlebih dahulu).
   - Dilarang melakukan perintah `git push` ke remote publik atau membuat commit Git tanpa instruksi eksplisit pengguna.

---

## 6. Tata Cara Memilih dan Mengeksekusi Tiket Implementasi

### 6.1 Siklus Status Tiket
- Status awal tiket hanya boleh `TODO` atau `BLOCKED`.
- Saat agent mulai mengerjakan satu tiket: ubah statusnya menjadi `IN_PROGRESS`.
- Tiket hanya boleh diubah statusnya menjadi `DONE` apabila **seluruh butir Definition of Done (DoD)** dan pengujian verifikasi terbukti berhasil.
- Jika ada kendala dependensi atau prasyarat manusia: ubah status menjadi `BLOCKED` dan tuliskan alasannya secara transparan.

### 6.2 Alur Pemilihan Tiket Khusus AI Agent (`[WEB]`)
1. **Periksa Tiket Tanpa Dependensi (*Unblocked Initial Batch*)**:
   - `TKT-001 [WEB]`: Inisialisasi Proyek Laravel 12 & Basis Data SQLite (Tiket Web Pertama yang Siap Dikerjakan).
2. **Periksa Dependensi**:
   Sebelum membuka tiket baru, pastikan seluruh tiket prasyarat yang tercantum pada seksi *Dependensi Tiket* sudah berstatus `DONE`.
3. **Patuhi Critical Path Khusus Web**:
   `TKT-001 [WEB]` $\rightarrow$ `TKT-006 [WEB]` (setelah `TKT-002` dibuka manusia) $\rightarrow$ `TKT-007 [WEB]` $\rightarrow$ `TKT-008 [WEB]` $\rightarrow$ `TKT-009 [WEB]` $\rightarrow$ `TKT-014 [WEB]`.

---

## 7. Protokol 10 Langkah Pengerjaan Tiket (Ringkasan dari `agent-workflow.md`)

1. **Baca Panduan Root**: Baca berkas `AGENTS.md` dan `BATASAN-SCOPE-AGENT.md`.
2. **Baca Konteks Global**: Baca `PRD.md` dan `decisions.md`.
3. **Verifikasi Domain `[WEB]` & Telaah Tiket**: Buka `tickets/TKT-xxx-[judul]-WEB.md`. Pastikan tiket bertanda `[WEB]`.
4. **Verifikasi Dependensi**: Pastikan tiket prasyarat berstatus `DONE`.
5. **Periksa Kode Eksisting**: Telusuri file proyek untuk mencegah duplikasi.
6. **Implementasi Terfokus**: Kerjakan hanya yang ada di dalam *Scope-In*.
7. **Pengujian Sintaks & Unit**: Jalankan `php -l` dan `php artisan test`.
8. **Catat Bukti & Log**: Tuliskan hasil aktual pada catatan tiket.
9. **Pembaruan Status**: Ubah `IN_PROGRESS` $\rightarrow$ `DONE` (atau `BLOCKED`).
10. **Berhenti**: Hentikan turn dan laporkan hasil ke pengguna. Jangan lanjut otomatis ke tiket berikutnya.
