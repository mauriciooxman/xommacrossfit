<?php
declare(strict_types=1);

// Datos públicos confirmados. null o [] = pendiente; nunca generar enlaces ficticios.
return [
    'name' => 'Xommafitnes',
    'description' => 'Conoce Xommafitnes, un gimnasio de CrossFit en Chile. Descubre nuestra identidad y la información para dar tu próximo paso.',
    'timezone' => 'America/Santiago',
    'canonical_url' => null, // URL HTTPS definitiva, incluida la subcarpeta si corresponde.
    'hero' => [
        'title_lines' => ['TU PRÓXIMO', 'PASO ES'],
        'title_accent' => 'CONTIGO.',
        'intro' => 'Entrena. Comparte. Vuelve a intentarlo.',
        'cta' => 'Quiero conocer el box',
        'note' => 'FUERZA PARA MOVERTE. CONSTANCIA PARA SEGUIR.',
        // Medios locales autorizados: foto WebP/JPEG/PNG; video MP4/WebM opcional.
        // Sin foto válida se conserva el diseño gráfico. El video necesita esa foto como respaldo.
        'image' => null, // assets/images/hero.webp
        'image_alt' => '',
        'video' => null, // assets/videos/hero.mp4; controles, sin autoplay.
        'video_label' => 'Conoce Xommafitnes en movimiento',
    ],
    'contact' => [
        'whatsapp_url' => null, // https://wa.me/ + número internacional confirmado.
        'email' => null,
        'address' => null,
        'maps_url' => null,
        'instagram_url' => null,
        'facebook_url' => null,
        'boxmagic_url' => null,
    ],
    // Publicar solo información aprobada por los dueños.
    'programs' => [
        ['name' => 'CrossFit', 'description' => 'Fuerza, movimiento y constancia. Conoce el entrenamiento que nos reúne.'],
    ],
    'coaches' => [], // [['name' => '…', 'bio' => '…']] Sin credenciales no verificadas.
    'plans' => [], // [['name' => '…', 'price' => '…', 'description' => '…']]
    'schedule' => [], // [['days' => '…', 'hours' => '…', 'program' => '…']]
    'plans_intro' => 'Conoce las opciones para entrenar y consulta sus valores antes de dar el primer paso.',
    'schedule_intro' => 'Consulta la programación del box antes de organizar tu visita.',
    'gallery' => [], // [['src' => 'assets/images/….webp', 'alt' => '…', 'width' => 1200, 'height' => 800]]
    'faq' => [
        ['question' => '¿Cómo puedo comenzar?', 'answer' => 'Revisa los canales de contacto al final de esta página. Allí publicaremos la información confirmada para consultar por tu primera visita.'],
        ['question' => '¿Dónde consulto los planes y horarios?', 'answer' => 'En la sección Planes y horarios encontrarás la información cuando esté confirmada. Por ahora no hay precios ni horarios publicados.'],
        ['question' => '¿Puedo reservar una clase desde esta web?', 'answer' => 'Esta web presenta el gimnasio. Las reservas y la gestión de membresías continúan en Boxmagic; el acceso aparecerá cuando esté confirmado el enlace oficial.'],
        ['question' => '¿Dónde está ubicado el gimnasio?', 'answer' => 'Xommafitnes está en Chile. Publicaremos la dirección y el enlace al mapa cuando los dueños confirmen esos datos.'],
    ],
];
