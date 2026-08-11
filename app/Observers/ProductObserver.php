<?php

namespace App\Observers;

use App\Models\Product;
use Laravel\Ai\Embeddings;

class ProductObserver
{
    /**
     * Handle the Product "saving" event.
     */
    public function saving(Product $product): void
    {
        if (! $product->exists || $product->isDirty(['name', 'description'])) {
            $text = trim("{$product->name}. {$product->description}");
            if ($text !== '') {
                $response = Embeddings::for([$text])->generate();
                $product->embedding = $response->embeddings[0];
            }
        }
    }
}
