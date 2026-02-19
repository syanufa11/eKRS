<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $rows = [];
        for ($i = 1; $i <= 50; $i++) {
            $rows[] = [
                'code' => "MK" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => "Course $i",
                'credits' => rand(1, 6),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('courses')->insert($rows);
        $this->command->info("Inserted 50 courses");
    }
}
