<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Product names grouped by category name.
     * These keys must match Category names exactly (see CategoryFactory).
     */
    public static array $productsByCategory = [
        'Electronics' => [
            'Ultra-HD 4K Smart Monitor 27"',
            'High-Speed Portable SSD 1TB',
            'Aluminum Multi-Port USB-C Hub Adapter',
            'Smart LED Desk Lamp with Wireless Charging',
        ],
        'Computers & Accessories' => [
            'Ergonomic Mechanical Gaming Keyboard',
            'Wireless Ergonomic Vertical Mouse',
            'Adjustable Laptop Stand with Cooling Fan',
            'Dual Monitor Arm Desk Mount',
        ],
        'Audio & Headphones' => [
            'Wireless Noise-Canceling Headphones',
            'Noise-Isolating True Wireless Earbuds',
            'Portable Bluetooth Speaker Waterproof',
            'Studio Monitor Headphones for Audio Production',
        ],
        'Footwear & Sneakers' => [
            'Lightweight Running Sneakers',
            'Classic Canvas Low-Top Sneakers',
            'Waterproof Hiking Boots',
            'Slip-On Casual Loafers',
        ],
        "Men's Apparel" => [
            'Organic Premium Cotton Hoodie',
            'Classic Fit Denim Jacket',
            'Slim Fit Chino Trousers',
            'Merino Wool Crew Neck Sweater',
        ],
        "Women's Fashion" => [
            'Lightweight Packable Rain Jacket',
            'High-Waisted Stretch Leggings',
            'Floral Print Wrap Dress',
            'Oversized Knit Cardigan',
        ],
        'Home & Kitchen' => [
            'Compact Automatic Espresso Machine',
            'Stainless Steel 12-Piece Cookware Set',
            'Digital Air Fryer 6-Quart',
            'Electric Kettle with Temperature Control',
        ],
        'Fitness & Outdoor Gear' => [
            'Adjustable Dumbbell Set 5-25kg',
            'Non-Slip Yoga Mat with Carry Strap',
            'Resistance Bands Set (5 Levels)',
            'Insulated Sports Water Bottle 32oz',
        ],
        'Beauty & Skincare' => [
            'Vitamin C Brightening Facial Serum',
            'Hydrating Aloe Vera Face Moisturizer',
            'Natural Bristle Facial Cleansing Brush',
            'Matte Finish Long-Wear Lipstick Set',
        ],
        'Books & Stationery' => [
            'The Art of Clean Code',
            'Atomic Habits: A Practical Guide',
            'Premium Leather-Bound Journal',
            'Deep Work: Rules for Focused Success',
        ],
        'Gaming & Consoles' => [
            'Wireless Pro Gaming Controller',
            'RGB Gaming Mouse Pad Extended',
            'Gaming Headset with Surround Sound',
            'Console Storage Stand with Cooling',
        ],
        'Smart Home Devices' => [
            'Smart Fitness & Health Tracker Watch',
            'Smart WiFi Video Doorbell',
            'Smart Plug with Voice Control (4-Pack)',
            'Smart Thermostat with App Control',
        ],
    ];

    public function definition(): array
    {
        // Fallback random pick if no category context is given
        $categoryName = array_rand(self::$productsByCategory);
        $name = fake()->randomElement(self::$productsByCategory[$categoryName]);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 9.99, 499.99),
            'stock' => fake()->numberBetween(0, 200),
            'image_path' => null,
            'is_active' => true,
        ];
    }

    /**
     * Force this product to belong to a specific category,
     * and pick a name that actually matches it.
     */
    public function forCategory(Category $category): static
{
    return $this->state(function () use ($category) {
        $names = self::$productsByCategory[$category->name] ?? null;
        $name = $names ? fake()->randomElement($names) : fake()->words(3, true);

        return [
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
        ];
    });
}

}