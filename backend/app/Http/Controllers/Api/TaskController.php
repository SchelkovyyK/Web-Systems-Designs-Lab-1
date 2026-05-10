<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Support\ApiError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
{
    $tasks = Cache::store('redis')->remember('tasks.index', 60, function () {
        return Task::query()->latest()->get();
    });

    return response()->json(['items' => $tasks], 200);
}


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'album_number' => 'required|integer',
            'priority' => 'required|string'
        ]);

        if ($validator->fails()) {
            return ApiError::make($request, 422, "Validation failed: " . $validator->errors()->first());
        }

        $task = Task::create($validator->validated());

        Cache::forget('tasks.index');

        return response()->json($task, 201);
    }

    public function show(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            return response()->json($task, 200);
        } catch (ModelNotFoundException $e) {
            return ApiError::make($request, 404, "Task with ID $id not found");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'album_number' => 'required|integer',
                'priority' => 'sometimes|required|string'
            ]);

            if ($validator->fails()) {
                return ApiError::make($request, 422, "Validation failed: " . $validator->errors()->first());
            }

            $task->update($validator->validated());

            Cache::forget('tasks.index');

            return response()->json($task, 200);
        } catch (ModelNotFoundException $e) {
            return ApiError::make($request, 404, "Task with ID $id not found");
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete();

            Cache::forget('tasks.index');

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return ApiError::make($request, 404, "Task with ID $id not found");
        }
    }
}
