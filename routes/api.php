<?php

declare(strict_types=1);

use App\Http\Controllers\ProductsController;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/', function () {
        $dbConnectionStatus = 'OK';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnectionStatus = 'ERROR: ' . $e->getMessage();
        }

        $lastCronRun = Product::max('imported_t');
        $lastCronRunTimestamp = Carbon::parse($lastCronRun);
        $uptime = sys_getloadavg();
        $timeZone = config('app.timezone');

        $memoryUsage = memory_get_usage(true);
        $memoryUsageFormatted = number_format($memoryUsage / 1024 / 1024, 2) . ' MB';

        return response()->json([
            'message' => 'Products API',
            'status' => 'Connected',
            'version' => config('app.version'),
            'db_connection' => $dbConnectionStatus,
            'last_cron_run' => "{$lastCronRunTimestamp->toDateTimeString()} | Time Zone: {$timeZone}",
            'uptime' => $uptime[0] . ' min',
            'memory_usage' => $memoryUsageFormatted,
        ]);
    });

    Route::apiResource('products', ProductsController::class)->except('store');
});
