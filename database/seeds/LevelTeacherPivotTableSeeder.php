<?php

use Illuminate\Database\Seeder;

class LevelTeacherPivotTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teachers = \App\Models\User\Teacher::all();
        $levels = \App\Models\Course\Level::all();
        foreach ($teachers as $teacher) {
            DB::table('level_teacher')->insert([
                'teacher_id' => $teacher->id,
                'level_id' => $levels->random()->id,
            ]);
            if (mt_rand(1,10) > 7) {
                DB::table('level_teacher')->insert([
                    'teacher_id' => $teacher->id,
                    'level_id' => $levels->random()->id,
                ]);
            }
        }
    }
}
