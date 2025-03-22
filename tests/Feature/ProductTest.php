<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductStatusEnum;
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

    it('fails to update a product with invalid data', function () {
        $invalidData = [
            'status' => 'invalid-status',
            'url' => 'invalid-url',
            'quantity' => 'not-a-number',
            'ingredients_text' => str_repeat('A', 301),
            'image_url' => 'invalid-url',
        ];

        $response = $this->patchJson(route('products.update', $this->product->code), $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
                'url',
                'quantity',
                'ingredients_text',
                'image_url',
            ]);
    });

    it('returns validation errors for invalid data on product update', function () {
        $invalidData = [
            'status' => 'invalid_status',
            'url' => 'not_a_valid_url',
            'creator' => 'AB',
            'product_name' => 'A',
            'quantity' => 0,
            'brands' => 'A',
            'categories' => 'A',
            'labels' => 'A',
            'cities' => 'AB',
            'purchase_places' => 'A',
            'stores' => 'A',
            'ingredients_text' => str_repeat('A', 301),
            'traces' => 'A',
            'serving_size' => 'A',
            'serving_quantity' => 0,
            'nutriscore_score' => 101,
            'nutriscore_grade' => str_repeat('A', 256),
            'main_category' => 'A',
            'image_url' => 'not_a_valid_url',
        ];

        $response = $this->putJson(route('products.update', ['product' => $this->product->code]), $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
                'url',
                'creator',
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
            ]);
    });

    it('updates a product with valid data', function () {
        $validData = [
            'status' => ProductStatusEnum::PUBLISHED->value(),
            'url' => 'https://example.com/product',
            'creator' => 'John Doe',
            'product_name' => 'Updated Product',
            'quantity' => 10,
            'brands' => 'Brand Name',
            'categories' => 'Food, Snacks',
            'labels' => 'Organic, Vegan',
            'cities' => 'New York, Los Angeles',
            'purchase_places' => 'Supermarket, Online',
            'stores' => 'Walmart, Target',
            'ingredients_text' => 'Sugar, Milk, Cocoa',
            'traces' => 'Peanuts, Gluten',
            'serving_size' => '100g',
            'serving_quantity' => '100',
            'nutriscore_score' => '80',
            'nutriscore_grade' => 'A',
            'main_category' => 'Snacks',
            'image_url' => 'https://example.com/image.jpg',
        ];

        $response = $this->patchJson(route('products.update', $this->product->code), $validData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated',
                'data' => $validData,
            ]);
        $this->assertDatabaseHas('products', array_merge(['id' => $this->product->id], $validData));
    });

    it('successfully changes the product status to trash without deleting the product', function () {
        $response = $this->deleteJson(route('products.destroy', ['product' => $this->product->code]));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product moved to trash',
                'data' => [
                    'id' => $this->product->id,
                    'status' => ProductStatusEnum::TRASH->value,
                ]
            ]);

        $this->product->refresh();

        $this->assertEquals(ProductStatusEnum::TRASH->value, $this->product->status->value);
    });
});
