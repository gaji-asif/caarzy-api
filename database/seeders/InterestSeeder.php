<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserInterest;
use Illuminate\Support\Facades\DB;


class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            "interest.outdoors",
            "interest.coffee",
            "interest.babySwimming",
            "interest.sports",
            "interest.culture",
            "interest.fleaMarket",
            "interest.hiking",
            "interest.reading",
            "interest.music",
            "interest.cooking",
            "interest.crafts",
            "interest.photography",
            "interest.outdoors",
            "interest.coffee",
            "interest.dance",
            "interest.yoga",
            "interest.cycling",
            "interest.fathers",
            "interest.mothers",
            "interest.multilingual",
            "interest.familyDays",
            "interest.dining"

        ];
        DB::table('user_interests')->truncate();

        foreach ($options as $opt) {
            UserInterest::firstOrCreate(['name' => $opt]);
        }
    }
}

