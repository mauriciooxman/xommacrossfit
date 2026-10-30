<?php
declare(strict_types=1);

// Para hosting con public_html fijo, definir XOMMA_ROOT en el wrapper privado descrito en README.
$root = defined('XOMMA_ROOT') ? XOMMA_ROOT : dirname(__DIR__);
require $root . '/app/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; media-src 'self'; style-src 'self'; script-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    echo 'Esta página no recibe formularios.';
    exit;
}

try {
    render_home(load_site($root), dirname($_SERVER['SCRIPT_FILENAME'] ?? __FILE__));
} catch (Throwable $exception) {
    http_response_code(500);
    error_log('Xommafitnes: no se pudo renderizar la página. Revisar configuración privada.');
    echo 'La página no está disponible en este momento.';
}
