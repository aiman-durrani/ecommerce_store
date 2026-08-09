<?php

use Illuminate\Support\Facades\Http;

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
    try {
        $res = Http::get("https://www.sourcesplash.com/api/search", ['q' => $keyword])->json();
        $photos = $res['photos'] ?? [];
        $urls = [];
        foreach (array_slice($photos, 0, 4) as $p) {
            if (isset($p['url'])) {
                $urls[] = $p['url'];
            }
        }
        $results[$cat] = $urls;
    } catch (\Throwable $e) {
        $results[$cat] = [];
    }
}

file_put_contents(__DIR__ . '/images_output.json', json_encode($results, JSON_PRETTY_PRINT));
echo "DONE! Count: " . count($results) . "\n";
