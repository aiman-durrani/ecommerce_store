<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Ai\Embeddings;

class ProductSearchService
{
    /**
     * Perform semantic search on products using cosine similarity.
     */
    public function search(string $query, int $limit = 12): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return new Collection();
        }

        // Generate embedding for query text
        $response = Embeddings::for([$query])->generate();
        $queryEmbedding = $response->embeddings[0];

        // Fetch products that have non-null embedding
        $products = Product::whereNotNull('embedding')
            ->where('is_active', true)
            ->with('category')
            ->get();

        // Calculate cosine similarity for each product
        $scored = $products->map(function (Product $product) use ($queryEmbedding) {
            $product->similarity_score = $this->cosineSimilarity($queryEmbedding, $product->embedding ?? []);
            return $product;
        });

        // Sort products by similarity score descending and take top limit
        $topProducts = $scored->sortByDesc('similarity_score')
            ->take($limit)
            ->values();

        return new Collection($topProducts->all());
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = min(count($a), count($b));
        for ($i = 0; $i < $count; $i++) {
            $valA = (float) $a[$i];
            $valB = (float) $b[$i];

            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
