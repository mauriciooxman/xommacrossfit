<?php
declare(strict_types=1);
// Operación real en INTEGRACIÓN: creación y consulta, sin ingresar tarjetas ni confirmar un pago.
if (PHP_SAPI !== 'cli' || !in_array('--create', $argv, true)) exit("Usa php tests/transbank-integration.php --create para crear una transacción real de integración.\n");
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/checkout.php';
$config = require dirname(__DIR__) . '/config/checkout.php';
$config['db']['dsn'] = preg_replace('/dbname=[^;]+/', 'dbname=xommafitnes_checkout_test', $config['db']['dsn']);
try {
    $db = checkout_db($config); $service = new CheckoutService($db, new WebpayGateway(), $config);
    $owner = bin2hex(random_bytes(32));
    $order = $service->createOrder(load_site(dirname(__DIR__)), ['plan' => 'full-day-8', 'name' => 'Prueba integración', 'email' => 'integration@example.invalid', 'student_type' => 'new', 'method' => 'webpay'], $owner, bin2hex(random_bytes(32)));
    $attempt = $service->start($order['reference'], $owner);
    if (!$attempt || !$attempt['token']) throw new RuntimeException('No se recibió token de integración.');
    $service->reconcile($order['reference'], (int) $attempt['id']);
    $final = $service->order($order['reference'], $owner);
    if ($final['payment_status'] !== 'pending' || $final['activation_status'] !== 'pending') throw new RuntimeException('Estado inesperado.');
    $saved = $service->attempts((int) $order['id'])[0];
    if ($saved['gateway_status'] !== 'INITIALIZED') throw new RuntimeException('No se verificó estado remoto.');
    echo "OK: Transbank integración REAL: create + status INITIALIZED; intento/token persistidos. Sin commit, tarjeta ni aprobación.\n";
} catch (Throwable $e) { fwrite(STDERR, "No se verificó integración: " . get_class($e) . ". Revisa conectividad y estados persistidos.\n"); exit(1); }
