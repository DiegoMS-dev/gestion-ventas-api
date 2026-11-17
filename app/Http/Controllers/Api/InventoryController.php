<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    // Historial de movimientos (más recientes primero)
    public function index(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'type' => ['nullable', Rule::in([
                InventoryMovement::TYPE_ENTRY,
                InventoryMovement::TYPE_EXIT,
                InventoryMovement::TYPE_ADJUSTMENT,
                InventoryMovement::TYPE_SALE,
                InventoryMovement::TYPE_RETURN,
            ])],
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $movements = InventoryMovement::with('product:id,code,name')
            ->when($validated['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('id')
            ->limit($validated['limit'] ?? 100)
            ->get();

        return response()->json($movements);
    }

    // Registrar entrada, salida o ajuste manual
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'type' => ['required', Rule::in([
                InventoryMovement::TYPE_ENTRY,
                InventoryMovement::TYPE_EXIT,
                InventoryMovement::TYPE_ADJUSTMENT,
            ])],
            // entrada/salida: cantidad a mover. ajuste: stock real contado.
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validated['type'] !== InventoryMovement::TYPE_ADJUSTMENT && $validated['quantity'] < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        $movement = DB::transaction(function () use ($validated) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            $change = match ($validated['type']) {
                InventoryMovement::TYPE_ENTRY => (int) $validated['quantity'],
                InventoryMovement::TYPE_EXIT => -(int) $validated['quantity'],
                default => (int) $validated['quantity'] - (int) $product->stock,
            };

            if ($change === 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'El stock ya coincide con la cantidad indicada.',
                ]);
            }

            return $this->inventory->move(
                $product,
                $change,
                $validated['type'],
                $validated['reason'] ?? null
            );
        });

        return response()->json($movement->load('product:id,code,name,stock'), 201);
    }
}
