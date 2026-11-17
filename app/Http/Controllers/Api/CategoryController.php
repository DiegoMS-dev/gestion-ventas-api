<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Listar categorías
    public function index()
    {
        return response()->json(
            Category::withCount('products')
                ->orderBy('name')
                ->get()
        );
    }

    // Registrar categoría
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    // Mostrar categoría
    public function show(Category $category)
    {
        return response()->json(
            $category->loadCount('products')
        );
    }

    // Actualizar categoría
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    // Eliminar categoría
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'No puedes eliminar una categoría que tiene productos asociados.'
            ], 409);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.'
        ]);
    }
}