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
        'image' => 'assets/images/box-xomma.jpeg',
        'image_alt' => 'Interior del box Xomma Fitness con estructuras de entrenamiento, anillas, cajones y una barra con discos.',
        'image_variants' => [],
        // Punto de enfoque horizontal/vertical; porcentajes de 0% a 100%.
        'image_position' => '50% 65%',
        'image_position_mobile' => '50% 65%',
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
        'boxmagic_url' => null, // URL HTTPS oficial de reservas de Boxmagic.
        'boxmagic_store_url' => null, // URL HTTPS de la tienda oficial de Boxmagic; independiente de reservas.
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
    // Tarifa de la imagen recibida; confirmar vigencia con el dueño antes de publicar.
    // Clases mensuales y precios enteros en CLP, en orden ascendente de clases.
    'plans' => [
        ['classes' => 8, 'price' => 45000],
        ['classes' => 12, 'price' => 50000],
        ['classes' => 16, 'price' => 55000],
        ['classes' => 20, 'price' => 60000],
        ['classes' => 24, 'price' => 65000],
        ['classes' => 28, 'price' => 70000],
        ['classes' => 32, 'price' => 75000],
    ],
    'plans_title' => 'Planes mensuales Full Day',
    'plans_condition' => 'Las clases son mensuales y no se acumulan para el mes siguiente.',
    // Transcripción de la imagen de horarios entregada por el usuario.
    // Las celdas vacías del lunes por la mañana no se publican como clases.
    'schedule' => [
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
    ],
    'schedule_image' => 'assets/images/horarios-xomma.jpeg',
    'plans_intro' => 'Consulta las opciones disponibles para entrenar en Xommafitnes',
    'schedule_intro' => 'Consulta la programación del box antes de organizar tu visita.',
    'gallery' => [], // [['src' => 'assets/images/….webp', 'alt' => '…', 'width' => 1200, 'height' => 800]]
    'faq' => [
        ['question' => '¿Cómo puedo comenzar?', 'answer' => 'Revisa los canales de contacto al final de esta página. Allí publicaremos la información confirmada para consultar por tu primera visita.'],
        ['question' => '¿Dónde consulto los planes y horarios?', 'answer' => 'En Planes y horarios encontrarás los planes mensuales Full Day y la programación del box. Puedes consultar los detalles por nuestro canal de contacto.'],
        ['question' => '¿Puedo reservar una clase desde esta web?', 'answer' => 'Esta web presenta el gimnasio. Las reservas y la gestión de membresías continúan en Boxmagic; el acceso aparecerá cuando esté confirmado el enlace oficial.'],
        ['question' => '¿Dónde está ubicado el gimnasio?', 'answer' => 'Xommafitnes está en Chile. Publicaremos la dirección y el enlace al mapa cuando los dueños confirmen esos datos.'],
    ],
];
