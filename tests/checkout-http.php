<?php
declare(strict_types=1);
// Requiere servidor local: php -S 127.0.0.1:8080 -t public
require dirname(__DIR__) . '/app/checkout.php';
$checks = 0;
function assert_http(bool $ok, string $label): void { global $checks; $checks++; if (!$ok) throw new RuntimeException('FAIL: ' . $label); }
$cookies = tempnam(dirname(__DIR__) . '/storage', 'http-cookies-');
$reference = null;
function request_checkout(string $path, ?array $post = null, bool $cookie = true): array {
    global $cookies;
    $curl = curl_init('http://127.0.0.1:8080/' . $path);
    $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10];
    if ($cookie) $options += [CURLOPT_COOKIEFILE => $cookies, CURLOPT_COOKIEJAR => $cookies];
    if ($post !== null) $options += [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($post)];
    curl_setopt_array($curl, $options); $raw = curl_exec($curl);
    if ($raw === false) throw new RuntimeException('Servidor local no accesible.');
    $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE); curl_close($curl);
    return [$code, substr($raw, 0, $size), substr($raw, $size)];
}
try {
    [$code, $headers, $html] = request_checkout('checkout.php');
    assert_http($code === 200, 'Formulario disponible');
    assert_http(str_contains(strtolower($headers), 'cache-control: no-store, private') && str_contains($headers, 'https://webpay3gint.transbank.cl'), 'Privacidad y CSP de integración');
    assert_http(str_contains($html, 'value="transfer" disabled'), 'Transferencia deshabilitada en HTML');
    assert_http(substr_count($html, '<option value=') === 7, 'Siete planes disponibles');
    preg_match('/name="csrf" value="([a-f0-9]+)"/', $html, $csrf);
    preg_match('/name="nonce" value="([a-f0-9]+)"/', $html, $nonce);
    $form = ['csrf' => $csrf[1], 'nonce' => $nonce[1], 'plan' => 'full-day-8', 'name' => '<script>alert(1)</script>', 'email' => 'http-test@example.invalid', 'student_type' => 'new', 'method' => 'webpay', 'price' => '1'];
    assert_http(request_checkout('checkout.php?action=buy', array_replace($form, ['csrf' => 'incorrecto']))[0] === 422, 'CSRF inválido rechazado');
    [$code, , $html] = request_checkout('checkout.php?action=buy', array_replace($form, ['method' => 'transfer']));
    assert_http($code === 422 && str_contains($html, 'Transferencia no disponible'), 'Transferencia forzada rechazada en backend');
    [$code, $headers] = request_checkout('checkout.php?action=buy', $form);
    assert_http($code === 303, 'POST redirige al resultado');
    preg_match('/Location: (checkout.php\?order=([a-f0-9]+))/i', $headers, $location); $reference = $location[2];
    [$code, $repeatHeaders] = request_checkout('checkout.php?action=buy', $form);
    assert_http($code === 303 && str_contains($repeatHeaders, $reference), 'POST duplicado retorna la misma orden');
    [$code, , $html] = request_checkout($location[1]);
    assert_http($code === 200 && str_contains($html, '$45.000') && !str_contains($html, '<script>'), 'Resultado usa monto del servidor y escapa contenido');
    assert_http(str_contains($html, 'Pendiente de confirmación del gimnasio'), 'Activación explícitamente pendiente');
    [$code, , $html] = request_checkout($location[1], null, false);
    assert_http($code === 404 && !str_contains($html, $reference) && !str_contains($html, 'http-test@example.invalid'), 'Otro navegador no accede a orden');
    assert_http(request_checkout('checkout.php?plan%5B%5D=8')[0] === 400, 'Parámetros array rechazados');
    assert_http(request_checkout('checkout.php?action=return&attempt=desconocido', ['token_ws' => 'inventado'])[0] === 422, 'Retorno inventado rechazado');
    echo "OK: $checks comprobaciones HTTP locales. Sin operaciones Transbank.\n";
} finally {
    if ($reference) {
        $db = checkout_db(require dirname(__DIR__) . '/config/checkout.php');
        // Solo la orden sintética creada en esta ejecución y sin intentos de pago.
        $db->prepare("DELETE FROM checkout_orders WHERE reference=? AND customer_email='http-test@example.invalid' AND NOT EXISTS (SELECT 1 FROM checkout_payment_attempts WHERE order_id=checkout_orders.id)")->execute([$reference]);
    }
    unlink($cookies);
}
