<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_items')) {
            foreach (DB::table('menu_items')->orderBy('id')->cursor() as $mi) {
                $categoryId = $this->resolveCategoryId((int) $mi->restaurant_id, $mi->category_id ? (int) $mi->category_id : null);
                $slug = Str::slug($mi->name).'-'.$mi->id;

                DB::table('products')->insert([
                    'id' => $mi->id,
                    'restaurant_id' => $mi->restaurant_id,
                    'category_id' => $categoryId,
                    'name' => $mi->name,
                    'slug' => $slug,
                    'description' => $mi->description,
                    'image' => $mi->image_path,
                    'base_price' => $mi->price,
                    'discount_price' => null,
                    'preparation_time' => null,
                    'is_active' => $mi->is_available,
                    'is_featured' => false,
                    'sort_order' => $mi->sort_order,
                    'dietary_info' => null,
                    'allergens' => null,
                    'calories' => null,
                    'created_at' => $mi->created_at ?? now(),
                    'updated_at' => $mi->updated_at ?? now(),
                    'deleted_at' => null,
                ]);

                $sizeId = DB::table('product_sizes')->insertGetId([
                    'product_id' => $mi->id,
                    'name' => 'Standard',
                    'price' => $mi->price,
                    'is_default' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_stock')->insert([
                    'product_id' => $mi->id,
                    'branch_id' => null,
                    'quantity' => 999,
                    'low_stock_threshold' => 5,
                    'track_stock' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->rememberSizeId((int) $mi->id, (int) $sizeId);
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('product_size_id')->nullable()->after('product_id')->constrained('product_sizes')->nullOnDelete();
        });

        foreach (DB::table('order_items')->whereNotNull('menu_item_id')->cursor() as $oi) {
            $sizeId = $this->sizeIds[(int) $oi->menu_item_id] ?? DB::table('product_sizes')
                ->where('product_id', $oi->menu_item_id)
                ->where('is_default', true)
                ->value('id');

            DB::table('order_items')->where('id', $oi->id)->update([
                'product_id' => $oi->menu_item_id,
                'product_size_id' => $sizeId,
            ]);
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['menu_item_id']);
            $table->dropColumn('menu_item_id');
        });

        Schema::drop('menu_items');
    }

    /** @var array<int, int> */
    private array $sizeIds = [];

    private function rememberSizeId(int $productId, int $sizeId): void
    {
        $this->sizeIds[$productId] = $sizeId;
    }

    private function resolveCategoryId(int $restaurantId, ?int $categoryId): int
    {
        if ($categoryId) {
            return $categoryId;
        }

        $existing = DB::table('categories')->where('restaurant_id', $restaurantId)->orderBy('id')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('categories')->insertGetId([
            'restaurant_id' => $restaurantId,
            'name' => 'General',
            'slug' => 'general-'.$restaurantId,
            'description' => null,
            'image' => null,
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        throw new \RuntimeException('This migration cannot be reversed safely.');
    }
};
