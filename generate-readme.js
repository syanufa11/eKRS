import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Root project
const rootDir = __dirname;
const screenshotDir = path.join(rootDir, "screenshoot");

// Folder/file yang dianggap penting
const importantFolders = ["app", "routes", "database", "resources", "public"];
const importantFiles = [
    ".env.example",
    "composer.json",
    "package.json",
    "artisan",
];

// Fungsi baca folder/file penting secara rekursif
function readImportantFolder(dir, indent = "") {
    let result = "";
    if (!fs.existsSync(dir)) return result;
    const items = fs.readdirSync(dir);
    items.forEach((item) => {
        const fullPath = path.join(dir, item);
        let isDir = false;
        try {
            isDir = fs.statSync(fullPath).isDirectory();
        } catch {
            return;
        }

        if (isDir && importantFolders.includes(item)) {
            result += `${indent}${item}/\n`;
            result += readImportantFolder(fullPath, indent + "  ");
        } else if (!isDir && importantFiles.includes(item)) {
            result += `${indent}${item}\n`;
        }
    });
    return result;
}

const structure = readImportantFolder(rootDir);

function encodeFileName(fileName) {
    return encodeURI(fileName);
}

// ─── Mapping penjelasan per nama file screenshot ───────────────────────────
const screenshotDescriptions = {
    "Login.png":
        "Halaman login sistem eKRS. Pengguna memasukkan email dan password untuk mengakses dashboard. Akun default: `admin@ekrs.com` / `password`.",
    "Dashboard.png":
        "Halaman utama setelah login. Menampilkan ringkasan statistik sistem seperti jumlah mahasiswa, mata kuliah, dan total enrollment yang terdaftar.",
    "Data Course.png":
        "Halaman manajemen mata kuliah. Menampilkan daftar seluruh mata kuliah beserta kode, nama, dan jumlah SKS.",
    "Form Course.png":
        "Form tambah/edit mata kuliah dengan validasi real-time (Livewire).",
    "Alert Success Tambah Course.png":
        "Notifikasi sukses setelah data mata kuliah baru berhasil disimpan ke database.",
    "Form Tambah Enrollment.png":
        "Halaman pengisian KRS baru menggunakan transaksi atomik untuk menjaga integritas data.",
    "Alert Success Tambah Enrollment.png":
        "Notifikasi sukses setelah proses enrollment (pengisian KRS) berhasil dilakukan.",
    "Data Enrollment.png":
        "Halaman utama data enrollment (KRS). Menampilkan 5.000.000 baris data secara efisien menggunakan Server-Side Pagination.",
    "Data Enrollment - Sorting Asc.png":
        "Fitur sorting ascending (A→Z) yang dieksekusi langsung di PostgreSQL pada 5 juta data.",
    "Data Enrollment - Sorting Desc.png":
        "Fitur sorting descending (Z→A) yang dieksekusi di sisi server.",
    "Data Enrollment - Filter Status.png":
        "Quick Filter berdasarkan status aktif/tidak aktif secara real-time menggunakan Livewire.",
    "Data Enrollment - Filter Semester.png":
        "Penyaringan data enrollment berdasarkan semester tertentu.",
    "Data Enrollment - AND.png":
        "Advanced Filter dengan logika AND (menggabungkan beberapa kondisi sekaligus).",
    "Data Enrollment - OR.png":
        "Advanced Filter dengan logika OR untuk fleksibilitas pencarian.",
    "Data Enrollment - Search NIM.png":
        "Fitur Live Search berdasarkan NIM dengan mekanisme debounce 300ms.",
    "Data Enrollment - Search Nama.png":
        "Fitur Live Search berdasarkan Nama menggunakan index PostgreSQL untuk performa cepat.",
    "Data Enrollment - Search Kode Mata Kuliah.png":
        "Pencarian instan berdasarkan kode mata kuliah tertentu.",
    "Form Edit Enrollment.png":
        "Form untuk memperbarui data enrollment yang sudah ada dengan validasi berlapis.",
    "Data Enrollment setelah update.png":
        "Tampilan tabel yang langsung diperbarui setelah data berhasil di-update tanpa reload.",
    "Pilih Data Enrollment (untuk dihapus).png":
        "Proses seleksi data enrollment yang akan dihapus dari sistem.",
    "Konfirmasi Hapus ke Trash.png":
        "Dialog konfirmasi Soft Delete — memindahkan data ke tabel sampah tanpa menghapusnya dari disk.",
    "Data Trash Enrollment.png":
        "Halaman Trash yang menampung data yang telah dihapus sementara.",
    "Konfirmasi Restore.png":
        "Proses mengembalikan data dari Trash ke daftar aktif (Restore).",
    "Konfirmasi Hapus Permanen.png":
        "Dialog konfirmasi untuk menghapus data secara permanen (Force Delete) dari database.",
    "Export CSV (All).png":
        "Proses memulai ekspor seluruh 5.000.000 baris data menggunakan metode streaming.",
    "Hasil CSV (All).png":
        "Bukti file CSV hasil ekspor data keseluruhan yang berhasil diunduh.",
    "Export CSV (Filter).png":
        "Proses ekspor data yang sudah difilter sebelumnya agar laporan lebih spesifik.",
    "Hasil CSV (Filter).png":
        "File CSV yang hanya berisi data sesuai kriteria filter pengguna.",
    "Data Mahasiswa.png":
        "Halaman manajemen data mahasiswa. Menampilkan daftar seluruh mahasiswa yang terdaftar dalam sistem beserta informasi identitas pokok.",
    "Detail Mahasiswa.png":
        "Tampilan detail profil mahasiswa tertentu yang mencakup riwayat akademik dan informasi pribadi.",
};

// Urutan custom sesuai alur pengujian
const customOrder = [
    "Login.png",
    "Dashboard.png",
    "Data Course.png",
    "Form Course.png",
    "Alert Success Tambah Course.png",
    "Form Tambah Enrollment.png",
    "Alert Success Tambah Enrollment.png",
    "Data Enrollment.png",
    "Data Enrollment - Sorting Asc.png",
    "Data Enrollment - Sorting Desc.png",
    "Data Enrollment - Filter Status.png",
    "Data Enrollment - Filter Semester.png",
    "Data Enrollment - AND.png",
    "Data Enrollment - OR.png",
    "Data Enrollment - Search NIM.png",
    "Data Enrollment - Search Nama.png",
    "Data Enrollment - Search Kode Mata Kuliah.png",
    "Form Edit Enrollment.png",
    "Data Enrollment setelah update.png",
    "Pilih Data Enrollment (untuk dihapus).png",
    "Konfirmasi Hapus ke Trash.png",
    "Data Trash Enrollment.png",
    "Konfirmasi Restore.png",
    "Konfirmasi Hapus Permanen.png",
    "Export CSV (All).png",
    "Hasil CSV (All).png",
    "Export CSV (Filter).png",
    "Hasil CSV (Filter).png",
    "Data Mahasiswa.png",
    "Detail Mahasiswa.png",
];

// ─── Build section screenshot ───────────────────────────────────────────────
let screenshotSection = "";
if (fs.existsSync(screenshotDir)) {
    const availableImages = fs
        .readdirSync(screenshotDir)
        .filter((file) => /\.(png|jpg|jpeg|gif)$/i.test(file)); // File sesuai urutan custom + file lain yang tidak ada di list (fallback)

    const orderedImages = [
        ...customOrder.filter((f) => availableImages.includes(f)),
        ...availableImages.filter((f) => !customOrder.includes(f)).sort(),
    ];

    if (orderedImages.length > 0) {
        screenshotSection = "## Screenshot\n\n";

        orderedImages.forEach((img, index) => {
            const encodedImg = encodeFileName(img);
            const label = img.replace(/\.(png|jpg|jpeg|gif)$/i, "");
            const desc =
                screenshotDescriptions[img] ||
                "Dokumentasi tampilan antarmuka sistem eKRS.";

            screenshotSection += `### ${index + 1}. ${label}\n\n`;
            screenshotSection += `> ${desc}\n\n`;
            screenshotSection += `![${img}](./screenshoot/${encodedImg})\n\n`; // Hanya tambahkan garis pemisah jika BUKAN gambar terakhir

            if (index < orderedImages.length - 1) {
                screenshotSection += `---\n\n`;
            }
        });
    }
} else {
    screenshotSection =
        "## Screenshot\n\n> Folder `screenshoot/` belum ditemukan. Tambahkan gambar ke folder tersebut lalu jalankan ulang script ini.\n\n";
}

// ─── Konten README ──────────────────────────────────────────────────────────
const readmeContent = `# Sistem eKRS – Elektronik Kartu Rencana Studi

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
Setiap operasi create/update melibatkan transaksi database yang menjamin konsistensi data pada tabel \`students\`, \`courses\`, dan \`enrollments\` dengan prinsip **All-or-Nothing**. Jika terjadi kegagalan di tengah proses, sistem otomatis melakukan rollback.

### 2. Validasi Berlapis (Frontend & Backend)
- **Frontend:** Real-time feedback menggunakan Livewire — validasi terjadi saat pengguna mengetik, sebelum tombol simpan ditekan.
- **Backend:** Validasi server-side memastikan keamanan data meskipun request dikirim langsung via API tools. Mencakup pengecekan duplikasi NIM/email, aturan bisnis enrollment, dan pencocokan pola Regex untuk kode mata kuliah.

### 3. Server-Side Pagination & Sorting
Sistem tidak memuat semua 5 juta data ke browser. Database hanya mengirimkan 10–25 baris sesuai halaman aktif menggunakan \`LIMIT\` dan \`OFFSET\` PostgreSQL. Pengguna dapat mengurutkan data berdasarkan NIM, Nama, Kode MK, Semester, atau Status secara dinamis.

### 4. Quick Filter & Live Search
- **Quick Filter:** Filter berdasarkan parameter paling sering digunakan dalam administrasi akademik (tahun ajaran, semester, status). Bekerja real-time tanpa reload halaman.
- **Live Search:** Pencarian instan berdasarkan NIM, Nama Mahasiswa, dan Kode Mata Kuliah dengan mekanisme **debounce** untuk efisiensi query.

### 5. Advanced Filter (AND/OR Logic)
Mendukung pencarian multi-kondisi — pengguna dapat menggabungkan parameter Tahun Ajaran, Status, dan Kode MK secara bersamaan. Query dibangun dinamis menggunakan Laravel Query Builder untuk execution plan yang optimal.

### 6. Soft Deletes & Fitur Trash
Data yang dihapus tidak hilang permanen — melainkan ditandai via kolom \`deleted_at\` dan dipindahkan ke menu **Trash**. Administrator dapat melakukan **restore** data atau **force delete** secara permanen.

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
\`\`\`bash
git clone https://github.com/syanufa11/eKRS
cd eKRS
\`\`\`

2. **Install dependencies:**
\`\`\`bash
composer update
\`\`\`

3. **Copy file \`.env.example\` ke \`.env\` dan sesuaikan konfigurasi database:**
\`\`\`bash
cp .env.example .env
\`\`\`

4. **Generate APP_KEY:**
\`\`\`bash
php artisan key:generate
\`\`\`

5. **Buat Database Baru:**
Buka alat manajemen database Anda (pgAdmin, Laragon Terminal, atau Command Prompt) dan buat database baru dengan nama \`db_krs\`.
\`\`\`sql
CREATE DATABASE db_krs;
\`\`\`
*Jika menggunakan Laragon, pastikan layanan PostgreSQL sudah menyala (Start All).*

6. **Jalankan migrasi dan seeder:**
\`\`\`bash
php artisan migrate:fresh --seed
\`\`\`

7. **Import Data Masif (5 Juta Baris):**

Buka **Terminal Laragon**, lalu masuk ke prompt PostgreSQL:
\`\`\`bash
psql -U postgres -d db_krs
\`\`\`

Setelah masuk ke prompt PostgreSQL (\`db_krs=#\`), jalankan perintah \`COPY\`.
**Catatan:** Harap tunggu beberapa saat hingga proses selesai karena ukuran data yang besar. Sesuaikan path file dengan lokasi absolut folder project Anda.

\`\`\`sql
COPY enrollments(student_id, course_id, academic_year, semester, status, created_at, updated_at)
FROM 'C:/path/to/your/project/storage/app/enrollments_unique.csv'
DELIMITER ',' CSV;
\`\`\`

Verifikasi jumlah data:
\`\`\`sql
SELECT COUNT(*) FROM enrollments;
-- Output: 5.000.000
\`\`\`

8. **Install dependencies (Frontend) & Build Asset:**
\`\`\`bash
npm install
npm run dev
# atau untuk produksi
npm run build
\`\`\`

9. **Jalankan server:**
\`\`\`bash
php artisan serve
\`\`\`

---

## Akun Default (Setelah Seeding)

> Setelah menjalankan migrate & seeder, gunakan akun berikut untuk login sebagai admin:

| Field    | Value             |
|----------|-------------------|
| Email    | admin@ekrs.com    |
| Password | password          |

---

## Struktur Project
\`\`\`
${structure}
\`\`\`

---

## Laporan Teknis

Laporan teknis lengkap mencakup implementasi seluruh skenario pengujian (TS-01 hingga TS-13), strategi seeding 5 juta data, penjelasan kode Atomic Transaction, Soft Deletes, Streaming Export, dan bukti pengujian dapat diakses melalui tautan berikut:

📄 **[Buka Laporan Teknis – Google Docs](https://docs.google.com/document/d/1fAwluqN60qymQJml4SoKel0-9pn96gRs9J1T2eg4txw/preview)**

> Laporan ini disusun sebagai dokumentasi resmi pengembangan Sistem eKRS skala besar oleh **Tasya Nurul Fadila**.

---

${screenshotSection}

---

## Pengembang

| Field   | Detail                                              |
|---------|-----------------------------------------------------|
| Nama    | Tasya Nurul Fadila                                  |
| Posisi  | Web Developer (Full Stack)                          |
| Topik   | Pengelolaan Data Akademik Skala Besar (5 Juta Data) |
`;

fs.writeFileSync("README.md", readmeContent);
console.log("README.md berhasil dibuat!");
