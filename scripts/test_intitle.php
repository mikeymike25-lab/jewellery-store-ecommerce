<?php
$queries = [
    'intitle:ring diamond gold filetype:bitmap',
    'intitle:necklace diamond gold filetype:bitmap',
    'intitle:earrings gold diamond filetype:bitmap',
    'intitle:bracelet gold diamond filetype:bitmap',
    'intitle:cufflinks gold filetype:bitmap',
    'intitle:watch luxury filetype:bitmap',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'EcloreLuxury/1.0 (contact@eclore.com)');

foreach ($queries as $q) {
    $url = "https://commons.wikimedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch=" . urlencode($q) . "&gsrnamespace=6&gsrlimit=25&prop=imageinfo&iiprop=url&iiurlwidth=800";
    curl_setopt($ch, CURLOPT_URL, $url);
    $json = curl_exec($ch);
    $data = json_decode($json, true);
    echo "$q: " . count($data['query']['pages'] ?? []) . " files\n";
    if (!empty($data['query']['pages'])) {
        foreach (array_slice($data['query']['pages'], 0, 3) as $p) {
            echo "  - " . $p['title'] . "\n";
        }
    }
}
curl_close($ch);
