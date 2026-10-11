<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE categories RESTART IDENTITY CASCADE;');
        } else {
            Category::query()->delete();
        }

        $categories = [
            // Main Categories
            ['id' => 1, 'name' => 'Rings', 'slug' => 'rings', 'category_order' => 0],
            ['id' => 2, 'name' => 'Necklaces & Pendants', 'slug' => 'necklaces-pendants', 'category_order' => 0],
            ['id' => 3, 'name' => 'Earrings', 'slug' => 'earrings', 'category_order' => 0],
            ['id' => 4, 'name' => 'Bracelets & Bangles', 'slug' => 'bracelets-bangles', 'category_order' => 0],
            ['id' => 5, 'name' => 'Timepieces & Fine Accessories', 'slug' => 'timepieces-accessories', 'category_order' => 0],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $subcategories = [
            // id=1 Rings
            ['name' => 'Engagement Rings', 'parent_id' => 1, 'category_order' => 1],
            ['name' => 'Wedding Bands', 'parent_id' => 1, 'category_order' => 2],
            ['name' => 'Statement Rings', 'parent_id' => 1, 'category_order' => 3],
            ['name' => 'Gemstone Rings', 'parent_id' => 1, 'category_order' => 4],
            ['name' => "Signet & Men's Rings", 'parent_id' => 1, 'category_order' => 5],

            // id=2 Necklaces
            ['name' => 'Solitaire Pendants', 'parent_id' => 2, 'category_order' => 6],
            ['name' => 'Statement Necklaces', 'parent_id' => 2, 'category_order' => 7],
            ['name' => 'Fine Chains', 'parent_id' => 2, 'category_order' => 8],
            ['name' => 'Lockets & Charms', 'parent_id' => 2, 'category_order' => 9],
            ['name' => 'Chokers & Collars', 'parent_id' => 2, 'category_order' => 10],

            // id=3 Earrings
            ['name' => 'Diamond Studs', 'parent_id' => 3, 'category_order' => 11],
            ['name' => 'Drop & Dangle Earrings', 'parent_id' => 3, 'category_order' => 12],
            ['name' => 'Hoop Earrings', 'parent_id' => 3, 'category_order' => 13],
            ['name' => 'Chandelier Earrings', 'parent_id' => 3, 'category_order' => 14],
            ['name' => 'Ear Cuffs & Climbers', 'parent_id' => 3, 'category_order' => 15],

            // id=4 Bracelets
            ['name' => 'Tennis Bracelets', 'parent_id' => 4, 'category_order' => 16],
            ['name' => 'Charm Bracelets', 'parent_id' => 4, 'category_order' => 17],
            ['name' => 'Cuff Bracelets', 'parent_id' => 4, 'category_order' => 18],
            ['name' => 'Bangle Sets', 'parent_id' => 4, 'category_order' => 19],
            ['name' => 'Chain Bracelets', 'parent_id' => 4, 'category_order' => 20],

            // id=5 Timepieces
            ['name' => "Luxury Women's Watches", 'parent_id' => 5, 'category_order' => 21],
            ['name' => "Men's Dress Watches", 'parent_id' => 5, 'category_order' => 22],
            ['name' => 'Jewelry Boxes & Travel Cases', 'parent_id' => 5, 'category_order' => 23],
            ['name' => 'Cufflinks & Tie Bars', 'parent_id' => 5, 'category_order' => 24],
            ['name' => 'Brooches & Pins', 'parent_id' => 5, 'category_order' => 25],
        ];

        $nextId = 6;
        foreach ($subcategories as $sub) {
            Category::create([
                'id' => $nextId++,
                'name' => $sub['name'],
                'slug' => Str::slug($sub['name']),
                'parent_id' => $sub['parent_id'],
                'category_order' => $sub['category_order'],
                'is_active' => true,
            ]);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('categories', 'id'), coalesce((SELECT MAX(id) FROM categories), 1));");
        }
    }
}
