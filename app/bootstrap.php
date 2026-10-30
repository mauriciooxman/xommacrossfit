<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Solo HTTPS en enlaces externos. Rechaza esquemas ejecutables y URLs ambiguas.
function https_url(?string $value): ?string
{
    if (!$value || !filter_var($value, FILTER_VALIDATE_URL)) {
        return null;
    }
    $parts = parse_url($value);
    return ($parts['scheme'] ?? '') === 'https' && !isset($parts['user']) && !isset($parts['pass']) ? $value : null;
}

function image_path(string $value): ?string
{
    return preg_match('~^assets/images/[a-zA-Z0-9_-]+\.(?:webp|png|jpe?g)$~D', $value) ? $value : null;
}

function load_site(string $root): array
{
    $site = require $root . '/config/site.php';
    // Futura configuración privada; el ejemplo y las instrucciones están en README.
    if (is_file($root . '/config/local.php')) {
        $site = array_replace_recursive($site, require $root . '/config/local.php');
    }
    date_default_timezone_set($site['timezone']);
    return $site;
}

function render_home(array $site, ?string $publicRoot = null): void
{
    $publicRoot ??= dirname(__DIR__) . '/public';
    $contact = $site['contact'];
    $links = [];
    foreach (['whatsapp_url', 'maps_url', 'instagram_url', 'facebook_url', 'boxmagic_url'] as $key) {
        $links[$key] = https_url($contact[$key] ?? null);
    }
    $email = filter_var($contact['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null;
    $canonical = https_url($site['canonical_url'] ?? null);
    $hero = $site['hero'];
    $heroImage = image_path($hero['image'] ?? '');
    if (!$heroImage || !is_file($publicRoot . '/' . $heroImage)) {
        $heroImage = null;
    }
    $heroVideo = $hero['video'] ?? null;
    if (!$heroImage || !$heroVideo || !preg_match('~^assets/videos/[a-zA-Z0-9_-]+\.(?:mp4|webm)$~D', $heroVideo)
        || !is_file($publicRoot . '/' . $heroVideo)) {
        $heroVideo = null;
    }
    $gallery = array_values(array_filter($site['gallery'], static fn (array $item): bool =>
        image_path($item['src'] ?? '') !== null && ($item['width'] ?? 0) > 0 && ($item['height'] ?? 0) > 0
    ));
    require __DIR__ . '/views/home.php';
}
