<?php
// Copiar a checkout.local.php. Nunca publicar ni versionar ese archivo.
return [
    'base_url' => 'http://127.0.0.1:8080', // Sin barra final; no derivar del encabezado Host.
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=xommafitnes;charset=utf8mb4',
        'user' => '',
        'password' => '',
    ],
    'bank' => [
        'holder' => '', 'rut' => '', 'bank' => '', 'account_type' => '',
        'account_number' => '', 'email' => '',
    ],
];
