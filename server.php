<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If user navigates directly to /admin or /admin/, redirect to admin subdomain
if ($uri === '/admin' || $uri === '/admin/') {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $parts = explode(':', $host);
    $port = isset($parts[1]) ? ':' . $parts[1] : '';
    header('Location: http://admin.localhost' . $port . '/login', true, 302);
    exit;
}

// Emulate Apache's mod_rewrite for static files
if ($uri !== '/' && file_exists($publicPath . $uri) && !is_dir($publicPath . $uri)) {
    return false;
}

require_once $publicPath . '/index.php';
