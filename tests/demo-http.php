<?php
declare(strict_types=1);
// Validación de demostración por HTTP/HTTPS; nunca crea órdenes ni llama a Transbank.
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
if (!filter_var($base, FILTER_VALIDATE_URL) || !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)) exit(1);
$checks = 0;
function demo_check(bool $ok, string $label): void {
    global $checks; $checks++;
    if (!$ok) throw new RuntimeException('FAIL: ' . $label);
}
function demo_request(string $path, string $method = 'GET'): array {
    global $base;
    $curl = curl_init($base . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 25, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PATH_AS_IS => true]);
    if ($method === 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
        'plan' => 'full-day-8', 'name' => 'Prueba demo', 'email' => 'demo@example.invalid',
        'student_type' => 'new', 'method' => 'transfer', 'amount' => 1, 'csrf' => 'forzado',
    ]));
    $raw = curl_exec($curl);
    if ($raw === false) throw new RuntimeException('No se pudo conectar al sitio de demostración: curl ' . curl_errno($curl));
    $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return [$code, substr($raw, 0, $size), substr($raw, $size)];
}
[$code, $headers, $html] = demo_request('/');
demo_check($code === 200 && str_contains($html, 'Demostración ·'), 'Portada con advertencia demo');
demo_check(str_contains(strtolower($headers), 'x-robots-tag: noindex, nofollow'), 'Sin indexación');
demo_check(!preg_match('~https?://(?:localhost|127\.0\.0\.1)~i', $html), 'Sin recursos a localhost');
demo_check(substr_count($html, '<tr><th scope="row">') === 7, 'Siete planes en portada');
demo_check(str_contains($html, 'data-agenda') && str_contains($html, 'agenda-day-7'), 'Agenda y días presentes');
preg_match_all('/\sid="([^"]+)"/', $html, $ids);
preg_match_all('/href="#([^"]+)"/', $html, $anchors);
foreach ($anchors[1] as $anchor) demo_check(in_array($anchor, $ids[1], true), 'Ancla existente');
[$code, $headers, $checkout] = demo_request('/checkout.php');
demo_check($code === 200 && str_contains($checkout, 'Compra deshabilitada en demostración'), 'Checkout solo vista previa');
demo_check(!str_contains(strtolower($headers), 'set-cookie:'), 'Demo no abre sesión ni genera cookie de propietario');
demo_check(!str_contains($headers, 'form-action \'self\' https://webpay3gint'), 'Sin envío de formulario a Transbank');
demo_check(str_contains($checkout, 'type="submit" disabled'), 'Compra deshabilitada en interfaz');
demo_check(substr_count($checkout, '<option value=') === 7, 'Selector conserva siete planes');
demo_check(!preg_match('~https?://(?:localhost|127\.0\.0\.1)~i', $checkout), 'Checkout sin URLs a localhost');
foreach ([8 => 45000, 12 => 50000, 16 => 55000, 20 => 60000, 24 => 65000, 28 => 70000, 32 => 75000] as $classes => $amount) {
    [$code, , $summary] = demo_request('/checkout.php?plan=full-day-' . $classes);
    demo_check($code === 200 && str_contains($summary, '<dd>' . $classes . '</dd>') && str_contains($summary, '$' . number_format($amount, 0, ',', '.')), 'Resumen de plan ' . $classes);
}
$assets = [];
preg_match_all('/(?:src|href)="(assets\/[^"?]+)"/', $html . $checkout, $assets);
foreach (array_unique($assets[1]) as $asset) {
    [$code, , $body] = demo_request('/' . $asset);
    demo_check($code === 200 && strlen($body) > 0, 'Recurso público: ' . $asset);
}
foreach (['/config/site.php', '/config/checkout.local.php', '/config/demo.local.php', '/.env', '/.git/config',
    '/.htaccess', '/app/bootstrap.php', '/vendor/autoload.php', '/tests/smoke.php', '/README.md',
    '/bin/demo-router.php', '/storage/demo-processes.json', '/storage/demo-php-server.log',
    '/storage/mysql-admin.php', '/storage/mysql-checkout/auto.cnf', '/database/migrations/001_checkout.sql',
    '/assets/', '/checkout.php/extra', '/%2e%2e/config/checkout.local.php', '/assets/images/%2e%2e/%2e%2e/config/site.php'] as $private) {
    $allowedDenials = str_contains($private, '%2e') ? [400, 403, 404] : [404];
    demo_check(in_array(demo_request($private)[0], $allowedDenials, true), 'Privado bloqueado: ' . $private);
}
foreach (['buy', 'pay', 'check', 'new', 'return', 'transfer', 'send-email'] as $action) {
    demo_check(demo_request('/checkout.php?action=' . $action, 'POST')[0] === 403, 'POST bloqueado: ' . $action);
    demo_check(demo_request('/checkout.php?action=' . $action)[0] === 403, 'GET operativo bloqueado: ' . $action);
}
demo_check(demo_request('/checkout.php?order=referencia-forzada')[0] === 403, 'Sin acceso a órdenes anteriores');
demo_check(demo_request('/checkout.php', 'PUT')[0] === 403, 'PUT bloqueado');
demo_check(demo_request('/', 'POST')[0] === 403, 'POST de portada bloqueado');
echo "OK: $checks comprobaciones de demo, recursos, navegación y bloqueo de operaciones/privados en $base\n";
