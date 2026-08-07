<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        if (strlen($term) < 2) {
            return response()->json(['orders' => []]);
        }

        $orders = Order::globalSearch($term)
            ->with(['customer', 'invoice'])
            ->take(10)
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }
}
