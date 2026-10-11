<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WishlistItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('wishlist_items')->delete();

        $users = User::all();
        $products = Product::all();

        $inserted = [];
        $items = [];

        for ($i = 0; $i < 400; $i++) {
            $user = $users->random();
            $product = $products->random();
            $key = $user->id.'-'.$product->id;

            if (isset($inserted[$key])) {
                continue;
            }
            $inserted[$key] = true;

            $items[] = [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'created_at' => now()->subDays(rand(1, 30)),
            ];
        }

        DB::table('wishlist_items')->insert($items);
    }
}
