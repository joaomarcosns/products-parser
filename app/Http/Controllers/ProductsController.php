<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductsRequest;
use App\Models\Product;

class ProductsController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {

        $products = Product::paginate();

        return response()->json([
            'message' => 'Products list',
            'data' => $products
        ]);
    }

    /** Display the specified resource. */
    public function show(Product $product) {

        return response()->json([
            'message' => 'Product found',
            'data' => $product
        ]);
    }

    /** Update the specified resource in storage. */
    public function update(UpdateProductsRequest $request, Product $product) {}

    /** Remove the specified resource from storage. */
    public function destroy(Product $product) {}
}
