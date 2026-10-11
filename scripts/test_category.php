<?php
$cats = [
    'Category:Diamond_rings',
    'Category:Engagement_rings',
    'Category:Diamond_necklaces',
    'Category:Pendants_(jewellery)',
    'Category:Earrings',
    'Category:Bracelets',
    'Category:Wristwatches',
    'Category:Cufflinks',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'EcloreLuxury/1.0 (contact@eclore.com)');

foreach ($cats as $cat) {
    $url = "https://commons.wikimedia.org/w/api.php?action=query&format=json&generator=categorymembers&gcmtitle=" . urlencode($cat) . "&gcmnamespace=6&gcmlimit=30&prop=imageinfo&iiprop=url&iiurlwidth=800";
    curl_setopt($ch, CURLOPT_URL, $url);
    $json = curl_exec($ch);
    $data = json_decode($json, true);
    echo "$cat: " . count($data['query']['pages'] ?? []) . " files\n";
}
curl_close($ch);
