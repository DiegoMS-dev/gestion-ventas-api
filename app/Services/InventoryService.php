<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Cambia el stock de un producto y deja registrado el movimiento.
     *
     * IMPORTANTE: debe llamarse dentro de DB::transaction() y con el producto
     * obtenido con lockForUpdate(), para evitar ventas simultáneas sobre el mismo stock.
     *
     * @param  int  $change  Cantidad con signo: positiva suma, negativa resta.
     */
    public function move(
        Product $product,
        int $change,
        string $type,
        ?string $reason = null,
        ?string $reference = null
    ): InventoryMovement {
        $before = (int) $product->stock;
        $after = $before + $change;

        if ($after < 0) {
            throw ValidationException::withMessages([
                'stock' => "Stock insuficiente para \"{$product->name}\". Disponible: {$before}.",
            ]);
        }

        $product->update(['stock' => $after]);

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $change,
            'stock_before' => $before,
            'stock_after' => $after,
            'reason' => $reason,
            'reference' => $reference,
        ]);
    }
}
