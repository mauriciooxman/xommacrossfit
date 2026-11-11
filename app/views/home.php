<!doctype html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($site['name']) ?> · CrossFit y comunidad</title>
    <meta name="description" content="<?= e($site['description']) ?>">
    <meta name="theme-color" content="#d5dd00">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CL">
    <meta property="og:title" content="<?= e($site['name']) ?> · CrossFit y comunidad">
    <meta property="og:description" content="<?= e($site['description']) ?>">
    <?php if ($canonical): ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php endif; ?>
    <link rel="icon" type="image/webp" href="assets/images/simbolo.webp">
    <link rel="stylesheet" href="assets/css/site.css">
    <script src="assets/js/site.js" defer></script>
</head>
<body>
<?php if ($demoMode): ?><p class="demo-banner" role="status">Demostración · Puedes explorar el sitio y el checkout. Órdenes, pagos, transferencias y envíos de correo deshabilitados.</p><?php endif; ?>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="#inicio" aria-label="<?= e($site['name']) ?>, inicio">
            <img src="assets/images/logo.webp" alt="<?= e($site['name']) ?>" width="600" height="144">
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="navigation" hidden>Menú <span aria-hidden="true">☰</span></button>
        <nav id="navigation" aria-label="Navegación principal">
            <a href="#comunidad">Comunidad</a>
            <a href="#entrenamiento">Entrenamiento</a>
            <a href="#planes">Planes y horarios</a>
            <a href="#contacto" class="nav-contact">Conversemos <span aria-hidden="true">↗</span></a>
        </nav>
    </div>
</header>
<main id="contenido">
    <section id="inicio" class="hero" aria-labelledby="hero-title">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow"><span class="status-dot" aria-hidden="true"></span> CROSSFIT · CHILE</p>
                <h1 id="hero-title"><?php foreach ($hero['title_lines'] as $line): ?><span class="hero-line"><?= e($line) ?></span><?php endforeach; ?><span class="hero-line hero-accent"><?= e($hero['title_accent']) ?></span></h1>
                <p class="hero-description"><?= e($hero['intro']) ?><br>Conoce <?= e($site['name']) ?> y encuentra tu motivo para moverte.</p>
                <div class="hero-actions">
                    <a class="button" href="<?= e($links['whatsapp_url'] ?? '#contacto') ?>"><?= $links['whatsapp_url'] ? 'Consultar por WhatsApp' : e($hero['cta']) ?> <span aria-hidden="true">↗</span></a>
                    <a class="text-link" href="#entrenamiento">Explora el entrenamiento <span aria-hidden="true">↓</span></a>
                </div>
                <p class="hero-note"><?= e($hero['note']) ?></p>
            </div>
            <?php if ($heroImage): ?>
            <div class="hero-art hero-media" data-photo-position="<?= e($heroPosition) ?>" data-photo-position-mobile="<?= e($heroMobilePosition) ?>">
                <img class="hero-photo" src="<?= e($heroImage) ?>"<?php if ($heroSources): ?> srcset="<?= e(implode(', ', $heroSources)) ?>" sizes="(max-width: 760px) min(450px, calc(100vw - 58px)), (max-width: 1200px) 44vw, 520px"<?php endif; ?> alt="<?= e($hero['image_alt']) ?>" width="<?= $heroImageSize[0] ?>" height="<?= $heroImageSize[1] ?>" fetchpriority="high" loading="eager">
                <?php if ($heroVideo): ?>
                <video class="hero-video" controls muted playsinline preload="none" poster="<?= e($heroImage) ?>" aria-label="<?= e($hero['video_label']) ?>" width="1200" height="1200">
                    <source src="<?= e($heroVideo) ?>" type="<?= str_ends_with($heroVideo, '.webm') ? 'video/webm' : 'video/mp4' ?>">
                    Tu navegador no reproduce este video. Puedes ver la imagen del gimnasio.
                </video>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="hero-art hero-graphic" aria-hidden="true">
                <span class="art-caption">EL MOVIMIENTO NOS REÚNE</span>
                <span class="art-orbit"></span>
                <img src="assets/images/simbolo.webp" alt="" width="680" height="680" fetchpriority="high">
                <div class="art-footer"><span>XOMMA / IDENTIDAD</span><span class="art-stripes"></span></div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <div class="brand-band" aria-hidden="true"><div>CONSTANCIA <span>↗</span> COMUNIDAD <span>↗</span> MOVIMIENTO <span>↗</span> XOMMAFITNES <span>↗</span></div></div>
    <section id="comunidad" class="section community">
        <div class="container split">
            <div><p class="eyebrow">01 / NUESTRA IDENTIDAD</p><h2>EL ESFUERZO<br>SE COMPARTE.</h2></div>
            <div class="section-copy"><p class="lead">Un nombre. Muchas ganas de moverse.</p><p>Somos <?= e($site['name']) ?>, un gimnasio de CrossFit en Chile. En nuestra identidad hay palabras que nos reúnen: amigos, constancia, fuerza y familia.</p></div>
        </div>
    </section>
    <section id="entrenamiento" class="section training" aria-labelledby="training-title">
        <div class="container">
            <div class="section-heading"><div><p class="eyebrow">02 / ENTRENAMIENTO</p><h2 id="training-title"><?= e($site['training_title']) ?></h2></div><p><?= e($site['training_intro']) ?></p></div>
            <div class="program-grid">
            <?php foreach ($programs as $index => $program): ?>
            <article class="program" aria-labelledby="program-<?= $index ?>">
                <?php if ($program['image']): ?>
                <div class="program-media" data-photo-position="<?= e($program['image_position']) ?>" data-photo-position-mobile="<?= e($program['image_position_mobile']) ?>">
                    <img class="program-photo" src="<?= e($program['image']) ?>" alt="<?= e($program['image_alt']) ?>" width="<?= $program['image_size'][0] ?>" height="<?= $program['image_size'][1] ?>" loading="lazy" decoding="async">
                </div>
                <?php endif; ?>
                <div class="program-body">
                    <span class="program-index" aria-hidden="true"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?> /</span>
                    <h3 id="program-<?= $index ?>"><?= e($program['name']) ?></h3>
                    <p><?= e($program['description']) ?></p>
                    <a href="<?= e($program['consult_url']) ?>" class="text-link program-cta"><?= e($program['cta']) ?> <span aria-hidden="true">↗</span></a>
                </div>
            </article>
            <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php if ($site['coaches']): ?>
    <section id="equipo" class="section"><div class="container"><p class="eyebrow">EL EQUIPO</p><h2>QUIENES NOS MUEVEN.</h2><div class="info-grid">
        <?php foreach ($site['coaches'] as $coach): ?><article class="info-panel"><h3><?= e($coach['name']) ?></h3><p><?= e($coach['bio']) ?></p></article><?php endforeach; ?>
    </div></div></section>
    <?php endif; ?>
    <section id="planes" class="section plans"><div class="container">
        <div class="section-heading"><h2>Planes y horarios</h2></div>
        <div class="planning-grid">
            <div class="rates-block"><h3 id="plans-title"><?= e($site['plans_title']) ?></h3>
                <?php if ($site['plans']): ?>
                <table class="plans-table" aria-labelledby="plans-title" aria-describedby="plans-condition">
                    <caption>Precios mensuales en pesos chilenos (CLP)</caption>
                    <tbody>
                    <?php foreach ($site['plans'] as $plan): ?>
                    <tr><th scope="row"><?= e((string) $plan['classes']) ?> clases</th><td><?= e('$' . number_format($plan['price'], 0, ',', '.')) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="plans-condition" class="plans-condition"><?= e($site['plans_condition']) ?></p>
                <?php else: ?><p><?= e($site['plans_intro']) ?></p><?php endif; ?>
                <div class="plan-actions">
                    <div class="plan-purchase">
                        <a class="plans-action plans-action--primary" href="checkout.php"<?= $checkoutEnvironment === 'integration' ? ' aria-describedby="purchase-test-note"' : '' ?>>Comprar plan <span aria-hidden="true">→</span></a>
                        <?php if ($checkoutEnvironment === 'integration'): ?><p id="purchase-test-note" class="purchase-test-note">Modo de prueba · sin cobros reales</p><?php endif; ?>
                    </div>
                    <a class="plans-action plans-action--secondary" href="<?= e($consultUrl ?? '#contacto') ?>">Consultar planes <span aria-hidden="true">↗</span></a>
                    <?php if ($links['boxmagic_store_url']): ?><a class="button" href="<?= e($links['boxmagic_store_url']) ?>">Comprar o renovar plan <span aria-hidden="true">↗</span></a><?php endif; ?>
                </div>
            </div>
            <div class="agenda-block" data-agenda><h3>Horarios de clases</h3>
                <div class="day-controls" aria-label="Día de entrenamiento" hidden>
                <?php foreach ($scheduleDays as $index => $day): ?><button type="button" id="day-control-<?= $index ?>" aria-label="<?= e($day['name']) ?>" aria-controls="agenda-day-<?= $index ?>"><?= e(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom', 'Feriados'][$index]) ?></button><?php endforeach; ?>
                </div>
                <?php foreach ($scheduleDays as $index => $day): ?>
                <div class="agenda-day" id="agenda-day-<?= $index ?>">
                    <h4><?= e($day['name']) ?></h4>
                    <?php if ($day['classes']): ?><ul class="class-list">
                    <?php foreach ($day['classes'] as $slot): ?><li><time><?= e($slot['hours']) ?></time><span><?= e($slot['program']) ?></span></li><?php endforeach; ?>
                    </ul><?php else: ?><p>No hay clases publicadas para este día.</p><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <div class="schedule-actions"><a class="plans-action plans-action--secondary" href="<?= e($consultUrl ?? '#contacto') ?>">Consultar horarios <span aria-hidden="true">↗</span></a></div>
                <?php if ($scheduleImage): ?><p class="poster-link"><a href="<?= e($scheduleImage) ?>" target="_blank" rel="noopener">Ver afiche de horarios <span class="sr-only">(abre en otra pestaña)</span> <span aria-hidden="true">↗</span></a></p><?php endif; ?>
                <?php if ($links['boxmagic_url']): ?><div class="plan-actions"><a class="button" href="<?= e($links['boxmagic_url']) ?>">Agendar clase <span aria-hidden="true">↗</span></a></div><?php endif; ?>
            </div>
        </div>
    </div></section>
    <section id="preguntas" class="section faq"><div class="container split">
        <div><p class="eyebrow">ANTES DE DAR EL PASO</p><h2>¿CONVERSAMOS?</h2><p>Algunas respuestas para empezar.</p></div>
        <div class="faq-list"><?php foreach ($site['faq'] as $item): ?><details><summary><?= e($item['question']) ?><span aria-hidden="true">+</span></summary><p><?= e($item['answer']) ?></p></details><?php endforeach; ?></div>
    </div></section>
    <section id="contacto" class="section contact"><div class="container">
        <p class="eyebrow">TU SIGUIENTE PASO</p><h2>HAGAMOS<br>QUE PASE<span>.</span></h2>
        <div class="contact-grid"><div><p class="contact-intro">Conoce el box. Pregunta por los planes.<br>Encuentra una razón para comenzar.</p>
            <?php if ($consultUrl): ?><a class="button" href="<?= e($consultUrl) ?>"><?= e($consultLabel) ?> <span aria-hidden="true">↗</span></a>
            <?php else: ?><div class="contact-status"><span class="status-dot" aria-hidden="true"></span><p>Pronto compartiremos nuestros canales oficiales de contacto.</p></div><?php endif; ?>
        </div><dl class="contact-details"><div><dt>UBICACIÓN</dt><dd><?= e($contact['address'] ?: 'Chile') ?></dd><?php if ($links['maps_url']): ?><dd><a href="<?= e($links['maps_url']) ?>">Ver ubicación en el mapa ↗</a></dd><?php endif; ?></div>
            <?php if ($email): ?><div><dt>CORREO</dt><dd><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></dd></div><?php endif; ?>
            <?php if ($links['instagram_url'] || $links['facebook_url']): ?><div><dt>ENCUÉNTRANOS</dt><dd class="social-links">
                <?php if ($links['instagram_url']): ?><a href="<?= e($links['instagram_url']) ?>">Instagram ↗</a><?php endif; ?>
                <?php if ($links['facebook_url']): ?><a href="<?= e($links['facebook_url']) ?>">Facebook ↗</a><?php endif; ?>
            </dd></div><?php endif; ?>
        </dl></div>
    </div></section>
</main>
<footer><div class="container footer-inner"><a class="footer-name" href="#inicio"><?= e($site['name']) ?></a><p>© <?= date('Y') ?> <?= e($site['name']) ?> · Chile</p><a href="#inicio">Volver arriba ↑</a></div></footer>
<?php if ($links['whatsapp_url']): ?><a class="floating-contact" href="<?= e($links['whatsapp_url']) ?>" aria-label="Contactar a Xommafitnes por WhatsApp">WhatsApp ↗</a><?php endif; ?>
</body>
</html>
