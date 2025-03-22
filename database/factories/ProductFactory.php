<?php

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('#####'),
            'status' => $this->faker->randomElement(ProductStatusEnum::cases())->value(),
            'url' => $this->faker->url(),
            'creator' => $this->faker->name(),
            'imported_t' => $this->faker->dateTimeThisDecade()->getTimestamp(),
            'created_t' => $this->faker->dateTimeThisDecade()->getTimestamp(),
            'last_modified_t' => $this->faker->dateTimeThisDecade()->getTimestamp(),
            'product_name' => $this->faker->sentence(3),
            'quantity' => $this->faker->randomNumber(2),
            'brands' => $this->faker->company(),
            'categories' => $this->faker->word(),
            'labels' => $this->faker->word(),
            'cities' => $this->faker->city(),
            'purchase_places' => $this->faker->streetAddress(),
            'stores' => $this->faker->company(),
            'ingredients_text' => $this->faker->sentence(10),
            'traces' => $this->faker->optional()->word(),
            'serving_size' => $this->faker->randomNumber(2) . 'g',
            'serving_quantity' => $this->faker->randomNumber(1),
            'nutriscore_score' => $this->faker->numberBetween(0, 100),
            'nutriscore_grade' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'main_category' => $this->faker->word(),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
