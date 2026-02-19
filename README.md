# Sistem eKRS – Elektronik Kartu Rencana Studi

## Deskripsi

Sistem ini dibangun menggunakan **Laravel (PHP)** untuk menangani proses manajemen Kartu Rencana Studi (KRS) dengan performa tinggi, mampu mengelola dataset hingga **5.000.000 baris data**.

Sistem mencakup manajemen **mahasiswa**, **mata kuliah**, **enrollment (KRS)**, fitur pencarian & filter canggih, serta **export data masif** berbasis streaming.

---

## Teknologi

- **Backend:** Laravel (PHP)
- **Frontend:** Livewire + Tailwind CSS
- **Database:** PostgreSQL
- **Autentikasi:** Laravel Auth

---

## Fitur Utama

### 1. Atomic Transaction (3 Tabel)
Setiap operasi create/update melibatkan transaksi database yang menjamin konsistensi data pada tabel `students`, `courses`, dan `enrollments` dengan prinsip **All-or-Nothing**. Jika terjadi kegagalan di tengah proses, sistem otomatis melakukan rollback.

### 2. Validasi Berlapis (Frontend & Backend)
- **Frontend:** Real-time feedback menggunakan Livewire — validasi terjadi saat pengguna mengetik, sebelum tombol simpan ditekan.
- **Backend:** Validasi server-side memastikan keamanan data meskipun request dikirim langsung via API tools. Mencakup pengecekan duplikasi NIM/email, aturan bisnis enrollment, dan pencocokan pola Regex untuk kode mata kuliah.

### 3. Server-Side Pagination & Sorting
Sistem tidak memuat semua 5 juta data ke browser. Database hanya mengirimkan 10–25 baris sesuai halaman aktif menggunakan `LIMIT` dan `OFFSET` PostgreSQL. Pengguna dapat mengurutkan data berdasarkan NIM, Nama, Kode MK, Semester, atau Status secara dinamis.

### 4. Quick Filter & Live Search
- **Quick Filter:** Filter berdasarkan parameter paling sering digunakan dalam administrasi akademik (tahun ajaran, semester, status). Bekerja real-time tanpa reload halaman.
- **Live Search:** Pencarian instan berdasarkan NIM, Nama Mahasiswa, dan Kode Mata Kuliah dengan mekanisme **debounce** untuk efisiensi query.

### 5. Advanced Filter (AND/OR Logic)
Mendukung pencarian multi-kondisi — pengguna dapat menggabungkan parameter Tahun Ajaran, Status, dan Kode MK secara bersamaan. Query dibangun dinamis menggunakan Laravel Query Builder untuk execution plan yang optimal.

### 6. Soft Deletes & Fitur Trash
Data yang dihapus tidak hilang permanen — melainkan ditandai via kolom `deleted_at` dan dipindahkan ke menu **Trash**. Administrator dapat melakukan **restore** data atau **force delete** secara permanen.

### 7. Export Streaming (5 Juta Baris)
Ekspor data ke format CSV menggunakan metode **streaming** — data dikirimkan langsung ke browser bit demi bit tanpa membebani RAM server. Mendukung dua mode:
- **Keseluruhan:** Mengunduh seluruh data mentah dari database.
- **Hasil Filter:** Mengunduh hanya data yang telah disaring.

---

## Skenario Pengujian

| Kode | Skenario |
|------|----------|
| TS-01 | Setup & Seed 5 Juta Data |
| TS-02 | Create dengan Atomic Transaction (3 Tabel) |
| TS-03 | Validasi Frontend (Livewire Real-time) |
| TS-04 | Validasi Backend (Security & Integrity) |
| TS-05 | Read Table & Server-Side Pagination |
| TS-06 | Sorting Server-Side |
| TS-07 | Quick Filter |
| TS-08 | Live Searching dengan Debounce |
| TS-09 | Advanced Filter (AND Logic) |
| TS-10 | Advanced Filter (OR Logic) |
| TS-11 | Update Data dengan Validasi Berlapis |
| TS-12 | Delete dengan Soft Deletes & Trash |
| TS-13 | Export CSV 5 Juta Baris (Streaming) |

---

## Instalasi

### Persyaratan
- PHP >= 8.1
- Composer
- PostgreSQL

### Langkah-langkah

1. **Clone repositori:**
```bash
git clone https://github.com/syanufa11/eKRS
cd eKRS
```

2. **Install dependencies:**
```bash
composer update
```

3. **Copy file `.env.example` ke `.env` dan sesuaikan konfigurasi database:**
```bash
cp .env.example .env
```

4. **Generate APP_KEY:**
```bash
php artisan key:generate
```

5. **Jalankan migrasi dan seeder:**
```bash
php artisan migrate:fresh --seed
```

6. **Import data 5 juta baris (via PostgreSQL COPY):**
```sql
COPY enrollments(student_id, course_id, academic_year, semester, status, created_at, updated_at)
FROM '/path/to/storage/app/enrollments_unique.csv'
DELIMITER ',' CSV;
```

Verifikasi jumlah data:
```sql
SELECT COUNT(*) FROM enrollments;
-- Output: 5.000.000
```

7. **Jalankan server:**
```bash
php artisan serve
```

---

## Akun Default (Setelah Seeding)

> Setelah menjalankan migrate & seeder, gunakan akun berikut untuk login sebagai admin:

| Field    | Value             |
|----------|-------------------|
| Email    | admin@ekrs.com    |
| Password | password          |

---

## Struktur Project
```
.env.example
app/
artisan
composer.json
database/
package.json
public/
resources/
routes/

```

---

## Laporan Teknis

Laporan teknis lengkap mencakup implementasi seluruh skenario pengujian (TS-01 hingga TS-13), strategi seeding 5 juta data, penjelasan kode Atomic Transaction, Soft Deletes, Streaming Export, dan bukti pengujian dapat diakses melalui tautan berikut:

📄 **[Buka Laporan Teknis – Google Docs](https://docs.google.com/document/d/1fAwluqN60qymQJml4SoKel0-9pn96gRs9J1T2eg4txw/preview)**

> Laporan ini disusun sebagai dokumentasi resmi pengembangan Sistem eKRS skala besar oleh **Tasya Nurul Fadila**.

---

## Screenshot

### 1. Login

> Halaman login sistem eKRS. Pengguna memasukkan email dan password untuk mengakses dashboard. Akun default: `admin@ekrs.com` / `password`.

![Login.png](./screenshoot/Login.png)

---

### 2. Dashboard

> Halaman utama setelah login. Menampilkan ringkasan statistik sistem seperti jumlah mahasiswa, mata kuliah, dan total enrollment yang terdaftar.

![Dashboard.png](./screenshoot/Dashboard.png)

---

### 3. Data Course

> Halaman manajemen mata kuliah. Menampilkan daftar seluruh mata kuliah beserta kode, nama, dan jumlah SKS. Admin dapat menambah, mengedit, dan menghapus data.

![Data Course.png](./screenshoot/Data%20Course.png)

---

### 4. Form Course

> Form tambah/edit mata kuliah dengan validasi real-time (Livewire). Kode mata kuliah divalidasi menggunakan Regex untuk memastikan format sesuai standar institusi.

![Form Course.png](./screenshoot/Form%20Course.png)

---

### 5. Form Enrollment

> Form pengisian KRS baru. Mahasiswa dan mata kuliah dihubungkan dalam satu transaksi atomik yang menjamin konsistensi data di tabel `students`, `courses`, dan `enrollments`.

![Form Enrollment.png](./screenshoot/Form%20Enrollment.png)

---

### 6. Data Enrollment

> Halaman utama data enrollment (KRS). Menampilkan 5.000.000 baris data secara efisien menggunakan Server-Side Pagination — hanya 10–25 baris yang dimuat per halaman.

![Data Enrollment.png](./screenshoot/Data%20Enrollment.png)

---

### 7. Data Enrollment - Sorting Asc

> Fitur sorting ascending (A→Z / terkecil→terbesar). Pengurutan dieksekusi langsung di PostgreSQL menggunakan `ORDER BY`, menjaga performa tetap stabil pada 5 juta data.

![Data Enrollment - Sorting Asc.png](./screenshoot/Data%20Enrollment%20-%20Sorting%20Asc.png)

---

### 8. Data Enrollment - Sorting Desc

> Fitur sorting descending (Z→A / terbesar→terkecil). Sama seperti sorting ascending, query dijalankan di sisi server tanpa memuat seluruh data ke memori.

![Data Enrollment - Sorting Desc.png](./screenshoot/Data%20Enrollment%20-%20Sorting%20Desc.png)

---

### 9. Data Enrollment - Filter Status

> Quick Filter berdasarkan **Status** enrollment (aktif/tidak aktif). Filter bekerja real-time menggunakan Livewire dan mengirimkan query `WHERE` baru ke database tanpa reload halaman.

![Data Enrollment - Filter Status.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Status.png)

---

### 10. Data Enrollment - Filter Semester

> Quick Filter berdasarkan **Semester**. Memungkinkan admin menyaring data enrollment per semester tertentu secara instan dari 5 juta baris data.

![Data Enrollment - Filter Semester.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Semester.png)

---

### 11. Data Enrollment - AND

> Advanced Filter dengan logika **AND** — menggabungkan beberapa kondisi (misal: Tahun Ajaran + Status + Kode MK) secara bersamaan. Query dibangun dinamis oleh Laravel Query Builder.

![Data Enrollment - AND.png](./screenshoot/Data%20Enrollment%20-%20AND.png)

---

### 12. Data Enrollment - OR

> Advanced Filter dengan logika **OR** — menampilkan data yang memenuhi salah satu dari beberapa kondisi yang dipilih. Cocok untuk pencarian data yang lebih fleksibel.

![Data Enrollment - OR.png](./screenshoot/Data%20Enrollment%20-%20OR.png)

---

### 13. Data Enrollment - Search NIM

> Fitur Live Search berdasarkan **NIM**. Pencarian real-time dengan mekanisme debounce (300ms) agar tidak membebani server dengan query berlebih saat pengguna mengetik.

![Data Enrollment - Search NIM.png](./screenshoot/Data%20Enrollment%20-%20Search%20NIM.png)

---

### 14. Data Enrollment - Search Nama

> Fitur Live Search berdasarkan **Nama Mahasiswa**. Query menggunakan klausa `LIKE` yang diarahkan ke indeks kolom PostgreSQL untuk hasil yang cepat di tengah 5 juta data.

![Data Enrollment - Search Nama.png](./screenshoot/Data%20Enrollment%20-%20Search%20Nama.png)

---

### 15. Data Enrollment - Search Kode Mata Kuliah

> Fitur Live Search berdasarkan **Kode Mata Kuliah**. Memudahkan pencarian enrollment untuk mata kuliah tertentu secara spesifik dan instan.

![Data Enrollment - Search Kode Mata Kuliah.png](./screenshoot/Data%20Enrollment%20-%20Search%20Kode%20Mata%20Kuliah.png)

---

### 16. Form Edit Enrollment

> Form edit data enrollment yang sudah ada. Setiap perubahan melewati validasi berlapis (frontend + backend) dan dibungkus dalam Atomic Transaction untuk menjaga integritas data.

![Form Edit Enrollment.png](./screenshoot/Form%20Edit%20Enrollment.png)

---

### 17. Alert Success Update Enrollment

> Notifikasi sukses setelah data enrollment berhasil diperbarui. Alert ditampilkan secara real-time oleh Livewire tanpa reload halaman.

![Alert Success Update Enrollment.png](./screenshoot/Alert%20Success%20Update%20Enrollment.png)

---

### 18. Data Enrollment setelah update

> Tampilan tabel enrollment setelah proses update berhasil dilakukan. Data terbaru langsung tercermin di tabel tanpa perlu refresh manual.

![Data Enrollment setelah update.png](./screenshoot/Data%20Enrollment%20setelah%20update.png)

---

### 19. Data Enrollment - Konfirmasi Soft Delete

> Dialog konfirmasi sebelum menghapus data enrollment. Sistem menggunakan **Soft Delete** — data tidak langsung hilang, melainkan ditandai `deleted_at` di database.

![Data Enrollment - Konfirmasi Soft Delete.png](./screenshoot/Data%20Enrollment%20-%20Konfirmasi%20Soft%20Delete.png)

---

### 20. Data Enrollment - Menu Trash

> Menu Trash (Sampah) yang menampilkan seluruh data enrollment yang telah di-soft delete. Admin dapat memilih untuk **restore** atau **force delete** secara permanen.

![Data Enrollment - Menu Trash.png](./screenshoot/Data%20Enrollment%20-%20Menu%20Trash.png)

---

### 21. Data Enrollment - Halaman Trash

> Halaman detail Trash. Memperlihatkan daftar data terhapus lengkap dengan opsi pemulihan (restore) per baris, menjaga fleksibilitas pengelolaan data tanpa risiko kehilangan permanen.

![Data Enrollment - Halaman Trash.png](./screenshoot/Data%20Enrollment%20-%20Halaman%20Trash.png)

---

### 22. Data Enrollment - Filter Export CSV

> Tampilan data enrollment yang telah difilter, siap untuk diekspor. Sistem mendukung ekspor **hanya data hasil filter** tanpa harus mengunduh seluruh 5 juta baris.

![Data Enrollment - Filter Export CSV.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Export%20CSV.png)

---

### 23. Alert Export CSV

> Notifikasi konfirmasi saat proses export CSV dimulai. Ekspor menggunakan metode **streaming** sehingga file langsung dikirim ke browser tanpa membebani RAM server.

![Alert Export CSV.png](./screenshoot/Alert%20Export%20CSV.png)

---

### 24. CSV Keseluruhan-1

> Proses unduhan CSV keseluruhan (bagian 1). Seluruh 5.000.000 baris data diekspor menggunakan teknik streaming — data dikirim bit demi bit langsung ke browser.

![CSV Keseluruhan-1.png](./screenshoot/CSV%20Keseluruhan-1.png)

---

### 25. CSV Keseluruhan-2

> Proses unduhan CSV keseluruhan (bagian 2). Memperlihatkan kelanjutan proses streaming export yang berjalan lancar tanpa timeout atau memory exhaustion di sisi server.

![CSV Keseluruhan-2.png](./screenshoot/CSV%20Keseluruhan-2.png)

---

### 26. CSV Filter

> Hasil file CSV dari ekspor data yang telah difilter. Hanya baris data yang sesuai kondisi filter yang masuk ke file, menghasilkan laporan yang lebih ringkas dan relevan.

![CSV Filter.png](./screenshoot/CSV%20Filter.png)

---


---

## Pengembang

| Field   | Detail                                              |
|---------|-----------------------------------------------------|
| Nama    | Tasya Nurul Fadila                                  |
| Posisi  | Web Developer (Full Stack)                          |
| Topik   | Pengelolaan Data Akademik Skala Besar (5 Juta Data) |
