<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;

#[Signature('app:backfill-embeddings')]
#[Description('Generate and backfill missing AI embeddings for products')]
class BackfillEmbeddings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $products = Product::whereNull('embedding')->get();
        $total = $products->count();

        if ($total === 0) {
            $this->info('All products already have embeddings. Nothing to backfill.');
            return 0;
        }

        $this->info("Found {$total} product(s) missing embeddings. Starting backfill...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $product) {
            $text = trim("{$product->name}. {$product->description}");
            if ($text !== '') {
                $response = Embeddings::for([$text])->generate();
                $product->embedding = $response->embeddings[0];
                $product->saveQuietly();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully backfilled embeddings for {$total} product(s).");

        return 0;
    }
}
