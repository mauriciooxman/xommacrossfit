<?php
declare(strict_types=1);

// Router exclusivo de presentación; se ejecuta con -t public. No sirve archivos privados.
$publicRoot = realpath(dirname(__DIR__) . '/public');
if ((require dirname(__DIR__) . '/config/demo.php')['enabled'] !== true) {
    http_response_code(503); exit('La presentación temporal está desactivada.');
}
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(403); exit('Demostración: operaciones deshabilitadas.');
}
if (in_array($path, ['/', '/index.php', '/checkout.php'], true)) {
    $entry = $path === '/checkout.php' ? 'checkout.php' : 'index.php';
    $_SERVER['SCRIPT_FILENAME'] = $publicRoot . DIRECTORY_SEPARATOR . $entry;
    $_SERVER['SCRIPT_NAME'] = '/' . $entry;
    require $publicRoot . DIRECTORY_SEPARATOR . $entry;
    return true;
}
if (preg_match('~^/assets/(?:css|js|images|videos)/[a-zA-Z0-9][a-zA-Z0-9_.-]*\.(?:css|js|webp|png|jpe?g|svg|ico|mp4|webm|woff2?)$~D', $path)) {
    $file = realpath($publicRoot . $path);
    if ($file && is_file($file) && str_starts_with(strtolower($file), strtolower($publicRoot . DIRECTORY_SEPARATOR))) return false;
}
http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Recurso no disponible.';
return true;
