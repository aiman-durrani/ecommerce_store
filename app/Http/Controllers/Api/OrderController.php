<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class OrderController extends Controller
{
    /**
     * Store a newly created order from user's cart (checkout).
     */
    public function store(CheckoutRequest $request, OrderService $orderService): JsonResponse
    {
        try {
            $order = $orderService->createFromCart($request->user(), $request->validated());

            return response()->json(new OrderResource($order), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display a listing of orders for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('orderItems.product')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order for the authenticated user.
     */
    public function show(Request $request, Order $order): JsonResponse|OrderResource
    {
        // Enforce ownership
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load('orderItems.product');

        return new OrderResource($order);
    }
}
