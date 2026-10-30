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

function program_consult_url(?string $whatsapp, string $message): string
{
    if (!https_url($whatsapp)) return '#contacto';
    $parts = parse_url($whatsapp);
    // Solo el enlace directo con número confirmado; nunca construir un número.
    if (strtolower($parts['host'] ?? '') !== 'wa.me'
        || !preg_match('~^/([1-9][0-9]{6,14})/?$~D', $parts['path'] ?? '', $match)) {
        return '#contacto';
    }
    return 'https://wa.me/' . $match[1] . '?text=' . rawurlencode($message);
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
    $heroImageSize = $heroImage ? getimagesize($publicRoot . '/' . $heroImage) : false;
    if (!$heroImageSize) $heroImage = null;
    $heroSources = [];
    foreach ($hero['image_variants'] ?? [] as $variant) {
        if (!image_path($variant) || !is_file($publicRoot . '/' . $variant)) continue;
        $size = getimagesize($publicRoot . '/' . $variant);
        // Los derivados deben conservar la proporción de la fotografía principal.
        if ($size && $heroImageSize && abs($size[0] / $size[1] - $heroImageSize[0] / $heroImageSize[1]) < .002) {
            $heroSources[$size[0]] = $variant . ' ' . $size[0] . 'w';
        }
    }
    ksort($heroSources);
    $position = static fn (string $value): string =>
        preg_match('~^(?:100|[0-9]{1,2})% (?:100|[0-9]{1,2})%$~D', $value) ? $value : '50% 50%';
    $heroPosition = $position($hero['image_position'] ?? '50% 50%');
    $heroMobilePosition = $position($hero['image_position_mobile'] ?? $heroPosition);
    $programs = [];
    foreach ($site['programs'] as $program) {
        $image = image_path($program['image'] ?? '');
        $size = $image && is_file($publicRoot . '/' . $image) ? getimagesize($publicRoot . '/' . $image) : false;
        $program['image'] = $size ? $image : null;
        $program['image_size'] = $size;
        $program['image_position'] = $position($program['image_position'] ?? '50% 50%');
        $program['image_position_mobile'] = $position($program['image_position_mobile'] ?? $program['image_position']);
        $program['consult_url'] = program_consult_url($links['whatsapp_url'], $program['message']);
        $programs[] = $program;
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
