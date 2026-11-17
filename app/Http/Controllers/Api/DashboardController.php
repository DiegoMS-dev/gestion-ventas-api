<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    // Datos reales para el panel principal
    public function __invoke()
    {
        $outOfStock = Product::where('stock', 0)->count();

        $lowStock = Product::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // Productos que necesitan reposición (agotados primero)
        $productsToRestock = Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'code', 'name', 'stock', 'min_stock']);

        $recentSales = Sale::with('customer:id,name')
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'code', 'customer_id', 'status', 'total', 'sold_at']);

        return response()->json([
            'pending' => [
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
                'pending_sales' => Sale::where('status', Sale::STATUS_PENDING)->count(),
            ],
            'restock' => [
                'total' => $outOfStock + $lowStock,
                'products' => $productsToRestock,
            ],
            'recent_sales' => $recentSales,
        ]);
    }
}
