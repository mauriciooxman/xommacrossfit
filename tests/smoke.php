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
check(str_contains($page, 'href="https://www.instagram.com/xommafitness/"'), 'Instagram confirmado visible');
check(str_contains($page, '>Consultar por Instagram <span'), 'Instagram como acción cuando faltan WhatsApp y correo');
check(!str_contains($page, 'Pronto compartiremos nuestros canales oficiales'), 'Sin mensaje de canales pendientes cuando existe Instagram');
$contactFixture = $site;
$contactFixture['contact']['instagram_url'] = null;
$contactFixture['contact']['facebook_url'] = 'https://www.facebook.com/example';
check(str_contains(html($contactFixture), '>Consultar por Facebook <span'), 'Facebook como alternativa confirmada');
$contactFixture['contact']['facebook_url'] = null;
check(str_contains(html($contactFixture), 'Pronto compartiremos nuestros canales oficiales'), 'Sin botón ficticio cuando no hay canales');
$contactFixture['contact']['instagram_url'] = 'javascript:alert(1)';
check(!str_contains(html($contactFixture), '>Consultar por Instagram <span'), 'Instagram inseguro no genera acción');
$contactFixture = $site;
$contactFixture['contact']['email'] = 'test@example.org';
check(str_contains(html($contactFixture), 'class="button" href="mailto:test@example.org"'), 'Correo tiene prioridad sobre redes');
$contactFixture['contact']['whatsapp_url'] = 'https://wa.me/15555550100';
check(str_contains(html($contactFixture), '>Hablemos por WhatsApp <span'), 'WhatsApp mantiene prioridad sobre correo y redes');
foreach (['whatsapp_url', 'maps_url', 'facebook_url', 'boxmagic_url', 'boxmagic_store_url'] as $pendingChannel) {
    check($site['contact'][$pendingChannel] === null, 'Canal pendiente sin publicar: ' . $pendingChannel);
}
check(!str_contains($page, 'id="equipo"'), 'Sin entrenadores inventados');
check(!str_contains($page, 'class="floating-contact"'), 'Sin WhatsApp sin confirmar');
check_links($page);

// Accesos Boxmagic independientes: datos temporales solo en memoria.
function plans_html(array $site): string
{
    preg_match('~<section id="planes".*?</section>~s', html($site), $match);
    return $match[0] ?? '';
}
$plansPage = plans_html($site);
check(str_contains($plansPage, '<h3 id="plans-title">Planes mensuales Full Day</h3>'), 'Título comercial Full Day');
check(str_contains($plansPage, '<h2>Planes y horarios</h2>'), 'Título directo de la sección');
check(str_contains($plansPage, '<caption>Precios mensuales en pesos chilenos (CLP)</caption>'), 'Unidad de precios indicada una sola vez');
preg_match_all('~<tr><th scope="row">([0-9]+) clases</th><td>(\$[0-9.]+)</td></tr>~', $plansPage, $planRows, PREG_SET_ORDER);
check(array_map(static fn (array $row): array => [$row[1], $row[2]], $planRows) === [
    ['8', '$45.000'], ['12', '$50.000'], ['16', '$55.000'], ['20', '$60.000'],
    ['24', '$65.000'], ['28', '$70.000'], ['32', '$75.000'],
], 'Siete pares exactos y ordenados en el HTML');
foreach ($site['plans'] as $plan) {
    check(is_int($plan['classes']) && is_int($plan['price']), 'Clases y CLP almacenados como enteros');
}
check(str_contains($plansPage, 'aria-labelledby="plans-title" aria-describedby="plans-condition"'), 'Tabla asociada a título y condición');
check(str_contains($plansPage, '<p id="plans-condition" class="plans-condition">Las clases son mensuales y no se acumulan para el mes siguiente.</p>'), 'Condición exacta bajo la tabla');
check(strpos($plansPage, '</table>') < strpos($plansPage, 'id="plans-condition"') && strpos($plansPage, 'id="plans-condition"') < strpos($plansPage, '>Consultar planes'), 'Tabla, condición y consulta en orden');
check(substr_count($plansPage, '>Consultar planes <span') === 1, 'Consulta única bajo la tabla');
$emptyPlans = $site;
$emptyPlans['plans'] = [];
check(!str_contains(plans_html($emptyPlans), 'class="plans-table"'), 'Sin tabla vacía cuando faltan planes');
check(str_contains($plansPage, 'class="plans-action plans-action--secondary" href="https://www.instagram.com/xommafitness/">Consultar planes'), 'Consulta secundaria al contacto confirmado');
check(str_contains($plansPage, 'href="checkout.php" aria-describedby="purchase-test-note">Comprar plan <span'), 'Compra conserva checkout y nota accesible en integración');
check(str_contains($plansPage, '>Modo de prueba · sin cobros reales</p>'), 'Advertencia independiente en entorno de integración');
check(str_contains($plansPage, 'class="plans-action plans-action--secondary" href="https://www.instagram.com/xommafitness/">Consultar horarios'), 'Horarios conserva contacto y estilo secundario');
check(!str_contains($plansPage, 'Comprar o renovar plan') && !str_contains($plansPage, 'Agendar clase'), 'Sin acciones Boxmagic pendientes');
check(!str_contains($plansPage, 'plan-row'), 'Sin tarjetas de planes vacías');
check(str_contains($page, 'href="#planes">Planes y horarios'), 'Navegación conserva ancla');
foreach ([[true, false], [false, true], [true, true]] as [$store, $booking]) {
    $access = $site;
    $access['contact']['boxmagic_store_url'] = $store ? 'https://example.org/store?box=1&source=web' : null;
    $access['contact']['boxmagic_url'] = $booking ? 'https://example.org/booking' : null;
    $accessPage = plans_html($access);
    check(str_contains($accessPage, 'Comprar o renovar plan') === $store, 'Tienda independiente');
    check(str_contains($accessPage, 'Agendar clase') === $booking, 'Reservas independientes');
    check(str_contains($accessPage, 'href="https://www.instagram.com/xommafitness/">Consultar planes'), 'Consultas se mantienen con Boxmagic');
    if ($store) check(str_contains($accessPage, 'class="button" href="https://example.org/store?box=1&amp;source=web">Comprar o renovar plan'), 'Compra principal con URL escapada');
    if ($booking) check(str_contains($accessPage, 'href="https://example.org/booking">Agendar clase'), 'Reserva con destino propio');
    check_links(html($access));
}
foreach (['boxmagic_store_url' => 'Comprar o renovar plan', 'boxmagic_url' => 'Agendar clase'] as $key => $label) {
    foreach (['', 'javascript:alert(1)', 'http://example.org', '//example.org', 'https://user:pass@example.org', "https://example.org\nmal"] as $badUrl) {
        $access = $site;
        $access['contact'][$key] = $badUrl;
        check(!str_contains(plans_html($access), $label), 'URL inválida omite acción: ' . $key);
    }
}
foreach ([
    ['whatsapp_url', 'https://wa.me/15555550100', 'https://wa.me/15555550100'],
    ['email', 'test@example.org', 'mailto:test@example.org'],
    ['facebook_url', 'https://www.facebook.com/example', 'https://www.facebook.com/example'],
] as [$key, $value, $destination]) {
    $access = $site;
    $access['contact']['instagram_url'] = null;
    $access['contact'][$key] = $value;
    check(str_contains(plans_html($access), 'href="' . $destination . '">Consultar planes'), 'Canal compartido: ' . $key);
}
$schedule = $site;
check($site['schedule'] === [
    ['days' => 'Martes a viernes', 'hours' => '08:00', 'program' => 'CrossFit'],
    ['days' => 'Martes a viernes', 'hours' => '09:00', 'program' => 'Halterofilia'],
    ['days' => 'Martes a viernes', 'hours' => '10:00', 'program' => 'CrossFit'],
    ['days' => 'Lunes a viernes', 'hours' => '18:00', 'program' => 'CrossFit'],
    ['days' => 'Lunes', 'hours' => '19:00', 'program' => 'CrossFit'],
    ['days' => 'Martes a viernes', 'hours' => '19:00', 'program' => 'Halterofilia'],
    ['days' => 'Lunes, miércoles y viernes', 'hours' => '20:00', 'program' => 'Fatburn'],
    ['days' => 'Martes y jueves', 'hours' => '20:00', 'program' => 'CrossFit'],
    ['days' => 'Lunes a jueves', 'hours' => '21:00', 'program' => 'CrossFit'],
    ['days' => 'Sábados, domingos y feriados', 'hours' => '10:00', 'program' => 'CrossFit'],
], 'Horarios transcritos de la imagen sin completar celdas vacías');
foreach ($site['schedule'] as $slot) {
    check(str_contains($plansPage, '<time>' . e($slot['hours']) . '</time><span>' . e($slot['program']) . '</span>'), 'Hora y disciplina de la fuente presentes en HTML');
}
$schedule['schedule'] = [['days' => 'Lunes', 'hours' => '18:30', 'program' => 'Disciplina de prueba']];
check(str_contains(plans_html($schedule), '<time>18:30</time><span>Disciplina de prueba</span>'), 'Agenda responde a cambios de configuración');
$agenda = schedule_by_day($site['schedule']);
$expectedDays = [
    [['18:00', 'CrossFit'], ['19:00', 'CrossFit'], ['20:00', 'Fatburn'], ['21:00', 'CrossFit']],
    [['08:00', 'CrossFit'], ['09:00', 'Halterofilia'], ['10:00', 'CrossFit'], ['18:00', 'CrossFit'], ['19:00', 'Halterofilia'], ['20:00', 'CrossFit'], ['21:00', 'CrossFit']],
    [['08:00', 'CrossFit'], ['09:00', 'Halterofilia'], ['10:00', 'CrossFit'], ['18:00', 'CrossFit'], ['19:00', 'Halterofilia'], ['20:00', 'Fatburn'], ['21:00', 'CrossFit']],
    [['08:00', 'CrossFit'], ['09:00', 'Halterofilia'], ['10:00', 'CrossFit'], ['18:00', 'CrossFit'], ['19:00', 'Halterofilia'], ['20:00', 'CrossFit'], ['21:00', 'CrossFit']],
    [['08:00', 'CrossFit'], ['09:00', 'Halterofilia'], ['10:00', 'CrossFit'], ['18:00', 'CrossFit'], ['19:00', 'Halterofilia'], ['20:00', 'Fatburn']],
    [['10:00', 'CrossFit']], [['10:00', 'CrossFit']], [['10:00', 'CrossFit']],
];
foreach ($agenda as $index => $day) {
    check(array_map(static fn (array $slot): array => [$slot['hours'], $slot['program']], $day['classes']) === $expectedDays[$index], 'Agenda exacta y ordenada: ' . $day['name']);
    preg_match('~<div class="agenda-day" id="agenda-day-' . $index . '">(.*?)</div>~s', $plansPage, $dayHtml);
    preg_match_all('~<time>([^<]+)</time><span>([^<]+)</span>~', $dayHtml[1] ?? '', $rows, PREG_SET_ORDER);
    check(array_map(static fn (array $row): array => [$row[1], $row[2]], $rows) === $expectedDays[$index], 'HTML del día coincide con agenda: ' . $day['name']);
    check(!str_contains($dayHtml[0] ?? '', ' hidden'), 'Día visible sin JavaScript: ' . $day['name']);
}
check(str_contains($plansPage, 'aria-label="Feriados"'), 'Feriados explícitos en el selector');
$finalConfig = require dirname(__DIR__) . '/config/site.php';
check($finalConfig['contact']['boxmagic_url'] === null && $finalConfig['contact']['boxmagic_store_url'] === null, 'Configuración final sin URLs de prueba');

// Disciplinas confirmadas: consultas útiles sin número ni imágenes ficticias.
check(substr_count($page, 'class="program"') === 4, 'Cuatro disciplinas en el bloque existente');
check(substr_count($page, 'id="entrenamiento"') === 1, 'Un único bloque de entrenamiento');
check(!str_contains($page, 'class="program-media"'), 'Sin huecos para fotos pendientes');
foreach ($site['programs'] as $program) {
    check(str_contains($page, 'href="#contacto" class="text-link program-cta">' . e($program['cta'])), 'Consulta disponible: ' . $program['name']);
    $url = program_consult_url('https://wa.me/15555550100?text=anterior', $program['message']);
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    check(($query['text'] ?? '') === $program['message'], 'Mensaje específico codificado: ' . $program['name']);
}
foreach ([null, 'javascript:alert(1)', 'https://example.org/15555550100', 'https://wa.me/sin-numero'] as $whatsapp) {
    check(program_consult_url($whatsapp, 'Consulta') === '#contacto', 'Sin WhatsApp válido se usa contacto');
}
$programPhoto = $site;
$programPhoto['programs'][0]['image'] = 'assets/images/logo.webp';
$programPhoto['programs'][0]['image_alt'] = 'Foto "de prueba"';
$programPhoto['programs'][0]['image_position_mobile'] = '25% 80%';
$programPhoto['programs'][1]['image'] = '../../config/site.php';
$programPhoto['programs'][2]['image'] = 'https://example.org/foto.jpg';
$programPhoto['programs'][3]['image'] = 'assets/images/no-existe.webp';
$programPhotoPage = html($programPhoto);
check(substr_count($programPhotoPage, 'class="program-media"') === 1, 'Solo fotografía local existente');
check(str_contains($programPhotoPage, 'data-photo-position-mobile="25% 80%"'), 'Enfoque móvil por disciplina');
check(str_contains($programPhotoPage, 'alt="Foto &quot;de prueba&quot;"'), 'Escape de descripción de disciplina');
check(str_contains($programPhotoPage, 'loading="lazy" decoding="async"'), 'Fotos secundarias diferidas');
check_links($programPhotoPage);

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
$fixture['plans'] = [['classes' => 1, 'price' => 1234]];
$fixture['plans_title'] = 'Prueba <b>escape</b>';
$fixture['plans_condition'] = 'Condición <script>prueba</script>';
$fixture['schedule'] = [['days' => 'Lunes', 'hours' => '18:30', 'program' => 'Prueba']];
$fixture['gallery'] = [['src' => 'assets/images/logo.webp', 'alt' => 'Prueba "escape"', 'width' => 600, 'height' => 144]];
$configured = html($fixture);
check(str_contains($configured, '&lt;script&gt;inseguro&lt;/script&gt;'), 'Escape de nombre');
check(!str_contains($configured, '<svg'), 'Escape de biografía');
check(str_contains($configured, 'id="equipo"'), 'Equipo confirmado visible');
check(str_contains($configured, '<time>18:30</time><span>Prueba</span>'), 'Horarios configurados visibles');
check(str_contains($configured, 'Prueba &lt;b&gt;escape&lt;/b&gt;'), 'Escape del título de planes');
check(str_contains($configured, 'Condición &lt;script&gt;prueba&lt;/script&gt;'), 'Escape de condición de planes');
check(str_contains($configured, '<td>$1.234</td>'), 'Precio entero formateado al renderizar');
check(!str_contains($configured, 'id="galeria"'), 'Sección de identidad retirada aunque haya fotos configuradas');
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
$withoutPhoto = $site;
$withoutPhoto['hero']['image'] = null;
check(str_contains(html($withoutPhoto), 'hero-graphic'), 'Hero gráfico sin material real');
check(str_contains($page, 'src="assets/images/box-xomma.jpeg"'), 'Nueva fotografía del box configurada');
check(!str_contains($page, 'comunidad-xomma-'), 'Sin variantes antiguas de comunidad en portada');
check(str_contains($page, 'width="900" height="1600"'), 'Dimensiones reales de la fotografía');
check(str_contains($page, 'fetchpriority="high" loading="eager"'), 'Foto principal sin carga diferida');
check(str_contains($page, 'data-photo-position-mobile="50% 65%"'), 'Enfoque móvil configurado');
check(str_contains($page, 'href="assets/images/horarios-xomma.jpeg" target="_blank" rel="noopener"'), 'Afiche secundario abre sin abandonar la página');
check(!str_contains($page, 'src="assets/images/horarios-xomma.jpeg"'), 'Sin afiche gigante ni descarga de imagen de agenda');
check(str_contains($page, 'Ver afiche de horarios'), 'Acceso secundario al afiche');
$invalidSchedule = $site;
$invalidSchedule['schedule_image'] = '../../config/site.php';
check(!str_contains(html($invalidSchedule), 'class="schedule-poster"'), 'Imagen privada de horarios rechazada');
check(str_contains(html($invalidSchedule), '<span>Fatburn</span>'), 'Agenda disponible si falta imagen válida');
$invalidFocus = $site;
$invalidFocus['hero']['image_position_mobile'] = '0%;background:url(https://example.org)';
$invalidFocus['hero']['image_variants'] = ['../../config/site.php', 'https://example.org/photo.jpg'];
$invalidFocusPage = html($invalidFocus);
check(!str_contains($invalidFocusPage, 'background:url'), 'Enfoque solo admite porcentajes');
check(!str_contains($invalidFocusPage, 'srcset='), 'Fuentes externas y privadas rechazadas');
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
