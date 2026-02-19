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
    const items = fs.readdirSync(dir);
    items.forEach((item) => {
        const fullPath = path.join(dir, item);
        let isDir = false;
        try {
            isDir = fs.statSync(fullPath).isDirectory();
        } catch {
            return; // skip broken symlinks atau file yang tidak bisa diakses
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

// Struktur folder/file penting
const structure = readImportantFolder(rootDir);

// Fungsi encode nama file agar aman di GitHub Markdown
function encodeFileName(fileName) {
    return encodeURI(fileName);
}

// Ambil gambar screenshoot (nama + preview) dengan urutan numerik
let screenshotSection = "";
if (fs.existsSync(screenshotDir)) {
    const images = fs
        .readdirSync(screenshotDir)
        .filter((file) => /\.(png|jpg|jpeg|gif)$/i.test(file))
        .sort((a, b) => {
            const aNum = parseInt(a.match(/\d+/)?.[0] || 0);
            const bNum = parseInt(b.match(/\d+/)?.[0] || 0);
            return aNum - bNum || a.localeCompare(b);
        });

    if (images.length > 0) {
        screenshotSection = "## Screenshot\n\n";
        screenshotSection +=
            "> Folder `screenshoot/` berisi bukti visual pengujian setiap skenario (TS-01 hingga TS-13),\n";
        screenshotSection +=
            "> termasuk tampilan tabel dengan jutaan data, form validasi, halaman trash, dan proses export CSV.\n";
        screenshotSection +=
            "> Screenshot diurutkan berdasarkan nomor skenario untuk memudahkan penelusuran.\n\n";

        images.forEach((img, index) => {
            const encodedImg = encodeFileName(img);
            screenshotSection += `${index + 1}. **${img}**\n\n`;
            screenshotSection += `![${img}](./screenshoot/${encodedImg})\n\n`;
        });
    }
} else {
    screenshotSection =
        "## Screenshot\n\n> Folder `screenshoot/` belum ditemukan. Tambahkan gambar ke folder tersebut lalu jalankan ulang script ini.\n\n";
}

// Konten README
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

5. **Jalankan migrasi dan seeder:**
\`\`\`bash
php artisan migrate:fresh --seed
\`\`\`

6. **Import data 5 juta baris (via PostgreSQL COPY):**
\`\`\`sql
COPY enrollments(student_id, course_id, academic_year, semester, status, created_at, updated_at)
FROM '/path/to/storage/app/enrollments_unique.csv'
DELIMITER ',' CSV;
\`\`\`

Verifikasi jumlah data:
\`\`\`sql
SELECT COUNT(*) FROM enrollments;
-- Output: 5.000.000
\`\`\`

7. **Jalankan server:**
\`\`\`bash
php artisan serve
\`\`\`

---

## Akun Default (Setelah Seeding)

> Setelah menjalankan migrate & seeder, gunakan akun berikut untuk login sebagai admin:

| Field    | Value             |
|----------|-------------------|
| Email    | admin@ekrs.com    |
| Password | password          |

---

## Struktur Project
\`\`\`
${structure}
\`\`\`

---

${screenshotSection}
---

## Pengembang

| Field   | Detail                                          |
|---------|-------------------------------------------------|
| Nama    | Tasya Nurul Fadila                              |
| Posisi  | Web Developer (Full Stack)                      |
| Topik   | Pengelolaan Data Akademik Skala Besar (5 Juta Data) |
`;

fs.writeFileSync("README.md", readmeContent);
console.log("README.md berhasil dibuat!");
