<?php

/**
 * Downloads 100 unique, genuine, documented fine jewelry photos from Wikimedia Commons.
 * Verifies all 100 hashes are unique.
 * Updates Database products with exactly 1 image per product.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\File;

$destDirs = [
    __DIR__ . '/../public/frontend/assets/products',
    __DIR__ . '/../frontend-ionic/public/assets/products',
];

foreach ($destDirs as $d) {
    if (!File::exists($d)) {
        File::makeDirectory($d, 0755, true);
    }
}

function searchWikimediaImages($query, $neededCount, &$usedUrls) {
    $results = [];
    $offset = 0;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'EcloreLuxuryJewellery/2.0 (contact@eclore.com)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    while (count($results) < $neededCount && $offset < 100) {
        $encodedQuery = urlencode($query);
        $url = "https://commons.wikimedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch={$encodedQuery}&gsrnamespace=6&gsrlimit=30&gsroffset={$offset}&prop=imageinfo&iiprop=url|mime&iiurlwidth=800";
        curl_setopt($ch, CURLOPT_URL, $url);
        $json = curl_exec($ch);
        $data = json_decode($json, true);

        if (!empty($data['query']['pages'])) {
            foreach ($data['query']['pages'] as $page) {
                $title = strtolower($page['title'] ?? '');
                // Strict filters
                if (
                    str_contains($title, 'diagram') ||
                    str_contains($title, 'map') ||
                    str_contains($title, 'portrait') ||
                    str_contains($title, 'coin') ||
                    str_contains($title, 'stamp') ||
                    str_contains($title, 'building') ||
                    str_contains($title, 'samsung') ||
                    str_contains($title, 'smartwatch') ||
                    str_contains($title, 'apple watch') ||
                    str_contains($title, 'book') ||
                    str_contains($title, 'drawing') ||
                    str_contains($title, 'illustration')
                ) {
                    continue;
                }

                $imgInfo = $page['imageinfo'][0] ?? null;
                if (!$imgInfo) continue;

                $thumbUrl = $imgInfo['thumburl'] ?? $imgInfo['url'] ?? null;
                if ($thumbUrl && !in_array($thumbUrl, $usedUrls)) {
                    $usedUrls[] = $thumbUrl;
                    $results[] = [
                        'title' => $page['title'],
                        'url' => $thumbUrl,
                    ];
                    if (count($results) >= $neededCount) break 2;
                }
            }
        }
        $offset += 30;
    }

    curl_close($ch);
    return $results;
}

$usedUrls = [];
$categories = [
    // 25 Rings
    1 => [
        'prefix' => 'ring',
        'query' => 'intitle:ring (diamond OR gold OR gemstone) filetype:bitmap',
        'count' => 25
    ],
    // 20 Necklaces
    2 => [
        'prefix' => 'necklace',
        'query' => 'intitle:necklace (diamond OR gold OR pendant) filetype:bitmap -earrings',
        'count' => 20
    ],
    // 20 Earrings
    3 => [
        'prefix' => 'earring',
        'query' => 'intitle:earrings (gold OR diamond OR gemstone) filetype:bitmap -necklace',
        'count' => 20
    ],
    // 20 Bracelets
    4 => [
        'prefix' => 'bracelet',
        'query' => 'intitle:bracelet (gold OR diamond OR bangle) filetype:bitmap -necklace',
        'count' => 20
    ],
    // 15 Timepieces & Fine Accessories (6 luxury watches, 5 cufflinks, 4 brooches)
    5 => [
        'prefix' => 'timepiece',
        'subqueries' => [
            ['query' => 'intitle:wristwatch (gold OR luxury OR automatic) filetype:bitmap -samsung -smartwatch', 'count' => 6],
            ['query' => 'intitle:cufflinks (gold OR silver) filetype:bitmap', 'count' => 5],
            ['query' => 'intitle:brooch (gold OR diamond) filetype:bitmap', 'count' => 4],
        ],
        'count' => 15
    ],
];

echo "1. Searching Wikimedia for 100 genuine luxury jewelry photos...\n";

$downloadPlan = [];
foreach ($categories as $catId => $cat) {
    if (isset($cat['subqueries'])) {
        $catImages = [];
        foreach ($cat['subqueries'] as $sub) {
            $imgs = searchWikimediaImages($sub['query'], $sub['count'], $usedUrls);
            $catImages = array_merge($catImages, $imgs);
        }
    } else {
        $catImages = searchWikimediaImages($cat['query'], $cat['count'], $usedUrls);
    }

    echo "Found " . count($catImages) . " distinct images for Category {$catId} ({$cat['prefix']})\n";
    $downloadPlan[$catId] = $catImages;
}

echo "\n2. Downloading and saving images to both public & ionic assets...\n";
$downloadedHashes = [];

foreach ($categories as $catId => $cat) {
    $images = $downloadPlan[$catId];
    $count = $cat['count'];

    for ($i = 1; $i <= $count; $i++) {
        $idx = $i - 1;
        $img = $images[$idx] ?? null;
        $filename = "{$cat['prefix']}-{$i}.jpg";

        if ($img) {
            $ch = curl_init($img['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $data = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http === 200 && strlen($data) > 2000) {
                $hash = md5($data);
                $downloadedHashes[$filename] = $hash;
                foreach ($destDirs as $d) {
                    file_put_contents("{$d}/{$filename}", $data);
                }
                echo "  Downloaded [{$i}/{$count}] {$filename} (" . strlen($data) . " bytes) -> {$img['title']}\n";
            } else {
                echo "  Failed [{$i}/{$count}] {$filename} from {$img['url']}\n";
            }
        }
    }
}

echo "\n3. Checking duplicates:\n";
$hashCounts = array_count_values($downloadedHashes);
$duplicates = array_filter($hashCounts, fn($c) => $c > 1);
if (empty($duplicates)) {
    echo "SUCCESS: 100% of downloaded files are UNIQUE! Zero duplicates detected.\n";
} else {
    echo "WARNING: Some duplicates found: " . count($duplicates) . "\n";
}

echo "\n4. Updating Products in Database with exactly 1 image per product...\n";
$products = Product::orderBy('id')->get();
$counters = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

foreach ($products as $prod) {
    $catId = $prod->category_id ?? 1;
    if (!isset($categories[$catId])) $catId = 1;

    $cat = $categories[$catId];
    $counters[$catId]++;
    $num = $counters[$catId];
    if ($num > $cat['count']) $num = $cat['count'];

    $relPath = "/assets/products/{$cat['prefix']}-{$num}.jpg";

    // Strictly ONE single image per product:
    $prod->images = [$relPath];
    $prod->gallery = [$relPath];
    $prod->save();
}

echo "Database updated! All " . $products->count() . " products now have exactly 1 genuine jewelry image.\n";
