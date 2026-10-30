<?php
declare(strict_types=1);

// Datos públicos confirmados. null o [] = pendiente; nunca generar enlaces ficticios.
return [
    'name' => 'Xommafitnes', // Cambiar aquí al confirmar la escritura comercial.
    'description' => 'Conoce Xommafitnes, un gimnasio de CrossFit en Chile. Descubre nuestra identidad y la información para dar tu próximo paso.',
    'timezone' => 'America/Santiago',
    'canonical_url' => null, // URL HTTPS definitiva, incluida la subcarpeta si corresponde.
    'hero' => [
        'title_lines' => ['TU PRÓXIMO', 'PASO'],
        'title_accent' => 'EMPIEZA AQUÍ.',
        'intro' => 'Entrena. Comparte. Vuelve a intentarlo.',
        'cta' => 'Quiero conocer el box',
        'note' => 'FUERZA PARA MOVERTE. CONSTANCIA PARA SEGUIR.',
        // Medios locales autorizados: foto WebP/JPEG/PNG; video MP4/WebM opcional.
        // Sin foto válida se conserva el diseño gráfico. El video necesita esa foto como respaldo.
        'image' => 'assets/images/comunidad-xomma-1200.webp',
        'image_alt' => 'Grupo de siete personas en el box, frente al letrero de Xomma CrossFit.',
        'image_variants' => [
            'assets/images/comunidad-xomma-480.webp',
            'assets/images/comunidad-xomma-800.webp',
            'assets/images/comunidad-xomma-1200.webp',
        ],
        // Punto de enfoque horizontal/vertical; porcentajes de 0% a 100%.
        'image_position' => '50% 70%',
        'image_position_mobile' => '50% 90%',
        'video' => null, // assets/videos/hero.mp4; controles, sin autoplay.
        'video_label' => 'Conoce Xommafitnes en movimiento',
    ],
    'contact' => [
        'whatsapp_url' => null, // https://wa.me/ + número internacional confirmado.
        'email' => null,
        'address' => null,
        'maps_url' => null,
        'instagram_url' => 'https://www.instagram.com/xommafitness/',
        'facebook_url' => null,
        'boxmagic_url' => null,
    ],
    // Publicar solo información aprobada por los dueños.
    'training_title' => 'CUATRO FORMAS DE DAR TU SIGUIENTE PASO',
    'training_intro' => 'El primer paso es conocer el box. La constancia se construye después.',
    // Foto opcional local; sin foto se conserva la composición tipográfica.
    // Enfoques horizontal/vertical en porcentajes, independientes para móvil.
    'programs' => [
        [
            'name' => 'CrossFit',
            'description' => 'Combina fuerza, resistencia y movimientos funcionales en entrenamientos variados.',
            'cta' => 'Consultar por CrossFit',
            'message' => 'Hola, quiero consultar por CrossFit en Xommafitnes.',
            'image' => null, 'image_alt' => '',
            'image_position' => '50% 50%', 'image_position_mobile' => '50% 50%',
        ],
        [
            'name' => 'Halterofilia',
            'description' => 'Una disciplina centrada en la técnica de la arrancada y el envión, para trabajar fuerza, potencia y coordinación.',
            'cta' => 'Consultar por Halterofilia',
            'message' => 'Hola, quiero consultar por Halterofilia en Xommafitnes.',
            'image' => null, 'image_alt' => '',
            'image_position' => '50% 50%', 'image_position_mobile' => '50% 50%',
        ],
        [
            'name' => 'HYROX',
            'description' => 'Entrenamiento que combina carrera y estaciones de trabajo funcional, con foco en la resistencia y la fuerza.',
            'cta' => 'Consultar por HYROX',
            'message' => 'Hola, quiero consultar por HYROX en Xommafitnes.',
            'image' => null, 'image_alt' => '',
            'image_position' => '50% 50%', 'image_position_mobile' => '50% 50%',
        ],
        [
            'name' => 'Musculación',
            'description' => 'Trabajo de fuerza con cargas para desarrollar la musculatura y mejorar el control del movimiento.',
            'cta' => 'Consultar por Musculación',
            'message' => 'Hola, quiero consultar por Musculación en Xommafitnes.',
            'image' => null, 'image_alt' => '',
            'image_position' => '50% 50%', 'image_position_mobile' => '50% 50%',
        ],
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
