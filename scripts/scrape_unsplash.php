<?php

function getPhotoIdsFromSearch($query, $needed = 25) {
    $url = "https://unsplash.com/s/photos/" . urlencode($query);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http !== 200 || !$html) {
        return [];
    }

    // Match photo IDs from URLs like https://images.unsplash.com/photo-1605100804763-247f67b3557e
    preg_match_all('/photo-([0-9a-fA-F-]+)/', $html, $matches);
    $ids = array_unique($matches[0] ?? []);

    return array_slice($ids, 0, $needed);
}

$queries = [
    'diamond-ring' => 15,
    'gold-wedding-ring' => 15,
    'diamond-necklace' => 15,
    'gold-pendant-necklace' => 15,
    'diamond-earrings' => 15,
    'gold-hoop-earrings' => 15,
    'gold-bracelet-jewelry' => 15,
    'diamond-tennis-bracelet' => 15,
    'luxury-wristwatch' => 15,
    'gold-cufflinks' => 10,
];

$all = [];
foreach ($queries as $q => $count) {
    $ids = getPhotoIdsFromSearch($q, $count);
    echo "Query '$q' -> found " . count($ids) . " photo IDs\n";
    $all[$q] = $ids;
}

file_put_contents(__DIR__ . '/scraped_ids.json', json_encode($all, JSON_PRETTY_PRINT));
echo "Saved to scraped_ids.json\n";
