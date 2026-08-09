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
     * These keys match Category names exactly (see CategoryFactory).
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

    /**
     * Hardcoded real photo URLs from SourceSplash API search for each category keyword.
     */
    public static array $categoryImages = [
        'Electronics' => [
            'https://www.sourcesplash.com/i/92b374c9fa74',
            'https://www.sourcesplash.com/i/66a4c349a6e6',
            'https://www.sourcesplash.com/i/89472ddd35f2',
            'https://www.sourcesplash.com/i/3b384e90e525',
        ],
        'Computers & Accessories' => [
            'https://www.sourcesplash.com/i/c3edf66f27ef',
            'https://www.sourcesplash.com/i/cdb893cfb734',
            'https://www.sourcesplash.com/i/2f389261a6df',
            'https://www.sourcesplash.com/i/26812ac9e293',
        ],
        'Audio & Headphones' => [
            'https://www.sourcesplash.com/i/3e731542d559',
            'https://www.sourcesplash.com/i/649e77dfc5e3',
            'https://www.sourcesplash.com/i/a66528f8f2c9',
            'https://www.sourcesplash.com/i/f33ee361e4a1',
        ],
        'Footwear & Sneakers' => [
            'https://www.sourcesplash.com/i/5211fb1f95fe',
            'https://www.sourcesplash.com/i/20f1063014fc',
            'https://www.sourcesplash.com/i/9183033f039c',
            'https://www.sourcesplash.com/i/2659442569b3',
        ],
        "Men's Apparel" => [
            'https://www.sourcesplash.com/i/116e64404fe0',
            'https://www.sourcesplash.com/i/2d4b425c9851',
            'https://www.sourcesplash.com/i/62e019c3a03b',
            'https://www.sourcesplash.com/i/da02c1497732',
        ],
        "Women's Fashion" => [
            'https://www.sourcesplash.com/i/08a0a0c8d056',
            'https://www.sourcesplash.com/i/26670bc78465',
            'https://www.sourcesplash.com/i/d1334f21193c',
            'https://www.sourcesplash.com/i/55fa775c0337',
        ],
        'Home & Kitchen' => [
            'https://www.sourcesplash.com/i/0d98a71c2c30',
            'https://www.sourcesplash.com/i/b73320c0f665',
            'https://www.sourcesplash.com/i/6914b8ffb552',
            'https://www.sourcesplash.com/i/4cd301a50d57',
        ],
        'Fitness & Outdoor Gear' => [
            'https://www.sourcesplash.com/i/0f58a2c751e9',
            'https://www.sourcesplash.com/i/6ab9f6014ab4',
            'https://www.sourcesplash.com/i/2c8a54e3e700',
            'https://www.sourcesplash.com/i/0b4d417ef6f0',
        ],
        'Beauty & Skincare' => [
            'https://www.sourcesplash.com/i/83fc1a9b21d6',
            'https://www.sourcesplash.com/i/7b84d76c2d1b',
            'https://www.sourcesplash.com/i/a0a1daf241af',
            'https://www.sourcesplash.com/i/6edc6530cb41',
        ],
        'Books & Stationery' => [
            'https://www.sourcesplash.com/i/12c11f68caa5',
            'https://www.sourcesplash.com/i/beb364bfdccd',
            'https://www.sourcesplash.com/i/dc5c9ba0a4b6',
            'https://www.sourcesplash.com/i/ab7f194297d9',
        ],
        'Gaming & Consoles' => [
            'https://www.sourcesplash.com/i/8cd15c9866c2',
            'https://www.sourcesplash.com/i/1340da0357b2',
            'https://www.sourcesplash.com/i/398f78d862df',
            'https://www.sourcesplash.com/i/a4b074b56bda',
        ],
        'Smart Home Devices' => [
            'https://www.sourcesplash.com/i/d05917387d5d',
            'https://www.sourcesplash.com/i/3b2a0932a72c',
            'https://www.sourcesplash.com/i/8c41f13d0d01',
            'https://www.sourcesplash.com/i/ef0f8a119796',
        ],
    ];

    public function definition(): array
    {
        $categoryName = array_rand(self::$productsByCategory);
        $name = fake()->randomElement(self::$productsByCategory[$categoryName]);
        $slug = Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999);
        $images = self::$categoryImages[$categoryName] ?? [];
        $image = !empty($images) ? fake()->randomElement($images) : "https://www.sourcesplash.com/i/92b374c9fa74";

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 9.99, 499.99),
            'stock' => fake()->numberBetween(0, 200),
            'image_path' => $image,
            'is_active' => true,
        ];
    }

    /**
     * Force this product to belong to a specific category,
     * and pick a name & real image that actually matches it.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(function () use ($category) {
            $names = self::$productsByCategory[$category->name] ?? null;
            $name = $names ? fake()->randomElement($names) : fake()->words(3, true);
            $slug = Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999);

            $images = self::$categoryImages[$category->name] ?? [];
            $image = !empty($images) ? fake()->randomElement($images) : "https://www.sourcesplash.com/i/92b374c9fa74";

            return [
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'image_path' => $image,
            ];
        });
    }
}