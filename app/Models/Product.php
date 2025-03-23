<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Product",
 *      title="Product",
 *      description="Modelo de produto",
 *      type="object",
 *      required={"code", "product_name", "status"},
 *      @OA\Property(property="code", type="string", example="123456"),
 *      @OA\Property(property="status", type="string", example="draft", enum={"draft", "trash", "published"}),
 *      @OA\Property(property="url", type="string", format="url", example="https://example.com/product"),
 *      @OA\Property(property="creator", type="string", example="John Doe"),
 *      @OA\Property(property="created_t", type="integer", example=1617181723),
 *      @OA\Property(property="last_modified_t", type="integer", example=1617181723),
 *      @OA\Property(property="product_name", type="string", example="Chocolate Bar"),
 *      @OA\Property(property="quantity", type="string", example="200g"),
 *      @OA\Property(property="brands", type="string", example="Nestle"),
 *      @OA\Property(property="categories", type="string", example="Snacks, Chocolates"),
 *      @OA\Property(property="labels", type="string", example="Gluten Free, Organic"),
 *      @OA\Property(property="cities", type="string", example="São Paulo, Rio de Janeiro"),
 *      @OA\Property(property="purchase_places", type="string", example="Supermarket"),
 *      @OA\Property(property="stores", type="string", example="Carrefour, Walmart"),
 *      @OA\Property(property="ingredients_text", type="string", example="Cocoa, Sugar, Milk"),
 *      @OA\Property(property="traces", type="string", example="Nuts, Soy"),
 *      @OA\Property(property="serving_size", type="string", example="50g"),
 *      @OA\Property(property="serving_quantity", type="number", format="float", example=50.0),
 *      @OA\Property(property="nutriscore_score", type="integer", example=5),
 *      @OA\Property(property="nutriscore_grade", type="string", example="B"),
 *      @OA\Property(property="main_category", type="string", example="Snacks"),
 *      @OA\Property(property="image_url", type="string", format="url", example="https://example.com/image.jpg")
 * )
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatusEnum::class,
        ];
    }

    public function getRouteKeyName()
    {
        return 'code';
    }
}
