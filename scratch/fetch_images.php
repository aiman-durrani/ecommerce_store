<?php

require __DIR__ . '/../vendor/autoload.php';

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
    $url = "https://www.sourcesplash.com/api/search?q=" . urlencode($keyword);
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    $urls = [];
    if (isset($data['photos']) && is_array($data['photos'])) {
        foreach (array_slice($data['photos'], 0, 4) as $photo) {
            if (isset($photo['url'])) {
                $urls[] = $photo['url'];
            }
        }
    }
    $results[$cat] = $urls;
}

echo "<?php\n\nreturn " . var_export($results, true) . ";\n";
