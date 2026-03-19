<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EchoController extends Controller
{
    public function echo(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $request->query('message', 'Hello from EchoController'),
        ]);
    }
}