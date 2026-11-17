<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    // Listar clientes con sus totales de compra
    public function index()
    {
        return response()->json(
            Customer::query()
                ->withCount([
                    'sales as sales_count' => fn ($q) => $q->where('status', '!=', Sale::STATUS_CANCELLED),
                ])
                ->withSum([
                    'sales as total_spent' => fn ($q) => $q->where('status', Sale::STATUS_COMPLETED),
                ], 'total')
                ->withMax([
                    'sales as last_purchase_at' => fn ($q) => $q->where('status', '!=', Sale::STATUS_CANCELLED),
                ], 'sold_at')
                ->orderBy('name')
                ->get()
        );
    }

    // Registrar cliente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:20|unique:customers,document',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    // Mostrar cliente
    public function show(Customer $customer)
    {
        return response()->json($customer);
    }

    // Actualizar cliente
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'document')->ignore($customer->id),
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    // Eliminar cliente
    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar un cliente que tiene ventas registradas.',
            ], 409);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }
}
