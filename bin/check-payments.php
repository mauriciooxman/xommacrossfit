<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
require dirname(__DIR__) . '/app/checkout.php';
try {
    $config = require dirname(__DIR__) . '/config/checkout.php'; $db = checkout_db($config);
    $service = new CheckoutService($db, new WebpayGateway(), $config);
    // Consulta persistida independiente del navegador; no crea nuevos cobros.
    $rows = $db->query("SELECT a.id,o.reference FROM checkout_payment_attempts a JOIN checkout_orders o ON o.id=a.order_id WHERE a.token IS NOT NULL AND a.status IN ('initialized','verification_pending') ORDER BY COALESCE(a.checked_at,a.created_at),a.id LIMIT 100")->fetchAll();
    foreach ($rows as $row) $service->reconcile($row['reference'], (int) $row['id']);
    echo 'Intentos consultados: ' . count($rows) . ". Revisar estados persistidos; los inciertos permanecen pendientes.\n";
} catch (Throwable $e) { fwrite(STDERR, "No se pudo consultar. Revisa conexión y configuración local.\n"); exit(1); }
