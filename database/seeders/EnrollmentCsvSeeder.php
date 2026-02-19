<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentCsvSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/enrollments_unique.csv');
        $f = fopen($path, 'w');

        $courses = DB::table('courses')->pluck('id');
        $years = [];
        for ($y = 2016; $y <= 2025; $y++) {
            $years[] = "$y/" . ($y + 1);
        }

        $total = 0;
        $this->command->info("Generating EXACT 5,000,000 rows...");

        DB::table('students')->orderBy('id')->chunk(1000, function ($students) use ($courses, $years, &$total, $f) {
            foreach ($students as $s) {
                foreach ($courses as $c) {
                    foreach ($years as $y) {
                        foreach ([1, 2] as $sem) {
                            fputcsv($f, [
                                $s->id,
                                $c,
                                $y,
                                $sem,
                                ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'][array_rand([0, 1, 2, 3])],
                                now(),
                                now()
                            ]);
                            $total++;
                        }
                    }
                }
            }
            $this->command->info("Generated {$total}");
        });

        fclose($f);
        $this->command->info("DONE: {$total} rows.");
    }
}
