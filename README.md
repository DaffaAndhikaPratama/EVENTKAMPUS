# Aplikasi EventKampus

EventKampus adalah aplikasi web manajemen event kampus yang komprehensif. Aplikasi ini memudahkan mahasiswa untuk mencari dan mendaftar event (seminar, workshop, lomba), serta memberikan fitur lengkap bagi Event Organizer (EO) untuk mengelola acara mereka, mulai dari publikasi hingga verifikasi peserta.

> *Proyek ini merupakan integrasi Tugas Besar (Final Project) yang mencakup 5 Mata Kuliah Inti: Pemrograman Web, Pemrograman Berorientasi Objek (PBO), Basis Data, dan Analisis Desain Berorientasi Objek (ADBO).*

---
Aplikasi ini dirancang untuk memenuhi kompetensi dari 5 mata kuliah berikut:

1.  **Analisis Desain Berorientasi Objek (ADBO):**
    * Perancangan sistem menggunakan diagram UML.
    * Penerapan pola desain dan pemisahan logika bisnis.

2.  **Pemrograman Web:**
    * Implementasi Backend menggunakan PHP Native (Tanpa Framework).
    * Frontend interaktif dengan Bootstrap 5.
    * Penerapan Autentikasi (termasuk Google OAuth), Session Management, dan Integrasi API (Google Calendar).

3.  **Basis Data:**
    * Perancangan skema database relasional (ERD) untuk Users, Events, Registrations, dan Notifications.
    * Implementasi operasi CRUD dan foreign key constraints.

4.  **Pemrograman Berorientasi Objek (PBO):**
    * Penerapan konsep OOP (Class, Object, Inheritance, Encapsulation) dalam `Event.php`, `Database.php`, dll.
    * Penggunaan *Helper Classes* (seperti `CalendarHelper`, `EmailHelper`).

---

## Latar Belakang Masalah

Kampus seringkali memiliki banyak kegiatan menarik, namun penyebaran informasinya masih terpencar (melalui pesan singkat atau poster fisik). Hal ini menyulitkan mahasiswa untuk menemukan event yang sesuai minat mereka. Selain itu, proses pendaftaran seringkali manual dan rentan kesalahan.

**EventKampus** hadir sebagai platform sentral yang menghubungkan penyelenggara event dengan mahasiswa. Dengan fitur pendaftaran online, verifikasi pembayaran otomatis/manual, dan integrasi kalender, aplikasi ini meningkatkan partisipasi dan efisiensi pengelolaan acara kampus.

---

## Fitur Unggulan

### 1.  Manajemen Event & Kategori
* **CRUD Event:** EO dapat membuat, mengedit, dan menghapus event.
* **Kategori & Pencarian:** Pencarian event berdasarkan judul dan filter kategori (Seminar, Workshop, dll).

### 2.  Sistem Pendaftaran & Tiket
* **Registrasi Online:** Pendaftaran peserta dengan upload bukti pembayaran.
* **E-Ticket:** Tiket elektronik dikirim via email setelah verifikasi.
* **Verifikasi Peserta:** EO dapat memverifikasi pembayaran dan status peserta.

### 3.  Integrasi Google
* **Google Login:** Masuk dengan mudah menggunakan akun Google.
* **Google Calendar:** Event yang terdaftar otomatis ditambahkan ke Google Calendar peserta.

### 4.  Sistem Notifikasi
* **Real-time Notifications:** Pemberitahuan status pendaftaran dan event baru di dashboard.
* **Email Notifications:** Konfirmasi pendaftaran dikirim otomatis ke email.

### 5.  Dashboard & Analitik
* **Statistik Event:** Visualisasi jumlah peserta dan distribusi kategori event.
* **Laporan PDF/CSV:** Admin dan EO dapat mengunduh laporan data event dan peserta.

### 6.  Autentikasi & Keamanan
* **Secure Login:** Hash password (Bcrypt) dan verifikasi email.
* **Role-Based Access:** Akses berbeda untuk Admin, EO, dan Mahasiswa.

---

##  Alat yang Digunakan

* **Bahasa:** PHP 8.x (OOP Style), JavaScript, HTML5, CSS3.
* **Database:** MySQL / MariaDB.
* **Framework CSS:** Bootstrap 5.
* **API:** Google OAuth 2.0, Google Calendar API.
* **Libraries:** `phpmailer/phpmailer`, `google/apiclient`, `vlucas/phpdotenv`.

---

##  Cara Instalasi

1.  **Clone Repository:**
    ```bash
    git clone https://github.com/DaffaAndhikaPratama/EVENTKAMPUS
    cd eventkampus
    ```
2.  **Install Dependencies:**
    ```bash
    composer install
    ```
3.  **Setup Database:**
    * Buat database `web_event_db`.
    * Import file SQL yang tersedia (misal `web_event_db.sql`) ke database.
4.  **Konfigurasi .env:**
    * Duplikat `.env.example` menjadi `.env` (atau buat baru).
    * Konfigurasi Database:
      ```env
      DB_HOST=localhost
      DB_USER=root
      DB_NAME=web_event_db
      ```
    * Konfigurasi Google API & SMTP (lihat panduan Google Console).
5.  **Jalankan:**
    * Akses via browser: `http://localhost/tugasAkhir/web/`

---

## Tim Pengembang

**Nama:**
* Daffa Andhika Pratama (24416255201110)
* Vharel Gheraldy.S (24416255201219)
* Dimas Fajri Maulana (24416255201226)

**Program Studi:** TEKNIK INFORMATIKA

---
