# Sistem Pengajuan Layanan Administrasi Wilayah Berbasis REST API

Sistem Pengajuan Layanan Administrasi Wilayah Berbasis REST API merupakan layanan backend yang dibangun untuk membantu digitalisasi proses administrasi pada tingkat desa atau kelurahan. Sistem ini menyediakan layanan pengajuan administrasi secara terstruktur, aman, dan mudah diintegrasikan dengan aplikasi lain.

Fitur utama sistem meliputi:

- Autentikasi menggunakan JWT (JSON Web Token)
- Manajemen data wilayah (Provinsi, Kota/Kabupaten, Kecamatan)
- Pengajuan layanan administrasi masyarakat
- Manajemen status pengajuan layanan
- Riwayat perubahan status pengajuan
- Logging aktivitas API
- Dokumentasi API menggunakan Postman

---

# Cara Menjalankan Sistem

## 1. Clone Repository

```bash
git clone https://github.com/BagusFatihuddin/Sistem-Pengajuan-Layanan-Administrasi-Wilayah-Berbasis-REST-API
```

Masuk ke folder project

```bash
cd Sistem-Pengajuan-Layanan-Administrasi-Wilayah-Berbasis-REST-API
```

---

## 2. Install Dependency

```bash
composer install
```

---

## 3. Copy File Environment

```bash
copy .env.example .env
```

---

## 4. Konfigurasi Database

Buka file `.env`, kemudian sesuaikan konfigurasi database.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

---

## 5. Generate Application Key

```bash
php artisan key:generate
```

---

## 6. Generate JWT Secret

```bash
php artisan jwt:secret
```

---

## 7. Jalankan Migrasi Database

```bash
php artisan migrate
```

---

## 8. Menjalankan Server

```bash
php artisan serve
```

---

# Informasi Akun Uji Coba

**Tester Account**

```
Email    : said@mail.com
Password : 123456
```

---

# Dokumentasi API

Dokumentasi API dapat diakses melalui:

### Postman Documentation

```
https://documenter.getpostman.com/view/43068383/2sBXwvJTiG
```

Dokumentasi mencakup:

- Endpoint API
- HTTP Method
- Authorization JWT
- Request Parameter
- Contoh Request
- Contoh Response
- Success Response
- Failed Response

---

# Tim Pengembang

**Bagus Fatihuddin**
- Perancangan Database
- JWT Authentication
- REST API Development
- Business Logic
- Logging API

**Muhammad Said**
- Dokumentasi API
- Pengujian Endpoint
- README Project
- Quality Assurance
- Dokumentasi Pengujian

---

# Lisensi

Project ini dibuat sebagai tugas akhir pengembangan Web Service REST API.
