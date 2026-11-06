# Test Database Reset

Pengelolaan satu database SQLite testing yang terdaftar milik aplikasi ini; tidak memakai database proyek Laravel lain.

## Fitur

- Informasi target local-test, snapshot privat, daftar snapshot, restore, reset, seeder idempotent, dan riwayat operasi.
- Reset dan restore memerlukan teks konfirmasi eksplisit di frontend serta backend; snapshot pengaman dibuat sebelum perubahan.
- Backup/restore memakai SQLite3 backup API; verifikasi integritas, foreign key, SHA-256 file snapshot, dan hash data/schema.
- Menolak environment produksi, target lain, symlink, penanda/schema tidak cocok, SQL bebas, serta path/koneksi pengguna.
- Akses hanya akun pemilik pertama; pendaftaran ditutup setelah pemilik dibuat. Snapshot dan database target tidak masuk Git.

## Teknologi

Laravel 13, React, Inertia.js, Tailwind CSS, TypeScript, SQLite3, PHPUnit.

## Cara menjalankan

Memerlukan PHP 8.4+, Composer, dan Node.js 22.12+. Arahkan document root Laragon ke folder `public`.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Jalankan `php artisan test` untuk pengujian backend.

Buat akun pemilik melalui halaman pendaftaran pada instalasi pertama. Target dibuat otomatis di storage/app/testing/registered-target/target.sqlite dan snapshot di storage/app/testing/snapshots. Tombol seeder hanya memasukkan data uji bawaan ke target tersebut. Aplikasi memerlukan ekstensi SQLite3 dan PDO SQLite bawaan PHP Laragon. Jangan mengubah APP_KEY setelah snapshot dibuat karena penanda kepemilikan bergantung pada key aplikasi.
