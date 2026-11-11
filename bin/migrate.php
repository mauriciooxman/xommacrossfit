<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
require dirname(__DIR__) . '/app/checkout.php';
try {
    $db = checkout_db(require dirname(__DIR__) . '/config/checkout.php');
    $db->exec('CREATE TABLE IF NOT EXISTS checkout_migrations (version VARCHAR(100) PRIMARY KEY, applied_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)) ENGINE=InnoDB');
    foreach (glob(dirname(__DIR__) . '/database/migrations/*.sql') as $file) {
        $version = basename($file); $q = $db->prepare('SELECT version FROM checkout_migrations WHERE version=?'); $q->execute([$version]);
        if ($q->fetchColumn()) continue;
        // DDL MySQL hace commit implícito; cada CREATE es idempotente para recuperarse de interrupciones.
        foreach (explode(';', file_get_contents($file)) as $sql) if (trim($sql) !== '') $db->exec($sql);
        $db->prepare('INSERT INTO checkout_migrations (version) VALUES (?)')->execute([$version]);
        echo "Aplicada: $version\n";
    }
    echo "Migraciones al día.\n";
} catch (Throwable $e) { fwrite(STDERR, "No se pudo migrar. Revisa MySQL/MariaDB y config/checkout.local.php. Tipo: " . get_class($e) . "\n"); exit(1); }
