<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    // Resumen de ventas por rango de fechas (por defecto: mes actual)
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now())->endOfDay();

        if (abs($from->diffInDays($to)) > 366) {
            throw ValidationException::withMessages([
                'to' => 'El rango máximo permitido es de un año.',
            ]);
        }

        // Solo las ventas completadas cuentan como ingreso
        $completed = fn () => Sale::where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sold_at', [$from, $to]);

        $items = fn () => SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sold_at', [$from, $to]);

        $salesCount = $completed()->count();
        $revenue = (float) $completed()->sum('total');

        // Ventas por día (rellenando los días sin ventas con 0)
        $perDay = $completed()
            ->selectRaw('DATE(sold_at) as day, SUM(total) as total, COUNT(*) as count')
            ->groupByRaw('DATE(sold_at)')
            ->get()
            ->keyBy('day');

        $byDay = [];
        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            $key = $date->format('Y-m-d');
            $row = $perDay->get($key);

            $byDay[] = [
                'date' => $key,
                'total' => $row ? (float) $row->total : 0,
                'count' => $row ? (int) $row->count : 0,
            ];
        }

        $topProducts = $items()
            ->selectRaw('sale_items.product_name as name, SUM(sale_items.quantity) as quantity, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ]);

        $byCategory = $items()
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("COALESCE(categories.name, 'Sin categoría') as name, SUM(sale_items.quantity) as quantity, SUM(sale_items.subtotal) as revenue")
            ->groupByRaw("COALESCE(categories.name, 'Sin categoría')")
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ]);

        $byPaymentMethod = $completed()
            ->selectRaw('payment_method as method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'method' => $row->method,
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);

        $topCustomers = $completed()
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->selectRaw('customers.id as id, customers.name as name, COUNT(*) as count, SUM(sales.total) as total')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);

        $pending = Sale::where('status', Sale::STATUS_PENDING)->whereBetween('sold_at', [$from, $to]);

        return response()->json([
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totals' => [
                'revenue' => $revenue,
                'sales_count' => $salesCount,
                'average_ticket' => $salesCount > 0 ? round($revenue / $salesCount, 2) : 0,
                'units_sold' => (int) $items()->sum('sale_items.quantity'),
                'pending_count' => (clone $pending)->count(),
                'pending_amount' => (float) (clone $pending)->sum('total'),
                'cancelled_count' => Sale::where('status', Sale::STATUS_CANCELLED)
                    ->whereBetween('sold_at', [$from, $to])
                    ->count(),
            ],
            'by_day' => $byDay,
            'top_products' => $topProducts,
            'by_category' => $byCategory,
            'by_payment_method' => $byPaymentMethod,
            'top_customers' => $topCustomers,
        ]);
    }
}
