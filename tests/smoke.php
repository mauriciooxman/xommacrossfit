<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
$checks = 0;
function check(bool $condition, string $label): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: $label\n");
        exit(1);
    }
}
function html(array $site): string
{
    ob_start();
    render_home($site);
    return (string) ob_get_clean();
}
function check_links(string $page): void
{
    preg_match_all('/\sid="([^"]+)"/', $page, $ids);
    check(count($ids[1]) === count(array_unique($ids[1])), 'IDs únicos');
    preg_match_all('/href="#([^"]*)"/', $page, $anchors);
    foreach ($anchors[1] as $anchor) {
        check($anchor !== '' && in_array($anchor, $ids[1], true), 'Anchor existente: ' . $anchor);
    }
    preg_match_all('/(?:src|href)="(assets\/[^"?]+)"/', $page, $assets);
    foreach ($assets[1] as $asset) {
        check(is_file(dirname(__DIR__) . '/public/' . $asset), 'Asset existente: ' . $asset);
    }
}
$site = load_site(dirname(__DIR__));
$page = html($site);
check(str_contains($page, 'lang="es-CL"'), 'Idioma');
check(substr_count($page, '<h1 ') === 1, 'Un H1');
check(!str_contains($page, '<form'), 'Sin recepción ficticia');
check(!str_contains($page, 'rel="canonical"'), 'Sin dominio supuesto');
check(!str_contains($page, 'href="https://'), 'Sin canales sin confirmar');
check(!str_contains($page, 'id="equipo"'), 'Sin entrenadores inventados');
check(!str_contains($page, 'class="floating-contact"'), 'Sin WhatsApp sin confirmar');
check_links($page);

// Fixtures sintéticos solo en memoria: no son datos comerciales del gimnasio.
$fixture = $site;
$fixture['name'] = '<script>inseguro</script>';
$fixture['canonical_url'] = 'https://example.org/box/';
foreach (['whatsapp_url', 'maps_url', 'instagram_url', 'facebook_url', 'boxmagic_url'] as $key) {
    $fixture['contact'][$key] = 'https://example.org/' . $key;
}
$fixture['contact']['email'] = 'test@example.org';
$fixture['contact']['address'] = 'Dirección sintética <b>prueba</b>';
$fixture['coaches'] = [['name' => 'Prueba', 'bio' => '<svg onload=alert(1)>']];
$fixture['plans'] = [['name' => 'Prueba', 'price' => 'No comercial', 'description' => '<b>Prueba</b>']];
$fixture['schedule'] = [['days' => 'Prueba', 'hours' => 'Prueba', 'program' => 'Prueba']];
$fixture['gallery'] = [['src' => 'assets/images/logo.webp', 'alt' => 'Prueba "escape"', 'width' => 600, 'height' => 144]];
$configured = html($fixture);
check(str_contains($configured, '&lt;script&gt;inseguro&lt;/script&gt;'), 'Escape de nombre');
check(!str_contains($configured, '<svg'), 'Escape de biografía');
check(str_contains($configured, 'id="equipo"'), 'Equipo confirmado visible');
check(str_contains($configured, '<table>'), 'Horarios confirmados visibles');
check(str_contains($configured, 'class="photo-grid"'), 'Galería configurada visible');
check(str_contains($configured, 'class="floating-contact"'), 'WhatsApp configurado visible');
check(str_contains($configured, 'href="mailto:test@example.org"'), 'Correo confirmado visible');
check(str_contains($configured, 'rel="canonical"'), 'Dominio confirmado visible');
check_links($configured);
foreach (['javascript:alert(1)', 'http://example.org', 'https://user:pass@example.org', '//example.org', "https://example.org\nmal"] as $invalid) {
    check(https_url($invalid) === null, 'URL insegura rechazada');
}
$fixture['contact']['whatsapp_url'] = 'javascript:alert(1)';
$fixture['gallery'][0]['src'] = '../../config/site.php';
$invalid = html($fixture);
check(!str_contains($invalid, 'javascript:'), 'Sin URL ejecutable en vista');
check(!str_contains($invalid, 'class="photo-grid"'), 'Ruta de imagen privada rechazada');

// Medios del hero: la ausencia de material mantiene la composición de marca.
check(str_contains($page, 'hero-graphic'), 'Hero gráfico sin material real');
check(!str_contains($page, '<video'), 'Sin video ni descargas vacías por defecto');
check(!str_contains($page, 'gallery-note'), 'Sin placeholder público de fotografías');
$media = $site;
$media['hero']['image'] = 'assets/images/logo.webp';
$media['hero']['image_alt'] = 'Prueba "segura"';
$media['hero']['video'] = 'assets/videos/no-existe.mp4';
$poster = html($media);
check(str_contains($poster, 'hero-photo'), 'Imagen local confirmada habilitada');
check(str_contains($poster, 'alt="Prueba &quot;segura&quot;"'), 'Escape del texto alternativo');
check(!str_contains($poster, '<video'), 'Video inexistente no publicado');
$media['hero']['image'] = '../../config/site.php';
check(str_contains(html($media), 'hero-graphic'), 'Imagen privada no accesible como hero');

// Archivo temporal exclusivamente para verificar el HTML del reproductor, no su reproducción.
$videoDirectory = dirname(__DIR__) . '/public/assets/videos';
$createdDirectory = !is_dir($videoDirectory);
if ($createdDirectory) mkdir($videoDirectory);
$videoName = 'smoke-' . bin2hex(random_bytes(8)) . '.mp4';
$videoFile = $videoDirectory . '/' . $videoName;
try {
    file_put_contents($videoFile, 'synthetic-template-test');
    $media['hero']['image'] = 'assets/images/logo.webp';
    $media['hero']['video'] = 'assets/videos/' . $videoName;
    $video = html($media);
    check(str_contains($video, '<video'), 'Video local con respaldo habilitado');
    check(str_contains($video, 'controls muted playsinline preload="none"'), 'Reproducción manual y carga mínima');
    check(str_contains($video, 'poster="assets/images/logo.webp"'), 'Poster de respaldo');
    check(!str_contains($video, 'autoplay'), 'Sin reproducción automática');
    $media['hero']['image'] = null;
    check(!str_contains(html($media), '<video'), 'Video requiere imagen de respaldo');
} finally {
    if (is_file($videoFile)) unlink($videoFile);
    if ($createdDirectory) rmdir($videoDirectory);
}
echo "OK: $checks comprobaciones de renderizado, enlaces, datos condicionales y escape.\n";
