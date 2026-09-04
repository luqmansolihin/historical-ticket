# TicketTrace — Historical Ticket Management System 🎫✈️🚆

> **TicketTrace** adalah sistem manajemen & rekapitulasi histori tiket perjalanan dinas perusahaan berbasis Laravel 11. Dilengkapi dengan otorisasi berbasis Role (*Admin*, *Booker & Payer*, *User*), filter multiple selection, log aktivitas perubahan status, tampilan E-Ticket Boarding Pass, serta ekspor CSV.

---

## 🌟 Fitur Utama

- **📊 Dashboard Analytics & Statistics Cards**
  - Ringkasan total pengeluaran biaya tiket, total tiket terdaftar, tiket berstatus Lunas, Belum Bayar, dan Dibatalkan.
- **🔍 Dynamic Multi-Select Search & Filter Toolbar**
  - Pencarian kata kunci fleksibel (*Kode Tiket*, *Rute*, *Nama Penumpang*, *Booker & Payer*).
  - Multi-select dropdown interaktif berbasis Alpine.js untuk **Moda Transportasi** (*Pesawat, Kereta Api, Bus, Travel, Kapal Laut, Mobil/Rental*) dan **Status Pembayaran** (*Lunas, Belum Bayar, Dibatalkan*).
  - Penyaringan tanggal keberangkatan (*Dari Tanggal* & *Sampai Tanggal*).
- **🎫 E-Ticket Boarding Pass View & Print**
  - Modal tampilan detail E-Ticket berdesain *boarding pass* maskapai penerbangan modern.
  - Cetak/print otomatis (`window.print()`).
- **👥 Dynamic Multiple Passengers Input**
  - Input banyak nama penumpang secara fleksibel dalam satu tiket pendaftaran.
- **📜 Sequential Status Activity Log & Timeline**
  - Pencatatan riwayat perubahan status tiket secara urut bertahap (*Step 1 ➔ Step 2 ➔ Step 3*).
  - Mencatat aktor pengubah, role, status awal, status tujuan, catatan keterangan, dan timestamp.
- **👥 Admin User Management Dashboard**
  - Kelola seluruh akun pengguna terdaftar (`/users`) khusus untuk role **Admin**.
  - Pendaftaran akun baru, pembaruan profil & role, serta reset password.
- **📥 Export Data CSV**
  - Ekspor rekapitulasi data tiket sesuai filter aktif secara langsung ke format berkas CSV.

---

## 🛡️ Matriks Hak Akses Berdasarkan Role

| Fitur / Hak Akses | Admin 🛡️ | Booker & Payer 📝💳 | User Regular 👤 |
|---|:---:|:---:|:---:|
| **Lihat Daftar & Detail Tiket** | ✅ | ✅ | ✅ |
| **Tambah Tiket Baru** | ✅ | ✅ *(Belum Bayar / Lunas)* | ❌ |
| **Edit Tiket Status "Belum Bayar"** | ✅ | ✅ *(Bisa ubah rute, biaya, & konfirmasi Lunas)* | ❌ |
| **Edit Tiket Status "Lunas"** | ✅ | ⚠️ *Read-only* *(Bisa edit tgl bayar & Dibatalkan)* | ❌ |
| **Edit Tiket Status "Dibatalkan"** | ✅ | ❌ *(Terkunci Permanen)* | ❌ |
| **Hapus Data Tiket** | ✅ *(Semua Status)* | ⚠️ *(Hanya status Belum Bayar)* | ❌ |
| **Kelola Akun (User Management)** | ✅ | ❌ | ❌ |

---

## 🛠️ Teknologi Yang Digunakan (Tech Stack)

- **Backend Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend / UI**: Vanilla CSS / Tailwind CSS (Dark Glassmorphic Theme) & Alpine.js
- **Database**: MySQL / MariaDB / PostgreSQL / SQLite
- **Icons & Typography**: FontAwesome 6, Google Fonts (Plus Jakarta Sans & Outfit)

---

## 🚀 Panduan Instalasi Lokal (Development)

### 1. Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- Database Engine (MySQL / MariaDB)

### 2. Langkah-Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/luqmansolihin/historical-ticket.git
cd historical-ticket

# 2. Install dependency PHP & JavaScript
composer install
npm install

# 3. Salin file lingkungan (.env)
cp .env.example .env

# 4. Generate Application Key
php artisan key:generate

# 5. Konfigurasi Database di file .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=historical_ticket
DB_USERNAME=root
DB_PASSWORD=

# 6. Jalankan migrasi database & seeder
php artisan migrate:fresh --seed

# 7. Buat symlink storage untuk lampiran berkas (PDF/Gambar)
php artisan storage:link

# 8. Jalankan server lokal
php artisan serve
```

Akses aplikasi di peramban web pada alamat: `http://127.0.0.1:8000`

---

## 🔐 Kredensial Akun Default (Setelah Seeding)

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@ticket.com` | `password` |

*(Setelah berhasil login sebagai Admin, Anda dapat mendaftarkan akun Booker, Payer, atau User baru melalui menu **Daftar Akun**)*

---

## 📦 Panduan Deploy ke Server Production

### Opsi 1: Deployment di cPanel (Shared Hosting)

1. **Upload Berkas**: Unggah seluruh berkas proyek (kecuali folder `node_modules` dan `.env` lokal) ke server cPanel.
2. **Atur Document Root**: Arahkan *Document Root* domain/subdomain ke folder `public/`.
3. **Database**:
   - Buat database MySQL baru melalui menu *MySQL Databases* di cPanel.
   - Buat file `.env` di server dan sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
4. **Jalankan Perintah Optimasi via Terminal cPanel / SSH**:
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Opsi 2: Deployment di VPS (Ubuntu Nginx + PHP-FPM)

```bash
# 1. Clone proyek ke folder web server
cd /var/www/
git clone https://github.com/luqmansolihin/historical-ticket.git
cd historical-ticket

# 2. Atur izin akses folder storage & cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 3. Install composer & konfigurasi .env
composer install --no-dev --optimize-autoloader
cp .env.example .env

# Edit .env sesuai konfigurasi database produksi
nano .env

# 4. Generate key & migrasi database
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# 5. Jalankan optimasi cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk kebutuhan internal manajemen rekapitulasi tiket dan dilindungi oleh [MIT License](LICENSE).
