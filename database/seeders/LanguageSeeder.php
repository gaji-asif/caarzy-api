<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            "language.Finnish",
            "language.Swedish", 
            "language.English", 
            "language.Germany", 
            "language.France",
            "language.Spain", 
            "language.Italy", 
            "language.Russia", 
            "language.Estonia", 
            "language.China",
            "language.Japan", 
            "language.Arabic", 
            "language.Somalia", 
            "language.Kurd", 
            "language.Persia"
        ];

        foreach ($options as $opt) {
            Language::firstOrCreate(['name' => $opt]);
        }
    }
}
