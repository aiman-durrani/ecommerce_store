<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * List of 12 predefined category names.
     */
    public static array $categories = [
        'Electronics',
        'Computers & Accessories',
        'Audio & Headphones',
        'Footwear & Sneakers',
        "Men's Apparel",
        "Women's Fashion",
        'Home & Kitchen',
        'Fitness & Outdoor Gear',
        'Beauty & Skincare',
        'Books & Stationery',
        'Gaming & Consoles',
        'Smart Home Devices',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::$categories);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}