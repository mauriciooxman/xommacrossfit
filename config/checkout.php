<?php
declare(strict_types=1);

// Configuración privada opcional, fuera de public/ e ignorada por Git.
$config = [
    'base_url' => 'http://127.0.0.1:8080',
    'environment' => 'integration',
    'db' => ['dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=xommafitnes;charset=utf8mb4', 'user' => '', 'password' => ''],
    'bank' => ['holder' => '', 'rut' => '', 'bank' => '', 'account_type' => '', 'account_number' => '', 'email' => ''],
];
if (is_file(__DIR__ . '/checkout.local.php')) {
    $config = array_replace_recursive($config, require __DIR__ . '/checkout.local.php');
}
if ($config['environment'] !== 'integration') {
    throw new RuntimeException('Esta entrega solo permite integración.');
}
return $config;
