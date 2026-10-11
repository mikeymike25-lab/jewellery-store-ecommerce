<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->delete();

        $subcategories = Category::whereNotNull('parent_id')->orderBy('category_order')->get();

        // Allowed Values according to user request
        $colors = ['yellow gold', 'white gold', 'rose gold', 'platinum', 'sterling silver', 'two-tone', 'mixed metals'];
        $materials = ['14k gold', '18k gold', '925 sterling silver', 'platinum 950', 'palladium', 'titanium', 'stainless steel', 'vermeil'];
        $gemstones = ['diamond', 'ruby', 'emerald', 'sapphire', 'pearl', 'morganite', 'aquamarine', 'amethyst', 'topaz', 'onyx', 'mixed gemstones', 'no gemstone'];
        $diamond_scales = ['no diamond', 'accent (< 0.10 ct)', '0.10 - 0.29 ct', '0.30 - 0.49 ct', '0.50 - 0.99 ct', '1.00 + ct'];

        $prefixes = ['Éclore', 'Lumière', 'Soleil', 'Clair', 'Aura', 'Celestial', 'Opulence', 'Royal', 'Divine', 'Radiance'];
        $categoryCounters = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($subcategories as $sub) {
            $count = 5; // Default
            $minPrice = 10000;
            $maxPrice = 50000;

            // Specific Subcategory Mapping
            switch ($sub->name) {
                // Rings (25 total)
                case 'Engagement Rings': $count = 5;
                    $minPrice = 25000;
                    $maxPrice = 120000;

                    break;
                case 'Wedding Bands': $count = 5;
                    $minPrice = 8000;
                    $maxPrice = 45000;

                    break;
                case 'Statement Rings': $count = 5;
                    $minPrice = 12000;
                    $maxPrice = 55000;

                    break;
                case 'Gemstone Rings': $count = 5;
                    $minPrice = 15000;
                    $maxPrice = 65000;

                    break;
                case "Signet & Men's Rings": $count = 5;
                    $minPrice = 10000;
                    $maxPrice = 40000;

                    break;

                    // Necklaces (20 total)
                case 'Solitaire Pendants': $count = 4;
                    $minPrice = 6000;
                    $maxPrice = 45000;

                    break;
                case 'Statement Necklaces': $count = 4;
                    $minPrice = 18000;
                    $maxPrice = 75000;

                    break;
                case 'Fine Chains': $count = 4;
                    $minPrice = 4000;
                    $maxPrice = 35000;

                    break;
                case 'Lockets & Charms': $count = 4;
                    $minPrice = 7000;
                    $maxPrice = 30000;

                    break;
                case 'Chokers & Collars': $count = 4;
                    $minPrice = 9000;
                    $maxPrice = 50000;

                    break;

                    // Earrings (20 total)
                case 'Diamond Studs': $count = 4;
                    $minPrice = 8000;
                    $maxPrice = 65000;

                    break;
                case 'Drop & Dangle Earrings': $count = 4;
                    $minPrice = 10000;
                    $maxPrice = 55000;

                    break;
                case 'Hoop Earrings': $count = 4;
                    $minPrice = 5000;
                    $maxPrice = 35000;

                    break;
                case 'Chandelier Earrings': $count = 4;
                    $minPrice = 15000;
                    $maxPrice = 70000;

                    break;
                case 'Ear Cuffs & Climbers': $count = 4;
                    $minPrice = 3500;
                    $maxPrice = 25000;

                    break;

                    // Bracelets (20 total)
                case 'Tennis Bracelets': $count = 4;
                    $minPrice = 18000;
                    $maxPrice = 85000;

                    break;
                case 'Charm Bracelets': $count = 4;
                    $minPrice = 9000;
                    $maxPrice = 40000;

                    break;
                case 'Cuff Bracelets': $count = 4;
                    $minPrice = 12000;
                    $maxPrice = 55000;

                    break;
                case 'Bangle Sets': $count = 4;
                    $minPrice = 8000;
                    $maxPrice = 45000;

                    break;
                case 'Chain Bracelets': $count = 4;
                    $minPrice = 5000;
                    $maxPrice = 35000;

                    break;

                    // Timepieces (15 total)
                case "Luxury Women's Watches": $count = 3;
                    $minPrice = 15000;
                    $maxPrice = 85000;

                    break;
                case "Men's Dress Watches": $count = 3;
                    $minPrice = 20000;
                    $maxPrice = 95000;

                    break;
                case 'Jewelry Boxes & Travel Cases': $count = 3;
                    $minPrice = 1500;
                    $maxPrice = 12000;

                    break;
                case 'Cufflinks & Tie Bars': $count = 3;
                    $minPrice = 2500;
                    $maxPrice = 18000;

                    break;
                case 'Brooches & Pins': $count = 3;
                    $minPrice = 4000;
                    $maxPrice = 22000;

                    break;
            }

            for ($i = 1; $i <= $count; $i++) {
                $prefix = $prefixes[array_rand($prefixes)];
                $color = $colors[array_rand($colors)];
                $material = $materials[array_rand($materials)];

                // Gemstone Logic
                if (str_contains($sub->name, 'Diamond')) {
                    $gemstone = 'diamond';
                } elseif (str_contains($sub->name, 'Gemstone')) {
                    $gemstone = $gemstones[array_rand(array_diff($gemstones, ['no gemstone', 'diamond']))];
                } else {
                    $gemstone = $gemstones[array_rand($gemstones)];
                }

                // Diamond scale logic
                if ($gemstone === 'diamond') {
                    $diamond = $diamond_scales[array_rand(array_diff($diamond_scales, ['no diamond']))];
                } else {
                    $diamond = 'no diamond';
                }

                $name = "{$prefix} ".Str::singular($sub->name)." in {$material}";
                if ($gemstone !== 'no gemstone') {
                    $name .= ' with '.Str::ucfirst($gemstone);
                }

                $sku = sprintf('%d%02d%02d', $sub->parent_id, $sub->category_order, $i);
                $price = rand($minPrice, $maxPrice);
                $cost = $price * 0.5;

                $catPrefixMap = [
                    1 => 'ring',
                    2 => 'necklace',
                    3 => 'earring',
                    4 => 'bracelet',
                    5 => 'timepiece',
                ];
                $catId = $sub->parent_id ?? 1;
                $categoryCounters[$catId] = ($categoryCounters[$catId] ?? 0) + 1;
                $itemNum = $categoryCounters[$catId];
                $catPfx = $catPrefixMap[$catId] ?? 'ring';

                $primaryImgPath = "/assets/products/{$catPfx}-{$itemNum}.jpg";

                Product::create([
                    'category_id' => $sub->parent_id,
                    'subcategory_id' => $sub->id,
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.$sku,
                    'description' => "A bespoke jewellery piece from Éclore's exclusive vault. This masterpiece showcases fine craftsmanship in {$material} with a meticulous {$color} finish, accentuating the natural brilliance of the {$gemstone}.",
                    'short_description' => 'Exquisite handcrafted '.Str::lower($sub->name),
                    'price' => $price,
                    'cost_price' => $cost,
                    'sale_price' => rand(1, 10) === 1 ? $price * 0.9 : null,
                    'tax_class' => 'VAT',
                    'sku' => $sku,
                    'barcode' => 'BCL'.strtoupper(Str::random(8)),
                    'stock_quantity' => rand(1, 12),
                    'low_stock_threshold' => 2,
                    'manage_stock' => true,
                    'in_stock' => true,
                    'material' => $material,
                    'color' => $color,
                    'gemstone' => $gemstone,
                    'diamonds' => $diamond,
                    'images' => [$primaryImgPath],
                    'gallery' => [$primaryImgPath],
                    'featured' => rand(1, 20) === 1,
                    'is_active' => true,
                    'view_count' => rand(50, 5000),
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }
        }
    }
}
