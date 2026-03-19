<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EchoController;

Route::get('/echo', [EchoController::class, 'echo']);