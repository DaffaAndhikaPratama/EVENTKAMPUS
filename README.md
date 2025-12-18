# Panduan Instalasi dan Penggunaan Program

## 1. Menjalankan Aplikasi Secara Lokal
Pastikan Anda telah menginstal **Laragon** (atau XAMPP) dan **Composer**.

1.  **Clone / Download** repository ini ke folder `www` (Laragon) atau `htdocs` (XAMPP).
2.  Buka terminal di dalam folder project, lalu jalankan perintah berikut untuk menginstal dependensi:
    ```bash
    composer install
    ```
3.  Jika menggunakan Laragon, aplikasi biasanya dapat diakses langsung via `http://nama-folder.test`. 
    Jika menggunakan built-in server PHP, jalankan:
    ```bash
    php -S localhost:8000
    ```
    Lalu buka browser di `http://localhost:8000`.

## 2. Import Database
1.  Buat database baru di phpMyAdmin (misalnya: `web_event_db`).
2.  Import file database SQL yang disertakan dalam project ini (jika ada, biasanya bernama `database.sql` atau sejenisnya).
    *   *Catatan: Pastikan struktur tabel sesuai dengan kebutuhan aplikasi (users, events, event_registrations, dll).* 

## 3. Konfigurasi Environment (.env)
Buat file bernama `.env` di folder root project, lalu salin konfigurasi berikut dan sesuaikan nilainya:

```env
# Konfigurasi Database
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=web_event_db

# URL Dasar Aplikasi (Sesuaikan dengan URL lokal Anda)
BASE_URL=http://localhost/tugasAkhir/web

# Konfigurasi Google API (Untuk Login & Kalender)
GOOGLE_CLIENT_ID = masukkan_client_id_google_anda
GOOGLE_CLIENT_SECRET = masukkan_client_secret_google_anda
GOOGLE_REDIRECT_URL = http://localhost/tugasAkhir/web/auth/callback.php
```

## 4. Daftar Akun Uji
Berikut adalah akun default untuk pengujian (jika menggunakan data dummy standar):
  
| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@gmail.com` | `123456` |
| **Event Organizer** | `eo@gmail.com` | `123456` |
| **Mahasiswa** | `mhs@gmail.com` | `123456` |

