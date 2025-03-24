<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Project extends Controller
{
    /**
     * @OA\Get(
     *      path="/",
     *      operationId="getApiStatus",
     *      tags={"Status"},
     *      summary="Verifica o status da API",
     *      description="Retorna informações sobre a conexão com o banco de dados, uptime, uso de memória e última execução do cron job.",
     *      @OA\Response(
     *          response=200,
     *          description="Resposta de sucesso",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Products API"),
     *              @OA\Property(property="status", type="string", example="Connected"),
     *              @OA\Property(property="version", type="string", example="1.0.0"),
     *              @OA\Property(property="db_connection", type="string", example="OK"),
     *              @OA\Property(property="last_cron_run", type="string", example="2025-03-23 14:00:00 | Time Zone: UTC"),
     *              @OA\Property(property="uptime", type="string", example="0.23 min"),
     *              @OA\Property(property="memory_usage", type="string", example="12.45 MB")
     *          )
     *      )
     * )
     */
    public function index()
    {
        $dbConnectionStatus = 'OK';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnectionStatus = 'ERROR: ' . $e->getMessage();
        }

        $lastCronRunTimestamp = Redis::get('last_import_job_timestamp');
        $uptime = sys_getloadavg();
        $timeZone = config('app.timezone');

        $memoryUsage = memory_get_usage(true);
        $memoryUsageFormatted = number_format($memoryUsage / 1024 / 1024, 2) . ' MB';

        return response()->json([
            'message' => 'Products API',
            'status' => 'Connected',
            'version' => config('app.version'),
            'db_connection' => $dbConnectionStatus,
            'last_cron_run' => "{$lastCronRunTimestamp} | Time Zone: {$timeZone}",
            'uptime' => $uptime[0] . ' min',
            'memory_usage' => $memoryUsageFormatted,
        ]);
    }
}
