# Baku Lanjam — Luxury Timepieces Marketplace & Seller Centre

Website e-commerce dan katalog pasar jam tangan mewah terverifikasi (Rolex, Patek Philippe, Audemars Piguet, Richard Mille) yang dilengkapi dengan Seller Centre Admin Dashboard (Shopee-Seller style) untuk mengunggah produk secara langsung, mengelola stok, serta menerima penawaran valuasi (Jual / Tukar Tambah) dan pencarian jam langka (LuxeSource).

---

## 🚀 Fitur Utama

- **Katalog Jam Mewah Interaktif**: Filter berdasarkan Merek, Kondisi (*Unworn/Mint/Very Good*), Ketersediaan (*Tersedia/Dipesan/Sourced*), serta pencarian real-time.
- **Floating WhatsApp Button**: Menghubungi admin langsung melalui WhatsApp dengan pesan otomatis terisi rincian produk yang dipilih.
- **PDP Detail Modal & Fitur Checkout**: Melihat spesifikasi teknis lengkap jam (Case size, Material, Movement, Box & Papers) serta checkout multi-item via WhatsApp.
- **Valuasi & Tukar Tambah Customer**: Form interaktif bagi customer yang ingin menjual atau tukar tambah jam tangan mereka.
- **LuxeSource Form**: Form pesanan pencarian jam tangan langka global.
- **Baku Lanjam Seller Centre (Admin Dashboard)**:
  - Proteksi Login Khusus Administrator (`admin` / `admin123`).
  - Unggah jam baru lengkap dengan fitur Drag & Drop Upload Foto.
  - Ringkasan statistik otomatis (Total Produk, Nilai Total Inventaris IDR, Permintaan Valuasi Pending).
  - Manajemen katalog (Hapus produk, edit status).

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, Vanilla JavaScript, Tailwind CSS, Google Fonts (Plus Jakarta Sans, Bebas Neue, Barlow Condensed)
- **Backend API**: Laravel 12 (PHP 8.2+)
- **Database**: SQLite / MySQL

---

## 📦 Menjalankan Secara Lokal

1. **Jalankan Backend Server**:
   ```bash
   php artisan serve
   ```
   API akan berjalan di `http://127.0.0.1:8000`.

2. **Buka Toko**:
   - Storefront: `http://127.0.0.1:8000/index.html` (atau klik ganda `index.html`)
   - Seller Centre (Admin): `http://127.0.0.1:8000/admin.html` (Username: `admin`, Password: `admin123`)

---

## 🌐 Deploy ke GitHub & Vercel

### 1. Push ke GitHub
```bash
git init
git add .
git commit -m "feat: Baku Lanjam marketplace and seller centre dashboard"
git branch -M main
git remote add origin https://github.com/USERNAME/REPO_NAME.git
git push -u origin main
```

### 2. Deploy ke Vercel
1. Buka [vercel.com](https://vercel.com) dan login dengan akun GitHub Anda.
2. Klik **Add New Project** lalu pilih repositori GitHub ini.
3. Di bagian **Framework Preset**, pilih **Other** (atau biarkan default).
4. Klik **Deploy**! File konfigurasi [`vercel.json`](./vercel.json) sudah siap otomatis mengatur routing static web dan admin portal.
