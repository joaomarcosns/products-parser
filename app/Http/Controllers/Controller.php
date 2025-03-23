<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API de Produtos",
 *      description="Documentação da API para gerenciamento de produtos",
 *      @OA\Contact(
 *          email="suporte@example.com"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor API"
 * )
 */
abstract class Controller {}
