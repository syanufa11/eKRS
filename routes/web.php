<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\EnrollmentExportController;

// Full-page Livewire Components
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Profile;
use App\Livewire\Dashboard;
use App\Livewire\CourseManager;
use App\Livewire\CourseTrashedManager;
use App\Livewire\EnrollmentManager;
use App\Livewire\EnrollmentTrashedManager;
use App\Livewire\StudentManager;
use App\Livewire\StudentTrashedManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Livewire\Documentation;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', Dashboard::class)->name('dashboard');
    // Optional jika mau akses via /dashboard juga
    Route::get('/dashboard', Dashboard::class);

    // Profile
    Route::get('/admin/profile', \App\Livewire\Admin\Profile::class)->name('admin.profile');
    Route::get('/admin/change-password', \App\Livewire\Admin\ChangePassword::class)->name('admin.password');
    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', CourseManager::class)->name('index');
        Route::get('/trashed', CourseTrashedManager::class)->name('trashed');
    });

    /*
    |--------------------------------------------------------------------------
    | Enrollments
    |--------------------------------------------------------------------------
    */
    Route::prefix('enrollments')->name('enrollments.')->group(function () {
        Route::get('/', EnrollmentManager::class)->name('index');
        Route::get('/trashed', EnrollmentTrashedManager::class)->name('trashed');

        // ── Export routes di sini — di dalam prefix enrollments & group auth ──
        // Letakkan SEBELUM fallback/wildcard agar tidak bentrok dengan route lain
        Route::get('/export/csv', [EnrollmentExportController::class, 'csv'])
            ->name('export.csv');

        Route::get('/export/status', [EnrollmentExportController::class, 'status'])
            ->name('export.status');
    });

    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', StudentManager::class)->name('index');
        Route::get('/trashed', StudentTrashedManager::class)->name('trashed');
    });

    /*
    |--------------------------------------------------------------------------
    | Logout (Centralized)
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kirim pesan flash ke session
        return redirect('/login')->with('notify', [
            'message' => 'Anda telah berhasil keluar sistem.',
            'type'    => 'success'
        ]);
    })->name('logout');

    // Route untuk Halaman Dokumentasi Teknis (TS-01 s/d TS-13)
    Route::get('/documentation', Documentation::class)->name('documentation');
});

/*
|--------------------------------------------------------------------------
| Export Routes (Controller Based)
|--------------------------------------------------------------------------
*/


Route::fallback(function () {
    return view('errors.error-404'); // Sesuaikan dengan nama file view Anda
});
