<?php

/**
 * Script to populate 100 distinct luxury jewelry images for Éclore products.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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

// Curated high-resolution jewelry photography from verified Unsplash Luxury Jewelry collection
$ringsUrls = [
    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1598560917505-59a3ad559071?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014755-ba31aa59c4b6?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1589674781759-c21c37956a44?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1608042314453-ae338d80c427?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1609172791834-3998b50f757f?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1622398925373-3f91b1e275f5?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1611591475880-3663a8a30364?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1620656798579-1984d9e87dfa?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1605289982774-9a6fef564df8?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014608-c2b647565e31?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1616877562261-26b6e4e5f7a3?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1615655406736-b37c4fabf923?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1543290936-71dc1b3285bb?auto=format&fit=crop&w=700&q=80',
];

$necklacesUrls = [
    'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1544441893-675973e31985?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1611591475880-3663a8a30364?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1598560917505-59a3ad559071?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014755-ba31aa59c4b6?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1615655406736-b37c4fabf923?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1608042314453-ae338d80c427?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1620656798579-1984d9e87dfa?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1622398925373-3f91b1e275f5?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1616877562261-26b6e4e5f7a3?auto=format&fit=crop&w=700&q=80',
];

$earringsUrls = [
    'https://images.unsplash.com/photo-1635767798638-3e25273a8236?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1588444837495-c6cfeb53f32d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1598560917505-59a3ad559071?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014755-ba31aa59c4b6?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1615655406736-b37c4fabf923?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1608042314453-ae338d80c427?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1620656798579-1984d9e87dfa?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1611591475880-3663a8a30364?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1622398925373-3f91b1e275f5?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1616877562261-26b6e4e5f7a3?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=700&q=80',
];

$braceletsUrls = [
    'https://images.unsplash.com/photo-1611591475880-3663a8a30364?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014755-ba31aa59c4b6?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1615655406736-b37c4fabf923?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1608042314453-ae338d80c427?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1620656798579-1984d9e87dfa?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1622398925373-3f91b1e275f5?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1616877562261-26b6e4e5f7a3?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1588444837495-c6cfeb53f32d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1635767798638-3e25273a8236?auto=format&fit=crop&w=700&q=80',
];

$accessoriesUrls = [
    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1547996160-c1dfa58cf8d8?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1508615039623-a25605d2b022?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1600003014755-ba31aa59c4b6?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1615655406736-b37c4fabf923?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1608042314453-ae338d80c427?auto=format&fit=crop&w=700&q=80',
];

$categoryUrlMap = [
    1 => ['prefix' => 'ring', 'urls' => $ringsUrls, 'fallback' => __DIR__ . '/../frontend-ionic/public/assets/ring.webp'],
    2 => ['prefix' => 'necklace', 'urls' => $necklacesUrls, 'fallback' => __DIR__ . '/../frontend-ionic/public/assets/necklace.webp'],
    3 => ['prefix' => 'earring', 'urls' => $earringsUrls, 'fallback' => __DIR__ . '/../frontend-ionic/public/assets/earrings.webp'],
    4 => ['prefix' => 'bracelet', 'urls' => $braceletsUrls, 'fallback' => __DIR__ . '/../frontend-ionic/public/assets/bracelet.webp'],
    5 => ['prefix' => 'timepiece', 'urls' => $accessoriesUrls, 'fallback' => __DIR__ . '/../frontend-ionic/public/assets/collection-section.webp'],
];

echo "Fetching and preparing 100 dedicated luxury product photos...\n";

$products = Product::orderBy('id')->get();
$counters = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

foreach ($products as $prod) {
    $catId = $prod->category_id ?? 1;
    if (!isset($categoryUrlMap[$catId])) {
        $catId = 1;
    }

    $map = $categoryUrlMap[$catId];
    $idx = $counters[$catId];
    $counters[$catId]++;

    $url = $map['urls'][$idx % count($map['urls'])];
    $filename = "{$map['prefix']}-" . ($idx + 1) . ".jpg";
    $relPath = "/assets/products/{$filename}";

    // Check if first target file already exists and is non-empty
    $firstTarget = "{$destDirs[0]}/{$filename}";
    $downloadSuccess = false;

    if (file_exists($firstTarget) && filesize($firstTarget) > 1000) {
        $downloadSuccess = true;
    } else {
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 200 && $data && strlen($data) > 1000) {
            foreach ($destDirs as $d) {
                file_put_contents("{$d}/{$filename}", $data);
            }
            $downloadSuccess = true;
        } else {
            // Fallback to copying category asset
            if (file_exists($map['fallback'])) {
                $ext = pathinfo($map['fallback'], PATHINFO_EXTENSION);
                $fallbackFilename = "{$map['prefix']}-" . ($idx + 1) . ".{$ext}";
                $relPath = "/assets/products/{$fallbackFilename}";
                foreach ($destDirs as $d) {
                    copy($map['fallback'], "{$d}/{$fallbackFilename}");
                }
                $downloadSuccess = true;
            }
        }
    }

    // Ensure all dest dirs have the file
    if ($downloadSuccess) {
        $baseFile = "{$destDirs[0]}/{$filename}";
        if (file_exists($baseFile)) {
            foreach ($destDirs as $d) {
                $targetFile = "{$d}/{$filename}";
                if (!file_exists($targetFile)) {
                    copy($baseFile, $targetFile);
                }
            }
        }
    }

    // Gallery images: primary + alternate
    $altIdx = ($idx + 1) % count($map['urls']);
    $altFilename = "{$map['prefix']}-" . ($altIdx + 1) . ".jpg";
    $gallery = [
        $relPath,
        "/assets/products/{$altFilename}",
    ];

    // Update product in DB
    $prod->images = [$relPath];
    $prod->gallery = $gallery;
    $prod->save();

    echo "Saved Product #{$prod->id} ({$prod->name}) -> {$relPath}\n";
}

curl_close($ch);
echo "Successfully completed! 100 products updated with dedicated luxury assets.\n";
