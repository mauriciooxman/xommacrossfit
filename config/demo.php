<?php
declare(strict_types=1);

$demo = ['enabled' => false];
if (is_file(__DIR__ . '/demo.local.php')) {
    $demo = array_replace($demo, require __DIR__ . '/demo.local.php');
}
return $demo;
