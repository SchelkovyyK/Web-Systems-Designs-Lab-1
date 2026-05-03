<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        Task::query()->delete();
<<<<<<< HEAD
=======

>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
        Task::insert([
            [
                'title' => 'Prepare cache design note',
                'description' => 'Document what is cached and why',
                'status' => 'todo',
                'priority' => 'high',
<<<<<<< HEAD
                'created_at' => now(), 'updated_at' => now(),
=======
                'album_number' => 78716,
                'created_at' => now(),
                'updated_at' => now(),
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
            ],
            [
                'title' => 'Benchmark repeated GET requests',
                'description' => 'Compare cold and warm reads',
                'status' => 'doing',
                'priority' => 'medium',
<<<<<<< HEAD
                'created_at' => now(), 'updated_at' => now(),
=======
                'album_number' => 78716,
                'created_at' => now(),
                'updated_at' => now(),
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
            ],
            [
                'title' => 'Add deployment diagram',
                'description' => 'Show Nginx, Laravel, Redis, and PostgreSQL',
                'status' => 'done',
                'priority' => 'low',
<<<<<<< HEAD
                'created_at' => now(), 'updated_at' => now(),
=======
                'album_number' => 78716,
                'created_at' => now(),
                'updated_at' => now(),
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
            ],
        ]);
    }
}
