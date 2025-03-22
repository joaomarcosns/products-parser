<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Set up the test environment before each test.
 */
beforeEach(function () {
    $this->products = Product::factory()->count(15)->create();
    $this->product = Product::factory()->create();
});

describe('Product Feature Tests', function () {
    it('returns a paginated list of products', function () {
        $response = $this->getJson(route('products.index'));
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
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
                    ]
                ],
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ],
                ],
            ],
        ]);

        $response->assertJson(['message' => 'Products list']);
    });

    it('returns a product by its code', function () {
        $response = $this->getJson(route('products.show', ['product' => $this->product->code]));

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data'])
            ->assertJson([
                'message' => 'Product found',
            ])
            ->assertJsonPath('data.id', $this->product->id);
    });

    it('returns 404 when product code does not exist', function () {
        $response = $this->getJson(route('products.show', ['product' => 'NON-EXISTENT-CODE']));
        $response->assertStatus(404);
    });
});
