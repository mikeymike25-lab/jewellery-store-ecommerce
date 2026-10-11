<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('session_id', 'idx_cart_items_session_id');
            $table->index('user_id', 'idx_cart_items_user_id');
            $table->index(['session_id', 'product_id'], 'idx_cart_items_session_product');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->index('session_id', 'idx_wishlist_items_session_id');
            $table->index('user_id', 'idx_wishlist_items_user_id');
            $table->index(['session_id', 'product_id'], 'idx_wishlist_items_session_product');
            $table->index(['user_id', 'product_id'], 'idx_wishlist_items_user_product');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index('product_id', 'idx_product_reviews_product_id');
            $table->index('is_approved', 'idx_product_reviews_is_approved');
            $table->index(['product_id', 'is_approved'], 'idx_product_reviews_product_approved');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id', 'idx_orders_user_id');
            $table->index('status', 'idx_orders_status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('created_at', 'idx_products_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('idx_cart_items_session_id');
            $table->dropIndex('idx_cart_items_user_id');
            $table->dropIndex('idx_cart_items_session_product');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropIndex('idx_wishlist_items_session_id');
            $table->dropIndex('idx_wishlist_items_user_id');
            $table->dropIndex('idx_wishlist_items_session_product');
            $table->dropIndex('idx_wishlist_items_user_product');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_product_reviews_product_id');
            $table->dropIndex('idx_product_reviews_is_approved');
            $table->dropIndex('idx_product_reviews_product_approved');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_created_at');
        });
    }
};
