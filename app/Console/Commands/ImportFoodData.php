<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ProductStatusEnum;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ErrorReportNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use Exception;

class ImportFoodData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'food:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa dados do Open Food Facts diariamente';

    /** Execute the console command. */
    public function handle()
    {
        $this->info('🔄 Iniciando importação de produtos...');

        // Erros
        $errorLogPath = storage_path('app/public/error_log.json');
        $errors = [];

        try {
            // Cria a tabela temporária
            $tempTable = $this->tableTemp();

            // URL base para os arquivos
            $baseUrl = 'https://challenges.coode.sh/food/data/json/';

            // Collection
            $productsCollect = collect();

            // Obtém a lista de arquivos do index.txt
            $indexResponse = Http::get($baseUrl . 'index.txt');

            throw new Exception('Falha ao obter a lista de arquivos!');
            if (! $indexResponse->successful()) {
                $this->error('❌ Falha ao obter a lista de arquivos!');
                throw new Exception('Falha ao obter a lista de arquivos!');
            }

            $files = explode(PHP_EOL, trim($indexResponse->body()));

            // Baixa e processa os arquivos
            $this->downloadAndProcessFiles($files, $baseUrl, $productsCollect, $errors);

            // Cria o arquivo CSV
            $this->info('📝 Criando arquivo CSV...');
            $this->generateCsv($productsCollect, $tempTable);

            $this->info('🔄 Sincronizando dados...');
            $this->sync($tempTable);
            $this->info('✅ Importação concluída!');
        } catch (Exception $e) {
            $this->error('❌ Ocorreu um erro na importação!');
            $errors[] = $e->getMessage();
            $this->logErrors($errors, $errorLogPath);
        }
    }

    private function tableTemp()
    {
        $table = 'products_temp';
        Schema::dropIfExists($table);
        Schema::create($table, function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('status')->nullable();
            $table->integer('imported_t')->default(Carbon::now()->timestamp);
            $table->text('url')->nullable();
            $table->string('creator')->nullable();
            $table->string('created_t')->nullable();
            $table->string('last_modified_t')->nullable();
            $table->string('product_name')->nullable();
            $table->string('quantity')->nullable();
            $table->string('brands')->nullable();
            $table->text('categories')->nullable();
            $table->string('labels')->nullable();
            $table->string('cities')->nullable();
            $table->string('purchase_places')->nullable();
            $table->string('stores')->nullable();
            $table->text('ingredients_text')->nullable();
            $table->string('traces')->nullable();
            $table->string('serving_size')->nullable();
            $table->string('serving_quantity')->nullable();
            $table->string('nutriscore_score')->nullable();
            $table->string('nutriscore_grade')->nullable();
            $table->string('main_category')->nullable();
            $table->text('image_url')->nullable();
        });

        return $table;
    }

    private function downloadAndProcessFiles($files, $baseUrl, $productsCollect, $errors)
    {
        foreach ($files as $file) {
            $this->info("📥 Baixando {$file}...");

            // Baixa o arquivo .gz
            $fileResponse = Http::get($baseUrl . $file);

            if (! $fileResponse->successful()) {
                $this->error("❌ Falha ao baixar {$file}");
                $errors[] = "Falha ao baixar {$file}";
                continue;
            }

            // Salva o arquivo .gz no diretório 'temp' dentro de 'storage/app'
            $gzPath = storage_path('app/public/temp/' . $file);
            Storage::disk('public')->put('temp/' . $file, $fileResponse->body());

            // Descompacta o arquivo .gz
            $jsonPath = str_replace('.gz', '', $gzPath);
            $this->gunzipFile($gzPath, $jsonPath);

            $this->processJsonFile($productsCollect, $jsonPath, $file);

            // Remove os arquivos temporários
            Storage::disk('public')->delete([
                "temp/{$file}",
                'temp/' . basename($jsonPath),
            ]);
        }
    }

    private function gunzipFile($gzFile, $outFile)
    {
        $bufferSize = 4096;
        $file = gzopen($gzFile, 'rb');
        $out = fopen($outFile, 'wb');

        while (! gzeof($file)) {
            fwrite($out, gzread($file, $bufferSize));
        }

        gzclose($file);
        fclose($out);
    }

    private function processJsonFile($productsCollect, $jsonPath, $fileName)
    {
        $file = fopen($jsonPath, 'r');
        if (! $file) {
            $this->error("❌ Não foi possível abrir o arquivo {$fileName}.");
            throw new Exception('Não foi possível abrir o arquivo {$fileName}.');
        }

        $lineCount = 0;

        while (($line = fgets($file)) !== false && $lineCount < 100) {
            $data = json_decode($line, true);
            $productsCollect->push([
                'code' => trim($data['code'], '"'),
                'status' => ProductStatusEnum::DRAFT->value,
                'url' => $this->nullable($data['url']),
                'creator' => $this->nullable($data['creator']),
                'created_t' => $this->nullable($data['created_t']),
                'last_modified_t' => $this->nullable($data['last_modified_t']),
                'product_name' => $this->nullable($data['product_name']),
                'quantity' => $this->nullable($data['quantity']),
                'brands' => $this->nullable($data['brands']),
                'categories' => $this->nullable($data['categories']),
                'labels' => $this->nullable($data['labels']),
                'cities' => $this->nullable($data['cities']),
                'purchase_places' => $this->nullable($data['purchase_places']),
                'stores' => $this->nullable($data['stores']),
                'ingredients_text' => $this->nullable($data['ingredients_text']),
                'traces' => $this->nullable($data['traces']),
                'serving_size' => $this->nullable($data['serving_size']),
                'serving_quantity' => $this->nullable($data['serving_quantity']),
                'nutriscore_score' => $this->nullable($data['nutriscore_score']),
                'nutriscore_grade' => $this->nullable($data['nutriscore_grade']),
                'main_category' => $this->nullable($data['main_category']),
                'image_url' => $this->nullable($data['image_url']),

            ]);
            $lineCount++;
        }

        fclose($file);

        $this->info("✅ {$fileName} importado com sucesso!");
    }

    private function generateCsv($productsCollect, $tempTable)
    {
        $header = [
            'code',
            'status',
            'url',
            'creator',
            'created_t',
            'last_modified_t',
            'product_name',
            'quantity',
            'brands',
            'categories',
            'labels',
            'cities',
            'purchase_places',
            'stores',
            'ingredients_text',
            'traces',
            'serving_size',
            'serving_quantity',
            'nutriscore_score',
            'nutriscore_grade',
            'main_category',
            'image_url',
        ];

        $this->copy(
            csv: $this->createFile($header, $productsCollect->toArray(), $tempTable),
            output: $this->output,
            tempTable: $tempTable,
            name: $tempTable
        );

        $this->info('✅ Dados copiados com sucesso!');
    }

    private static function createFile($header, array $dataModel, $name)
    {
        Storage::disk('public')->put("$name.csv", '');
        $csv = Writer::createFromPath(storage_path("app/public/$name.csv"), 'w+');
        $csv->setDelimiter(';');
        $csv->insertOne($header);
        $csv->insertAll($dataModel);
    }

    private function copy($csv, $output, $tempTable, $name)
    {
        $begin = now();
        $file = storage_path("app/public/$name.csv");
        $file_temp = storage_path("app/public/{$name}_temp.csv");
        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $header = implode(',', $csv->getHeader());
        shell_exec("iconv -c -t utf8 $file > /$file_temp");
        shell_exec("rm $file");
        shell_exec("mv $file_temp $file");
        //pega do rotina
        $connectionString = sprintf(
            '-U %s -h %s -d %s -p %s -c',
            env('DB_USERNAME') ?? config('database.connections.pgsql.username'),
            env('DB_HOST') ?? config('database.connections.pgsql.host'),
            env('DB_DATABASE') ?? config('database.connections.pgsql.database'),
            env('DB_PORT') ?? config('database.connections.pgsql.port'),
        );

        $execString = sprintf(
            'PGPASSWORD=%s psql %s "\COPY \\"%s\\"(%s) FROM \'%s\' DELIMITER \';\' CSV HEADER"',
            env('DB_PASSWORD') ?? config('database.connections.pgsql.password'),
            $connectionString,
            $tempTable,
            $header,
            $file
        );

        shell_exec($execString);

        $executionTime = Carbon::now()->diffInMinutes($begin);
        $output->info(sprintf('FIM DO COPY. TEMPO DE EXECUCAO: %d minutos', $executionTime));
    }

    private function nullable($value)
    {
        return ! empty($value) ? $value : null;
    }

    private function sync($tableName)
    {
        $sql =
            "WITH prod_temp AS (
            SELECT DISTINCT ON (pt.code)
                pt.code,
                pt.status,
                pt.imported_t,
                pt.url,
                pt.creator,
                pt.created_t,
                pt.last_modified_t,
                pt.product_name,
                pt.quantity,
                pt.brands,
                pt.categories,
                pt.labels,
                pt.cities,
                pt.purchase_places,
                pt.stores,
                pt.ingredients_text,
                pt.traces,
                pt.serving_size,
                pt.serving_quantity,
                pt.nutriscore_score,
                pt.nutriscore_grade,
                pt.main_category,
                pt.image_url,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM {$tableName} pt
        )
        INSERT INTO products (
            code,
            status,
            imported_t,
            url,
            creator,
            created_t,
            last_modified_t,
            product_name,
            quantity,
            brands,
            categories,
            labels,
            cities,
            purchase_places,
            stores,
            ingredients_text,
            traces,
            serving_size,
            serving_quantity,
            nutriscore_score,
            nutriscore_grade,
            main_category,
            image_url,
            created_at,
            updated_at
        )
        SELECT
            code,
            status,
            imported_t,
            url,
            creator,
            created_t,
            last_modified_t,
            product_name,
            quantity,
            brands,
            categories,
            labels,
            cities,
            purchase_places,
            stores,
            ingredients_text,
            traces,
            serving_size,
            serving_quantity,
            nutriscore_score,
            nutriscore_grade,
            main_category,
            image_url,
            created_at,
            updated_at
        FROM prod_temp
        ON CONFLICT (code)
        DO UPDATE SET
            imported_t = EXCLUDED.imported_t,
            status = EXCLUDED.status,
            url = EXCLUDED.url,
            creator = EXCLUDED.creator,
            created_t = EXCLUDED.created_t,
            last_modified_t = EXCLUDED.last_modified_t,
            product_name = EXCLUDED.product_name,
            quantity = EXCLUDED.quantity,
            brands = EXCLUDED.brands,
            categories = EXCLUDED.categories,
            labels = EXCLUDED.labels,
            cities = EXCLUDED.cities,
            purchase_places = EXCLUDED.purchase_places,
            stores = EXCLUDED.stores,
            ingredients_text = EXCLUDED.ingredients_text,
            traces = EXCLUDED.traces,
            serving_size = EXCLUDED.serving_size,
            serving_quantity = EXCLUDED.serving_quantity,
            nutriscore_score = EXCLUDED.nutriscore_score,
            nutriscore_grade = EXCLUDED.nutriscore_grade,
            main_category = EXCLUDED.main_category,
            image_url = EXCLUDED.image_url,
            updated_at = EXCLUDED.updated_at;
    ";

        DB::statement($sql);
    }

    private function logErrors(array $errors, string $path)
    {
        $logData = ['errors' => $errors]; // Sempre sobrescreve os erros antigos

        file_put_contents($path, json_encode($logData, JSON_PRETTY_PRINT));

        // Enviar e-mail com o arquivo anexado
        Notification::route('mail', 'seuemail@example.com')->notify(new ErrorReportNotification($path));
    }
}
