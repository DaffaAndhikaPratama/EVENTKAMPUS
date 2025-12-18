# Daftar Endpoint (Routing)

Berikut adalah daftar file yang berfungsi sebagai endpoint utama dalam aplikasi:

## Autentikasi
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET/POST` | `/auth/login.php` | Halaman Login & Proses Login |
| `GET/POST` | `/auth/register.php` | Halaman Daftar & Proses Registrasi |
| `GET` | `/auth/logout.php` | Proses Logout |
| `GET/POST` | `/auth/verify.php` | Verifikasi Email (OTP) |
| `GET` | `/auth/callback.php` | Callback Login Google OAuth |

## Dashboard & Halaman Utama
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/pages/dashboard.php` | Dashboard Utama (Menyesuaikan Role) |
| `GET` | `/index.php` | Landing Page |
| `GET` | `/pages/search_result.php` | Pencarian Event |

## Manajemen Event (CRUD)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET/POST` | `/event_management/create.php` | Form & Proses Tambah Event (Create) |
| `GET` | `/event_management/detail_event.php` | Detail Event (Read) |
| `GET/POST` | `/event_management/edit.php` | Form & Proses Edit Event (Update) |
| `GET` | `/event_management/delete.php` | Proses Hapus Event (Delete) |
| `POST` | `/event_management/verifikasi_peserta.php` | Update Status Peserta (Confirm/Reject) |

## Fitur Tambahan
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/pages/csv/download_admin.php` | Download Laporan CSV (Admin) |
| `GET` | `/pages/csv/download_eo.php` | Download Laporan CSV (EO) |
| `POST` | `/user_profile/edit_profile.php` | Edit Profil User |
