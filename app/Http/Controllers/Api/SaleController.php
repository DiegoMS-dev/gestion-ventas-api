<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    // Listar ventas
    public function index()
    {
        return response()->json(
            Sale::with('customer:id,name,document')
                ->withCount('items')
                ->orderByDesc('sold_at')
                ->orderByDesc('id')
                ->get()
        );
    }

    // Detalle de una venta
    public function show(Sale $sale)
    {
        return response()->json(
            $sale->load('customer:id,name,document,phone', 'items')
        );
    }

    // Registrar venta (descuenta stock)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => ['required', Rule::in(Sale::PAYMENT_METHODS)],
            'status' => ['required', Rule::in([Sale::STATUS_COMPLETED, Sale::STATUS_PENDING])],
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Si el mismo producto viene repetido, sumamos las cantidades
        $quantities = [];
        foreach ($validated['items'] as $item) {
            $id = (int) $item['product_id'];
            $quantities[$id] = ($quantities[$id] ?? 0) + (int) $item['quantity'];
        }

        $sale = DB::transaction(function () use ($validated, $quantities) {
            // Bloqueamos los productos en orden para evitar deadlocks
            ksort($quantities);

            $products = Product::whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sale = Sale::create([
                'code' => 'TMP-' . Str::uuid(),
                'customer_id' => $validated['customer_id'] ?? null,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'total' => 0,
                'sold_at' => now(),
            ]);

            $sale->update(['code' => 'V-' . str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT)]);

            $total = 0;

            foreach ($quantities as $productId => $quantity) {
                $product = $products[$productId];
                $unitPrice = (float) $product->price;
                $subtotal = round($unitPrice * $quantity, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                // Lanza ValidationException si no hay stock suficiente (revierte todo)
                $this->inventory->move(
                    $product,
                    -$quantity,
                    InventoryMovement::TYPE_SALE,
                    'Venta registrada',
                    $sale->code
                );

                $total += $subtotal;
            }

            $sale->update(['total' => round($total, 2)]);

            return $sale;
        });

        return response()->json(
            $sale->load('customer:id,name,document', 'items'),
            201
        );
    }

    // Cambiar estado (pendiente -> completada, o cancelar y devolver stock)
    public function updateStatus(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Sale::STATUS_COMPLETED,
                Sale::STATUS_PENDING,
                Sale::STATUS_CANCELLED,
            ])],
        ]);

        $newStatus = $validated['status'];

        if ($sale->status === Sale::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Una venta cancelada no puede volver a activarse.',
            ]);
        }

        if ($sale->status === $newStatus) {
            return response()->json($sale->load('customer:id,name,document'));
        }

        DB::transaction(function () use ($sale, $newStatus) {
            if ($newStatus === Sale::STATUS_CANCELLED) {
                $items = $sale->items()->whereNotNull('product_id')->orderBy('product_id')->get();

                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);

                    if (! $product) {
                        continue;
                    }

                    $this->inventory->move(
                        $product,
                        (int) $item->quantity,
                        InventoryMovement::TYPE_RETURN,
                        'Venta cancelada',
                        $sale->code
                    );
                }
            }

            $sale->update(['status' => $newStatus]);
        });

        return response()->json($sale->load('customer:id,name,document'));
    }
}
