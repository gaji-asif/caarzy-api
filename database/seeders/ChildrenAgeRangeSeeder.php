<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChildrenAgeRange;
use Illuminate\Support\Facades\DB;

class ChildrenAgeRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear table and reset auto-increment
        DB::table('children_age_ranges')->truncate();

        $options = [];
        $start = new \DateTime('2020-01-01');
        $end = new \DateTime(); // today
        $end->modify('first day of this month'); // start from current month

        // Generate month-year options descending
        while ($end >= $start) {
            $options[] = "language." . $end->format("m.Y");
            $end->modify('-1 month');
        }

        // Add static year-only values at the end
        $options[] = 'language.2019';
        $options[] = 'language.2018';
        $options[] = 'language.2017';

        // Insert into database
        foreach ($options as $opt) {
            ChildrenAgeRange::firstOrCreate(['name' => $opt]);
        }
    }


}
