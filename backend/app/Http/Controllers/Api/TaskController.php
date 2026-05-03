<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
<<<<<<< HEAD
    public function index()
    {
        $tasks = Cache::remember('tasks.index', 60, function () {
            return Task::all();
        });

        return response()->json($tasks, 200);
    }

    public function store(Request $request)
=======
    public function index(): JsonResponse
    {
        $tasks = Cache::remember('tasks.index', 60, function () {
            return Task::query()->latest()->get();
        });

        return response()->json([
            'items' => $tasks,
        ]);
    }

    public function store(Request $request): JsonResponse
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $task = Task::create($validated);

        Cache::forget('tasks.index');

        return response()->json($task, 201);
    }

<<<<<<< HEAD
    public function show($id)
=======
    public function show(Task $task): JsonResponse
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
    {
        return response()->json($task);
    }

<<<<<<< HEAD
    public function update(Request $request, $id)
=======
    public function update(Request $request, Task $task): JsonResponse
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:todo,doing,done'],
            'priority' => ['sometimes', 'in:low,medium,high'],
        ]);

        $task->update($validated);

<<<<<<< HEAD
        Cache::forget('tasks.index'); 

        return response()->json($task, 200);
    }

    public function destroy($id)
=======
        Cache::forget('tasks.index');

        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
    {
        $task->delete();

<<<<<<< HEAD
        Cache::forget('tasks.index'); 
=======
        Cache::forget('tasks.index');
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)

        return response()->json(null, 204);
    }
}
