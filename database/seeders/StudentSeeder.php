<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $rows = [];
        for ($i = 1; $i <= 5000; $i++) {
            $rows[] = [
                'nim' => str_pad($i, 10, '0', STR_PAD_LEFT),
                'name' => "Student $i",
                'email' => "student$i@mail.com",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('students')->insert($rows);
        $this->command->info("Inserted 5000 students");
    }
}
