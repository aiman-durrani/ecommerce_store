<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchSourceSplashImages extends Command
{
    protected $signature = 'images:fetch';
    protected $description = 'Fetch fixed category images from SourceSplash';

    public function handle()
    {
        $categories = [
            'Electronics' => 'electronics gadget',
            'Computers & Accessories' => 'computer keyboard',
            'Audio & Headphones' => 'headphones',
            'Footwear & Sneakers' => 'sneakers shoes',
            "Men's Apparel" => 'mens fashion clothing',
            "Women's Fashion" => 'womens fashion clothing',
            'Home & Kitchen' => 'kitchen appliance',
            'Fitness & Outdoor Gear' => 'fitness equipment',
            'Beauty & Skincare' => 'skincare cosmetics',
            'Books & Stationery' => 'books stationery',
            'Gaming & Consoles' => 'gaming controller',
            'Smart Home Devices' => 'smart home device',
        ];

        $results = [];

        foreach ($categories as $cat => $keyword) {
            $this->info("Fetching for category: {$cat} (keyword: {$keyword})...");
            try {
                $response = Http::withoutVerifying()->get('https://www.sourcesplash.com/api/search', [
                    'q' => $keyword,
                ]);

                if ($response->successful()) {
                    $photos = $response->json('photos') ?? [];
                    $urls = [];
                    foreach (array_slice($photos, 0, 4) as $p) {
                        if (!empty($p['url'])) {
                            $urls[] = $p['url'];
                        }
                    }
                    $results[$cat] = $urls;
                } else {
                    $this->error("Failed response for {$cat}");
                    $results[$cat] = [];
                }
            } catch (\Throwable $e) {
                $this->error("Error for {$cat}: " . $e->getMessage());
                $results[$cat] = [];
            }
        }

        $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(base_path('scratch/category_images.json'), $json);

        $this->info("Successfully saved category images to scratch/category_images.json");
        return 0;
    }
}
