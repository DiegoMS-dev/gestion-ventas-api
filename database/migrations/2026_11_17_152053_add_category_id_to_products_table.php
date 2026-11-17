
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();
        });

        // Recuperar las categorías que ya existen en productos
        $existingCategories = DB::table('products')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->get();

        foreach ($existingCategories as $item) {
            $categoryId = DB::table('categories')->insertGetId([
                'name' => $item->category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')
                ->where('category', $item->category)
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
