<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductStatusEnum;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductsRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Lista todos os produtos paginados",
     *      description="Retorna a lista de produtos com paginação",
     *      @OA\Response(
     *          response=200,
     *          description="Lista de produtos retornada com sucesso",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Products list"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(ref="#/components/schemas/Product")
     *                  ),
     *                  @OA\Property(property="per_page", type="integer", example=15),
     *                  @OA\Property(property="total", type="integer", example=100)
     *              )
     *          )
     *      )
     * )
     */
    public function index(): JsonResponse
    {

        $products = Product::paginate();

        return response()->json([
            'message' => 'Products list',
            'data' => $products
        ]);
    }

    /**
     * @OA\Get(
     *      path="/products/{product}",
     *      operationId="getProductByCode",
     *      tags={"Products"},
     *      summary="Exibe um produto específico",
     *      description="Retorna os detalhes de um produto com base no CÓDIGO fornecido",
     *      @OA\Parameter(
     *          name="product",
     *          in="path",
     *          required=true,
     *          description="CÓDIGO do produto",
     *          @OA\Schema(
     *              type="integer",
     *              example=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Produto encontrado",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Product found"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Product"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Produto não encontrado",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Product not found")
     *          )
     *      )
     * )
     */
    public function show(Product $product): JsonResponse
    {

        return response()->json([
            'message' => 'Product found',
            'data' => $product
        ]);
    }

    /**
     * @OA\Put(
     *      path="/products/{product}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Atualiza um produto",
     *      description="Atualiza as informações de um produto existente com base no CÓDIGO fornecido",
     *      @OA\Parameter(
     *          name="product",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"status", "product_name"},
     *              @OA\Property(property="status", type="string", example="published", enum={"draft", "trash", "published"}),
     *              @OA\Property(property="url", type="string", format="url", example="https://example.com/product"),
     *              @OA\Property(property="creator", type="string", example="John Doe"),
     *              @OA\Property(property="product_name", type="string", example="Chocolate Bar"),
     *              @OA\Property(property="quantity", type="string", example="200g"),
     *              @OA\Property(property="brands", type="string", example="Nestle"),
     *              @OA\Property(property="categories", type="string", example="Snacks, Chocolates"),
     *              @OA\Property(property="labels", type="string", example="Gluten Free, Organic"),
     *              @OA\Property(property="cities", type="string", example="São Paulo, Rio de Janeiro"),
     *              @OA\Property(property="purchase_places", type="string", example="Supermarket"),
     *              @OA\Property(property="stores", type="string", example="Carrefour, Walmart"),
     *              @OA\Property(property="ingredients_text", type="string", example="Cocoa, Sugar, Milk"),
     *              @OA\Property(property="traces", type="string", example="Nuts, Soy"),
     *              @OA\Property(property="serving_size", type="string", example="50g"),
     *              @OA\Property(property="serving_quantity", type="number", format="float", example=50.0),
     *              @OA\Property(property="nutriscore_score", type="integer", example=5),
     *              @OA\Property(property="nutriscore_grade", type="string", example="B"),
     *              @OA\Property(property="main_category", type="string", example="Snacks"),
     *              @OA\Property(property="image_url", type="string", format="url", example="https://example.com/image.jpg")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Produto atualizado com sucesso",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Product updated"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Product"
     *              )
     *          )
     *      )
     * )
     */
    public function update(UpdateProductsRequest $request, Product $product): JsonResponse
    {

        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated',
            'data' => $product
        ]);
    }

    /**
     * @OA\Delete(
     *      path="/products/{product}",
     *      operationId="destroyProduct",
     *      tags={"Products"},
     *      summary="Move um produto para a lixeira",
     *      description="Move o produto especificado para a lixeira, alterando seu status para 'trash', com base no CÓDIGO fornecido",
     *      @OA\Parameter(
     *          name="product",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Produto movido para a lixeira com sucesso",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Product moved to trash"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Product"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Produto não encontrado",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Product not found")
     *          )
     *      )
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->update(['status' => ProductStatusEnum::TRASH->value]);

        return response()->json([
            'message' => 'Product moved to trash',
            'data' => $product
        ]);
    }
}
