<?php

namespace Database\Seeders;

<<<<<<< HEAD
=======
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
<<<<<<< HEAD
=======
    use WithoutModelEvents;

>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
    public function run(): void
    {
        $this->call([
            TaskSeeder::class,
        ]);
    }
}