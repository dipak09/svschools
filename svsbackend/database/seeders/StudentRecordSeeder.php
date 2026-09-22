<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentRecordSeeder extends Seeder
{
    /**
     * How many demo records to generate for each user.
     * Override with: php artisan db:seed --class=StudentRecordSeeder
     * (change the constant, or set DEMO_RECORDS_PER_USER in .env)
     */
    public const PER_USER = 1000;

    public function run(): void
    {
        $perUser = (int) env('DEMO_RECORDS_PER_USER', self::PER_USER);

        $users = User::orderBy('id')->get(['id', 'name']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Create a user first, then re-run this seeder.');
            return;
        }

        $faker = \Faker\Factory::create('en_IN');

        $standards = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
        $divisions = ['A', 'B', 'C', 'D'];
        $statuses = ['active', 'active', 'active', 'inactive', 'alumni']; // weighted toward active

        foreach ($users as $user) {
            // Make the seeder re-runnable without duplicating rows.
            DB::table('student_records')->where('user_id', $user->id)->delete();

            $this->command->info("Seeding {$perUser} records for user #{$user->id} ({$user->name})...");

            // Insert in chunks so we never build one giant query.
            foreach (array_chunk(range(1, $perUser), 500) as $chunk) {
                $rows = [];

                foreach ($chunk as $i) {
                    $math = $faker->numberBetween(28, 100);
                    $science = $faker->numberBetween(28, 100);
                    $english = $faker->numberBetween(28, 100);
                    $total = $math + $science + $english;
                    $percentage = round($total / 3, 2);

                    $feesTotal = $faker->randomElement([25000, 32000, 40000, 48000, 55000]);
                    $feesPaid = $faker->randomElement([
                        $feesTotal,
                        $feesTotal,
                        round($feesTotal * 0.75, 2),
                        round($feesTotal * 0.5, 2),
                        round($feesTotal * 0.25, 2),
                    ]);

                    $name = $faker->name();

                    $rows[] = [
                        'user_id' => $user->id,
                        'roll_no' => sprintf('SVS-%d-%05d', $user->id, $i),
                        'student_name' => $name,
                        'email' => strtolower(preg_replace('/[^a-z]/i', '', explode(' ', $name)[0])) . $i . '@svsdemo.test',
                        'phone' => '9' . $faker->numerify('#########'),
                        'standard' => $faker->randomElement($standards),
                        'division' => $faker->randomElement($divisions),
                        'gender' => $faker->randomElement(['male', 'female', 'other']),
                        'dob' => $faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
                        'city' => $faker->city(),
                        'attendance_percent' => $faker->numberBetween(45, 100),
                        'marks_math' => $math,
                        'marks_science' => $science,
                        'marks_english' => $english,
                        'total_marks' => $total,
                        'percentage' => $percentage,
                        'grade' => $this->grade($percentage),
                        'fees_total' => $feesTotal,
                        'fees_paid' => $feesPaid,
                        'fees_due' => round($feesTotal - $feesPaid, 2),
                        'status' => $faker->randomElement($statuses),
                        'admission_date' => $faker->dateTimeBetween('-6 years', 'now')->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('student_records')->insert($rows);
            }
        }

        $total = DB::table('student_records')->count();
        $this->command->info("Done. student_records now holds {$total} rows.");
    }

    private function grade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }
}
