<?php

declare(strict_types=1);

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Project;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/', [Project::class, 'index']);

    Route::apiResource('products', ProductsController::class)->except('store');
});
