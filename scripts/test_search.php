<?php
$url = 'https://commons.wikimedia.org/w/api.php?action=query&format=json&generator=search&gsrnamespace=6&gsrsearch=filetype:bitmap%20ring%20diamond%20gold%20jewelry&gsrlimit=3&prop=imageinfo&iiprop=url|mime&iiurlwidth=800';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'EcloreApp/1.0 (contact@eclore.com)');
$json = curl_exec($ch);
curl_close($ch);
$data = json_decode($json, true);
foreach ($data['query']['pages'] as $p) {
    echo "- " . $p['title'] . PHP_EOL;
    echo "  thumb: " . ($p['imageinfo'][0]['thumburl'] ?? '') . PHP_EOL;
}
