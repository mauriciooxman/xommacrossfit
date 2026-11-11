<?php
declare(strict_types=1);

$root = defined('XOMMA_ROOT') ? XOMMA_ROOT : dirname(__DIR__);
require $root . '/app/bootstrap.php';
require $root . '/app/checkout.php';

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'self'; script-src 'none'; base-uri 'none'; form-action 'self' https://webpay3gint.transbank.cl; frame-ancestors 'none'");
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

$error = null; $order = null; $redirectAttempt = null; $attempts = []; $site = load_site($root);
$plans = checkout_plans($site); $config = require $root . '/config/checkout.php';
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST', 'HEAD'], true)) {
    http_response_code(405); header('Allow: GET, HEAD, POST'); exit('Método no permitido.');
}
// Rechazar arrays en todos los campos antes de comparaciones o llamadas tipadas.
$input = $method === 'POST' ? $_POST : $_GET;
foreach (array_merge($_GET, $input) as $value) {
    if (!is_string($value) || strlen($value) > 2048) { http_response_code(400); exit('Solicitud inválida.'); }
}
$selected = $_GET['plan'] ?? $input['plan'] ?? array_key_first($plans);
$values = ['name' => $input['name'] ?? '', 'email' => $input['email'] ?? '', 'student_type' => $input['student_type'] ?? 'new'];
$reference = $_GET['order'] ?? '';
$secure = parse_url($config['base_url'], PHP_URL_SCHEME) === 'https';
$demoMode = demonstration_mode();

// Corte previo a sesiones, MySQL, SDK y retornos: una vista pública nunca produce efectos.
if ($demoMode) {
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'self'; script-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
    if (!in_array($method, ['GET', 'HEAD'], true) || $action !== '' || $reference !== '') {
        http_response_code(403);
        exit('Demostración: las operaciones y la consulta de órdenes están deshabilitadas.');
    }
    $csrf = $nonce = '';
    require $root . '/app/views/checkout.php';
    exit;
}

function checkout_redirect(string $reference): never
{
    header('Location: checkout.php?order=' . rawurlencode($reference), true, 303); exit;
}

try {
    // El retorno de Webpay no depende de cookies SameSite ni contiene una vista de datos personales.
    if ($action === 'return') {
        $service = new CheckoutService(checkout_db($config), new WebpayGateway(), $config);
        $reference = $service->handleReturn($_GET['attempt'] ?? '', $input);
        checkout_redirect($reference);
    }

    session_name('xomma_checkout');
    session_cache_limiter(''); // Conservar Cache-Control: no-store definido antes de iniciar la sesión.
    $sessionDirectory = $root . '/storage/checkout-sessions';
    if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true) && !is_dir($sessionDirectory)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento privado de sesiones.');
    }
    session_save_path($sessionDirectory);
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1');
    if (!session_start()) throw new RuntimeException('No se pudo iniciar la sesión privada.');
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    // Cookie privada de propietario: permite cerrar y volver durante 30 días sin exponer claves en URLs.
    $owner = $_COOKIE['xomma_owner'] ?? '';
    if (!is_string($owner) || !preg_match('/^[a-f0-9]{64}$/D', $owner)) {
        $owner = bin2hex(random_bytes(32));
        setcookie('xomma_owner', $owner, ['expires' => time() + 30 * 86400, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    }
    $_SESSION['purchase_nonce'] ??= bin2hex(random_bytes(32));
    if ($action === 'new' && $method === 'POST') {
        if (!hash_equals($_SESSION['csrf'], $input['csrf'] ?? '')) throw new DomainException('La sesión venció. Recarga la página.');
        $_SESSION['purchase_nonce'] = bin2hex(random_bytes(32));
        header('Location: checkout.php', true, 303); exit;
    }
    if ($method === 'POST' && !hash_equals($_SESSION['csrf'], $input['csrf'] ?? '')) throw new DomainException('La sesión venció. Recarga la página.');
    if ($method === 'POST' || $reference !== '') {
        $service = new CheckoutService(checkout_db($config), new WebpayGateway(), $config);
        if ($method === 'POST' && $action === 'buy') {
            // El nonce queda estable al refrescar/volver atrás; solo «Otra compra» genera uno nuevo.
            $nonce = $input['nonce'] ?? '';
            if (!preg_match('/^[a-f0-9]{64}$/D', $nonce)) throw new DomainException('Solicitud inválida.');
            $requestKey = hash('sha256', $owner . ':' . $nonce);
            // Un nonce ajeno no puede crear una compra nueva.
            if (!hash_equals($_SESSION['purchase_nonce'], $nonce)) throw new DomainException('Formulario anterior. Vuelve a iniciar la compra.');
            $order = $service->createOrder($site, $input, $owner, $requestKey);
            checkout_redirect($order['reference']);
        }
        $order = $service->order($reference, $owner);
        if ($method === 'POST' && $action === 'pay') {
            $redirectAttempt = $service->start($reference, $owner);
            if (!$redirectAttempt) checkout_redirect($reference);
        } elseif ($method === 'POST' && $action === 'check') {
            $attempts = $service->attempts((int) $order['id']);
            if ($attempts) $service->reconcile($reference, (int) $attempts[0]['id']);
            checkout_redirect($reference);
        }
        $order = $service->order($reference, $owner);
        $attempts = $service->attempts((int) $order['id']);
    }
} catch (DomainException $e) {
    http_response_code($reference ? 404 : 422); $error = $e->getMessage();
} catch (Throwable $e) {
    http_response_code(503);
    $error = 'El checkout no está disponible. Revisa la conexión MySQL y la configuración local. Si ya pagaste, conserva tu referencia y consulta el estado más tarde.';
    error_log('Xomma checkout: operación no disponible; tipo=' . get_class($e));
}
$csrf = $_SESSION['csrf'] ?? '';
$nonce = $_SESSION['purchase_nonce'] ?? '';
if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
require $root . '/app/views/checkout.php';
