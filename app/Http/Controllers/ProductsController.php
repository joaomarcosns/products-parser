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

    /** Store a newly created resource in storage. */
    public function store(StoreProductsRequest $request) {}

    /** Display the specified resource. */
    public function show(Product $products) {}

    /** Update the specified resource in storage. */
    public function update(UpdateProductsRequest $request, Product $products) {}

    /** Remove the specified resource from storage. */
    public function destroy(Product $products) {}
}
