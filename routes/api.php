<?php

declare(strict_types=1);

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Project;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/', [Project::class, 'index']);

    Route::apiResource('products', ProductsController::class)->except('store');
});
