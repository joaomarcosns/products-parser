<?php

use App\Http\Controllers\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/', function () {
        return response()->json([
            'message' => 'Products API',
            'status' => 'Connected',
            'version' => config('app.version')
        ]);
    });


    Route::apiResource('products', ProductsController::class)->except('store');
});
