<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando importação de produtos...');

        // Cria a tabela temporária
        $tempTable = $this->tableTemp();

        // URL base para os arquivos
        $baseUrl = "https://challenges.coode.sh/food/data/json/";

        // Collection
        $productsCollect = collect();

        // Obtém a lista de arquivos do index.txt
        $indexResponse = Http::get($baseUrl . "index.txt");

        if (!$indexResponse->successful()) {
            $this->error('❌ Falha ao obter a lista de arquivos!');
            return;
        }

        $files = explode(PHP_EOL, trim($indexResponse->body()));

        foreach ($files as $file) {
            $this->info("📥 Baixando {$file}...");

            // Baixa o arquivo .gz
            $fileResponse = Http::get($baseUrl . $file);
            if (!$fileResponse->successful()) {
                $this->error("❌ Falha ao baixar {$file}");
                continue;
            }

            // Salva o arquivo .gz no diretório 'temp' dentro de 'storage/app'
            $gzPath = storage_path('app/public/temp/' . $file);
            Storage::disk('public')->put('temp/' . $file, $fileResponse->body());

            // // Descompacta o arquivo .gz
            $jsonPath = str_replace('.gz', '', $gzPath);
            $this->gunzipFile($gzPath, $jsonPath);

            // // Processa os dados JSON em lote
            $this->processJsonFile($productsCollect, $jsonPath, $file);

            // // Remove os arquivos temporários
            Storage::disk('public')->delete([
                "temp/{$file}",
                // "temp/" . basename($jsonPath)
            ]);
        }

        $this->info("✅ Importação concluída!");
    }

    private function gunzipFile($gzFile, $outFile)
    {
        $bufferSize = 4096;
        $file = gzopen($gzFile, 'rb');
        $out = fopen($outFile, 'wb');

        while (!gzeof($file)) {
            fwrite($out, gzread($file, $bufferSize));
        }

        gzclose($file);
        fclose($out);
    }

    private function processJsonFile($productsCollect, $jsonPath, $fileName)
    {
        // Abre o arquivo JSON
        // dd($jsonPath);
        $file = fopen('/var/www/storage/app/public/temp/products_01.json', 'r');
        // $file =  fopen("temp/products.json", 'r');
        // Verifica se o arquivo foi aberto com sucesso
        if (!$file) {
            $this->error("❌ Não foi possível abrir o arquivo {$fileName}.");
            return;
        }

        // Lê todo o conteúdo do arquivo
        $jsonContentCollection = collect();
        $lineCount = 0;
        while (($line = fgets($file)) !== false && $lineCount < 100) {
            // $jsonContent .= $line;  // Concatenar cada linha
            $data = json_decode($line, true);
            dd($data);
            $lineCount++;  // Contar as linhas
        }

        fclose($file);
        // Converte o conteúdo JSON em array
        $products = [];

        dd($products);

        if (!$products) {
            $this->error("❌ Falha ao processar {$fileName}");
            return;
        }

        // Limita a 100 produtos
        $limitedProducts = array_slice($products, 0, 100);

        dd($limitedProducts);

        // Adiciona os produtos ao collection
        foreach ($limitedProducts as $product) {
            dd($product);  // Exibe cada produto no dd (para depuração)
            $productsCollect->push($product);
        }

        $this->info("✅ {$fileName} importado com sucesso!");
    }


    private function tableTemp()
    {
        Schema::dropIfExists('temp');
        Schema::create('temp', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('status');
            $table->timestamp('imported_at')->default(now());
            $table->string('url');
            $table->string('creator');
            $table->timestamp('created_at');
            $table->timestamp('last_modified_at');
            $table->string('product_name');
            $table->string('quantity');
            $table->string('brands');
            $table->string('categories');
            $table->string('labels');
            $table->string('cities');
            $table->string('purchase_places');
            $table->string('stores');
            $table->string('ingredients_text');
            $table->string('traces');
            $table->string('serving_size');
            $table->string('serving_quantity');
            $table->integer('nutriscore_score');
            $table->string('nutriscore_grade');
            $table->string('main_category');
            $table->string('image_url');
        });

        return 'temp';
    }

    public static function createFile($header, $networkId, array $dataModel, $name)
    {
        Storage::disk('public')->put($networkId . "/$name.csv", '');
        $csv = Writer::createFromPath(storage_path("app/public/$networkId/$name.csv"), 'w+');
        $csv->setDelimiter(';');
        $csv->insertOne($header);
        $csv->insertAll($dataModel);
    }

    public static function copy($network, $csv, $output, $temp_table, $name)
    {
        $begin     = now();
        $file      = storage_path("app/public/$network->id/$name.csv");
        $file_temp = storage_path("app/public/$network->id/{$name}_temp.csv");
        $csv       = Reader::createFromPath($file, 'r');
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
            env('DB_HOST')     ?? config('database.connections.pgsql.host'),
            env('DB_DATABASE') ?? config('database.connections.pgsql.database'),
            env('DB_PORT')     ?? config('database.connections.pgsql.port'),
        );

        $execString = sprintf(
            'PGPASSWORD=%s psql %s "\COPY \\"%s\\"(%s) FROM \'%s\' DELIMITER \';\' CSV HEADER"',
            env('DB_PASSWORD') ?? config('database.connections.pgsql.password'),
            $connectionString,
            $temp_table,
            $header,
            $file
        );

        shell_exec($execString);

        if ($output) {
            $executionTime = Carbon::now()->diffInMinutes($begin);
            $output->info(sprintf('%s FIM DO COPY. TEMPO DE EXECUCAO: %d minutos', $network->name, $executionTime));
        }
    }
}
