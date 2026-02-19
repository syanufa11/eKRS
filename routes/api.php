<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/v1/enrollments-test', function (Request $request) {

    try {
        $validated = $request->validate([
            'student_nim'   => 'required|numeric|digits_between:8,12',
            'student_name'  => 'required|string|min:3|max:100',
            'student_email' => 'required|email|max:150',
            'course_id'     => 'required|exists:courses,id',
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'      => 'required|in:1,2',
            'status'        => 'required|in:DRAFT,SUBMITTED,APPROVED,REJECTED',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Validasi Berhasil',
            'data' => $validated
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'errors' => $e->errors()
        ], 422);
    }
});
