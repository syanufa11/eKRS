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

## Screenshot

> Folder `screenshoot/` berisi bukti visual pengujian setiap skenario (TS-01 hingga TS-13),
> termasuk tampilan tabel dengan jutaan data, form validasi, halaman trash, dan proses export CSV.
> Screenshot diurutkan berdasarkan nomor skenario untuk memudahkan penelusuran.

1. **Alert Export CSV.png**

![Alert Export CSV.png](./screenshoot/Alert%20Export%20CSV.png)

2. **Alert Success Update Enrollment.png**

![Alert Success Update Enrollment.png](./screenshoot/Alert%20Success%20Update%20Enrollment.png)

3. **CSV Filter.png**

![CSV Filter.png](./screenshoot/CSV%20Filter.png)

4. **Dashboard.png**

![Dashboard.png](./screenshoot/Dashboard.png)

5. **Data Course.png**

![Data Course.png](./screenshoot/Data%20Course.png)

6. **Data Enrollment - AND.png**

![Data Enrollment - AND.png](./screenshoot/Data%20Enrollment%20-%20AND.png)

7. **Data Enrollment - Filter Export CSV.png**

![Data Enrollment - Filter Export CSV.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Export%20CSV.png)

8. **Data Enrollment - Filter Semester.png**

![Data Enrollment - Filter Semester.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Semester.png)

9. **Data Enrollment - Filter Status.png**

![Data Enrollment - Filter Status.png](./screenshoot/Data%20Enrollment%20-%20Filter%20Status.png)

10. **Data Enrollment - Halaman Trash.png**

![Data Enrollment - Halaman Trash.png](./screenshoot/Data%20Enrollment%20-%20Halaman%20Trash.png)

11. **Data Enrollment - Konfirmasi Soft Delete.png**

![Data Enrollment - Konfirmasi Soft Delete.png](./screenshoot/Data%20Enrollment%20-%20Konfirmasi%20Soft%20Delete.png)

12. **Data Enrollment - Menu Trash.png**

![Data Enrollment - Menu Trash.png](./screenshoot/Data%20Enrollment%20-%20Menu%20Trash.png)

13. **Data Enrollment - OR.png**

![Data Enrollment - OR.png](./screenshoot/Data%20Enrollment%20-%20OR.png)

14. **Data Enrollment - Search Kode Mata Kuliah.png**

![Data Enrollment - Search Kode Mata Kuliah.png](./screenshoot/Data%20Enrollment%20-%20Search%20Kode%20Mata%20Kuliah.png)

15. **Data Enrollment - Search Nama.png**

![Data Enrollment - Search Nama.png](./screenshoot/Data%20Enrollment%20-%20Search%20Nama.png)

16. **Data Enrollment - Search NIM.png**

![Data Enrollment - Search NIM.png](./screenshoot/Data%20Enrollment%20-%20Search%20NIM.png)

17. **Data Enrollment - Sorting Asc.png**

![Data Enrollment - Sorting Asc.png](./screenshoot/Data%20Enrollment%20-%20Sorting%20Asc.png)

18. **Data Enrollment - Sorting Desc.png**

![Data Enrollment - Sorting Desc.png](./screenshoot/Data%20Enrollment%20-%20Sorting%20Desc.png)

19. **Data Enrollment setelah update.png**

![Data Enrollment setelah update.png](./screenshoot/Data%20Enrollment%20setelah%20update.png)

20. **Data Enrollment.png**

![Data Enrollment.png](./screenshoot/Data%20Enrollment.png)

21. **Form Course.png**

![Form Course.png](./screenshoot/Form%20Course.png)

22. **Form Edit Enrollment.png**

![Form Edit Enrollment.png](./screenshoot/Form%20Edit%20Enrollment.png)

23. **Form Enrollment.png**

![Form Enrollment.png](./screenshoot/Form%20Enrollment.png)

24. **Login.png**

![Login.png](./screenshoot/Login.png)

25. **CSV Keseluruhan-1.png**

![CSV Keseluruhan-1.png](./screenshoot/CSV%20Keseluruhan-1.png)

26. **CSV Keseluruhan-2.png**

![CSV Keseluruhan-2.png](./screenshoot/CSV%20Keseluruhan-2.png)


---

## Pengembang

| Field   | Detail                                          |
|---------|-------------------------------------------------|
| Nama    | Tasya Nurul Fadila                              |
| Posisi  | Web Developer (Full Stack)                      |
| Topik   | Pengelolaan Data Akademik Skala Besar (5 Juta Data) |
