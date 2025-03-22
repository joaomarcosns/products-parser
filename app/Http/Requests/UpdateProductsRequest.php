<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductsRequest extends FormRequest
{
    /** Determine if the user is authorized to make this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:' . implode(',', array_column(ProductStatusEnum::cases(), 'value'))],
            'url' => ['nullable', 'string', 'url', 'max:255'],
            'creator' => ['nullable', 'string', 'min:3', 'max:255'],
            'product_name' => ['nullable', 'string', 'min:3', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'brands' => ['nullable', 'string', 'min:3', 'max:255'],
            'categories' => ['nullable', 'string', 'min:3', 'max:255'],
            'labels' => ['nullable', 'string', 'min:3', 'max:255'],
            'cities' => ['nullable', 'string', 'min:3', 'max:255'],
            'purchase_places' => ['nullable', 'string', 'min:3', 'max:255'],
            'stores' => ['nullable', 'string', 'min:3', 'max:255'],
            'ingredients_text' => ['nullable', 'max:300', 'string'],
            'traces' => ['nullable', 'string', 'min:3', 'max:255'],
            'serving_size' => ['nullable', 'string', 'min:3', 'max:255'],
            'serving_quantity' => ['nullable', 'integer', 'min:1'],
            'nutriscore_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'nutriscore_grade' => ['nullable', 'string', 'max:255'],
            'main_category' => ['nullable', 'string', 'min:3', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
