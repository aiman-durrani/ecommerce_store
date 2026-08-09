<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->relationLoaded('cartItems') ? $this->cartItems : collect();
        $total = $items->sum(function ($item) {
            $price = $item->product ? (float) $item->product->price : 0;
            return $price * $item->quantity;
        });

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'total' => round($total, 2),
        ];
    }
}
