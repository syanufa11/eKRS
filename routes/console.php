<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\File;

// 1. Membersihkan sisa file export tiap jam
Schedule::call(function () {
    $path = storage_path('app/exports');

    if (File::isDirectory($path)) {
        $directories = File::directories($path);

        foreach ($directories as $dir) {
            // Cek waktu modifikasi folder (last modified)
            // Jika lebih tua dari 3 jam, hapus beserta isinya
            if (File::lastModified($dir) < now()->subHours(3)->timestamp) {
                File::deleteDirectory($dir);
            }
        }
    }
})->hourly();

// 2. Queue Worker (Penting untuk ExportEnrollmentsJob)
Schedule::command('queue:work --stop-when-empty')->everyMinute();
Schedule::command('queue:restart')->everyFiveMinutes();
