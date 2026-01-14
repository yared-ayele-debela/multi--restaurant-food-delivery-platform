<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->decimal('base_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->unsignedInteger('preparation_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('dietary_info')->nullable();
            $table->json('allergens')->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['restaurant_id', 'slug']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index('category_id');
        });

        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('price', 10, 2);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_quantity')->default(1);
            $table->string('group_name', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('product_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('restaurant_branches')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('track_stock')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
            $table->index('product_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock');
        Schema::dropIfExists('product_addons');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('products');
    }
};
