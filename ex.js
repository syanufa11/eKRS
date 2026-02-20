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

const screenshotDescriptions = {
    "Login.png":
        "Halaman login sistem eKRS. Pengguna memasukkan email dan password untuk mengakses dashboard.",
    "Dashboard.png":
        "Halaman utama setelah login. Menampilkan ringkasan statistik sistem.",
    "Data Course.png":
        "Halaman manajemen mata kuliah. Menampilkan daftar seluruh mata kuliah beserta kode, nama, dan jumlah SKS.",
    "Form Course.png":
        "Form tambah/edit mata kuliah dengan validasi real-time (Livewire).",
    "Alert Success Tambah Course.png":
        "Notifikasi sukses setelah data mata kuliah baru berhasil disimpan.",
    "Form Tambah Enrollment.png":
        "Halaman pengisian KRS baru menggunakan transaksi atomik.",
    "Alert Success Tambah Enrollment.png":
        "Notifikasi sukses setelah proses enrollment berhasil dilakukan.",
    "Data Enrollment.png":
        "Halaman utama data enrollment (KRS) 5.000.000 baris data dengan Server-Side Pagination.",
    "Data Enrollment - Sorting Asc.png":
        "Fitur sorting ascending (A→Z) pada 5 juta data.",
    "Data Enrollment - Sorting Desc.png":
        "Fitur sorting descending (Z→A) pada 5 juta data.",
    "Data Enrollment - Filter Status.png":
        "Quick Filter berdasarkan status aktif/tidak aktif.",
    "Data Enrollment - Filter Semester.png":
        "Penyaringan data enrollment berdasarkan semester.",
    "Data Enrollment - AND.png": "Advanced Filter dengan logika AND.",
    "Data Enrollment - OR.png": "Advanced Filter dengan logika OR.",
    "Data Enrollment - Search NIM.png":
        "Fitur Live Search berdasarkan NIM dengan debounce.",
    "Data Enrollment - Search Nama.png":
        "Fitur Live Search berdasarkan Nama Mahasiswa.",
    "Data Enrollment - Search Kode Mata Kuliah.png":
        "Pencarian instan berdasarkan kode mata kuliah.",
    "Form Edit Enrollment.png": "Form untuk memperbarui data enrollment.",
    "Data Enrollment setelah update.png":
        "Tampilan tabel setelah data berhasil di-update.",
    "Pilih Data Enrollment (untuk dihapus).png":
        "Proses seleksi data enrollment untuk dihapus.",
    "Konfirmasi Hapus ke Trash.png": "Dialog konfirmasi Soft Delete.",
    "Data Trash Enrollment.png":
        "Halaman Trash (Sampah) data yang dihapus sementara.",
    "Konfirmasi Restore.png": "Proses mengembalikan data dari Trash.",
    "Konfirmasi Hapus Permanen.png": "Dialog konfirmasi Force Delete.",
    "Export CSV (All).png": "Proses ekspor 5.000.000 baris data via streaming.",
    "Hasil CSV (All).png": "Bukti file CSV hasil ekspor keseluruhan.",
    "Export CSV (Filter).png": "Proses ekspor data hasil filter.",
    "Hasil CSV (Filter).png": "File CSV hasil filter.",
    "Data Mahasiswa.png": "Halaman manajemen data mahasiswa.",
    "Detail Mahasiswa.png": "Tampilan detail profil mahasiswa.",
};

const customOrder = Object.keys(screenshotDescriptions);

let screenshotSection = "";
if (fs.existsSync(screenshotDir)) {
    const availableImages = fs
        .readdirSync(screenshotDir)
        .filter((file) => /\.(png|jpg|jpeg|gif)$/i.test(file));
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
                screenshotDescriptions[img] || "Dokumentasi antarmuka eKRS.";
            screenshotSection += `### ${index + 1}. ${label}\n\n`;
            screenshotSection += `> ${desc}\n\n`;
            screenshotSection += `![${img}](./screenshoot/${encodedImg})\n\n`;
            if (index < orderedImages.length - 1)
                screenshotSection += `---\n\n`;
        });
    }
}

const readmeContent = `# Sistem eKRS – Elektronik Kartu Rencana Studi

## Deskripsi
Sistem manajemen KRS performa tinggi berbasis **Laravel (PHP)** dan **PostgreSQL**, dioptimasi untuk menangani **5.000.000 data**.

---

## Arsitektur Proyek

Sistem ini memisahkan tanggung jawab antara pengelolaan data (Backend) dan tampilan antarmuka (Frontend) secara terstruktur:

| Layer | Folder Utama | Deskripsi |
| :--- | :--- | :--- |
| **Backend (Logic)** | \`app/Http/\` | Berisi Controller, Livewire Component, dan Business Logic. |
| **Database** | \`database/\` | Berisi Migrasi tabel, Seeders untuk 5jt data, dan Factory. |
| **Frontend** | \`resources/views/\` | Template antarmuka menggunakan Blade dan komponen Livewire. |
| **Routing** | \`routes/\` | Definisi jalur akses web dan autentikasi admin. |
| **Public Assets** | \`public/\` | Berisi file CSS, JS, dan aset gambar yang dapat diakses browser. |



---

## Fitur Utama
1. **Atomic Transaction:** Menjaga integritas data pada 3 tabel sekaligus (All-or-Nothing).
2. **Server-Side Pagination:** Efisiensi pemuatan data masif (5 Juta Baris).
3. **Livewire Power:** Interaksi dinamis tanpa refresh halaman (Search, Filter, CRUD).
4. **Streaming Export:** Export CSV 5 Juta data tanpa membebani RAM server.

---

## Skenario Pengujian
| Kode | Skenario |
| :--- | :--- |
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

### 1. Persiapan Database
Buka Laragon atau pgAdmin, buat database baru:
\`\`\`sql
CREATE DATABASE krs;
\`\`\`

### 2. Setup Project
\`\`\`bash
git clone https://github.com/syanufa11/eKRS
cd eKRS
composer update
cp .env.example .env
php artisan key:generate
\`\`\`

### 3. Migrasi & Import Data
\`\`\`bash
php artisan migrate:fresh --seed
\`\`\`
*Import manual untuk data masif:*
\`\`\`sql
COPY enrollments(student_id, course_id, academic_year, semester, status, created_at, updated_at)
FROM 'D:/Project Laravel/krs/storage/app/enrollments_unique.csv'
DELIMITER ',' CSV;
\`\`\`

---

## Struktur Lengkap Project
\`\`\`
${structure}
\`\`\`

---

## Laporan Teknis
📄 **[Buka Laporan Teknis – Google Docs](https://docs.google.com/document/d/1fAwluqN60qymQJml4SoKel0-9pn96gRs9J1T2eg4txw/preview)**

---

${screenshotSection}

---

## Pengembang
| Field | Detail |
| :--- | :--- |
| **Nama** | Tasya Nurul Fadila |
| **Posisi** | Web Developer (Full Stack) |
| **Keahlian** | PHP (Laravel), SQL, JavaScript |
`;

fs.writeFileSync("README.md", readmeContent);
console.log("README.md berhasil dibuat dengan struktur lengkap!");
