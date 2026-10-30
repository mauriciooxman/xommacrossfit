# Xommafitnes

Primera versión local de la web pública. PHP renderiza HTML; CSS y un pequeño script controlan la presentación y el menú móvil. Sin framework, paquetes, cookies, analítica, formularios, base de datos ni servicios externos en esta etapa. No se ha publicado ni enviado correo.

## Ejecutar

Requiere PHP 8.3 o superior dentro de una rama con soporte de seguridad. Para producción, confirmar con SolucionHost la versión mantenida disponible y usar su última revisión de seguridad. No usar el servidor integrado en producción.

```powershell
php -S 127.0.0.1:8080 -t public
```

Abrir http://127.0.0.1:8080. Detener con Ctrl+C. Si el PHP local advierte que no carga `xml`, corregir su `php.ini` fuera del proyecto; esta web no depende de esa extensión. Para una prueba aislada sin cargar ese archivo: `php -n -S 127.0.0.1:8080 -t public`.

```powershell
Get-ChildItem app,config,public,tests -Recurse -Filter *.php | ForEach-Object { php -n -l $_.FullName }
php -n tests/smoke.php
node --check public/assets/js/site.js
```

## Estructura y edición

- `public/`: única carpeta expuesta a Internet, entrada `index.php` y recursos locales.
- `app/bootstrap.php`: carga, escape y validación de URLs; `app/views/home.php`: vista.
- `config/site.php`: **todos los datos públicos** confirmados y pendientes. `null`/`[]` ocultan canales, entrenadores y contenidos sin confirmar. Al modificar planes, ubicación o contactos, revisar también las respuestas de `faq` en ese archivo.
- `config/local.php`: opcional, ignorado por Git; devuelve un array de sobreescrituras. Reservado para configuración privada futura. No poner contraseñas en `site.php`.
- `storage/`: reservado, privado, vacío. No recibe ni procesa datos.
- `docs/`: alcance y diseño futuro. `tests/`: comprobaciones sin dependencias.

WhatsApp, redes, mapa y Boxmagic aparecen solo con URLs HTTPS válidas. No se genera mapa embebido ni petición a terceros. Los enlaces se abren en la misma pestaña. El botón principal baja a contacto hasta configurar WhatsApp. Sin un canal confirmado todavía **no hay captación operativa**. No hay formulario ni mensaje de envío exitoso; las peticiones POST devuelven 405.

Se conservan los cuatro JPEG originales. Los WebP de `public/assets/images/` son recortes y reducciones de los logos suministrados, sin fotografías inventadas. Añadir fotos autorizadas optimizadas a esa carpeta y completar `gallery` con ruta relativa, texto alternativo y dimensiones. Confirmar permisos de imagen de las personas antes de publicarlas. El nombre visible provisional es «Xommafitnes» aunque el logotipo recibido dice «Xomma Fitness».

## SolucionHost: confirmar antes de desplegar

1. PHP mantenido disponible, revisión instalada, HTTPS y capacidad de fijar `display_errors=Off` y registro privado de errores. `Options -Indexes` puede requerir autorización del hosting: si produce 500, desactivar el listado desde su panel y retirar esa línea.
2. Ruta absoluta de la cuenta, carpeta pública asignada y posibilidad de almacenar `app/`, `config/` y `storage/` **fuera** de ella. No subir el proyecto completo a `public_html` ni confiar en `.htaccess` para ocultar secretos.
3. Método de carga disponible (panel, SFTP/FTP seguro), permisos y respaldo/restauración. No se exige SSH, Composer remoto, proceso persistente ni cambio de document root.
4. Para etapas posteriores: MySQL/MariaDB y versión, `pdo_mysql`, transacciones InnoDB y `utf8mb4`, capacidad y límites de importación (`upload_max_filesize`, `post_max_size`, memoria y tiempo), espacio privado para archivos temporales.
5. Para avisos posteriores: SMTP autenticado, TLS, puerto de salida, cuota, remitente autorizado y SPF/DKIM/DMARC del dominio. Confirmar cron y su versión de PHP; si no existe, ejecución manual autenticada. Composer y biblioteca SMTP podrán prepararse localmente y subirse con `vendor/` cuando se habilite correo.

La compatibilidad real con el plan de hosting sigue pendiente; no se ha asumido ninguna capacidad del proveedor.

### Despliegue compartido sin cambiar document root

Ejemplo ilustrativo: la cuenta permite `/home/CUENTA/xommafitnes-private/` junto a `/home/CUENTA/public_html/`. Sustituir por rutas reales:

1. Subir `app/`, `config/` y `storage/` a `xommafitnes-private/`. No subir originales, pruebas ni documentación al directorio público.
2. Copiar `public/assets/` y `public/.htaccess` a `public_html/` (o a su subcarpeta pública). Copiar `public/index.php` como `xommafitnes-private/front.php`.
3. Crear **solo** este `public_html/index.php` con la ruta real:

```php
<?php
declare(strict_types=1);
define('XOMMA_ROOT', '/home/CUENTA/xommafitnes-private');
require XOMMA_ROOT . '/front.php';
```

Si se permite cambiar document root, subir el proyecto privado y apuntarlo a `public/`; no hace falta wrapper. Si no existe ninguna ubicación privada fuera del área web, resolver esa limitación con el proveedor antes de desplegar.

4. Confirmar dominio/HTTPS antes de completar `canonical_url`; no hay canonical ni datos estructurados con dominio inventado. Antes de publicación, usar protección de acceso del hosting para la vista previa.
5. Comprobar portada, assets, anchors, móvil, teclado y URLs reales; verificar que `/config/site.php`, `/app/bootstrap.php`, `/storage/` y archivos privados no son accesibles. Probar POST 405 y que los errores no muestran rutas. Mantener copia de la versión anterior para volver atrás.

Futuro respaldo: archivos privados y dump cifrado de base de datos fuera del sitio público, acceso limitado, retención acordada y ensayo de restauración en entorno aislado antes de usar datos reales.

## Disciplinas

El bloque `#entrenamiento` presenta CrossFit, Halterofilia, HYROX y Musculación en dos columnas, o una en móvil. Título e introducción: `training_title` y `training_intro` en `config/site.php`. Cada entrada de `programs` contiene nombre, descripción, CTA y mensaje editable. Sin WhatsApp se enlaza a Contacto, donde está Instagram; al configurar una URL `https://wa.me/NUMERO` válida se genera un mensaje específico codificado, sin enviarlo automáticamente.

Para añadir una fotografía, guardar un WebP/JPEG/PNG optimizado en `public/assets/images/` y completar `image` e `image_alt` en la disciplina correspondiente. Ajustar `image_position` y `image_position_mobile` con dos porcentajes, por ejemplo `50% 40%`. Se usa un panel 16:9 con dimensiones detectadas y carga diferida; escoger fotos con margen para recortar y revisar rostros en ambos tamaños. Con `image: null`, ruta inválida o archivo inexistente, la tarjeta conserva su composición tipográfica sin huecos. La fotografía del hero es independiente.

El nombre provisional se cambia en `config/site.php`, clave `name`; revisar también los textos editables que lo mencionan, incluida la descripción y los mensajes de consulta. Validación de esta entrega: 138 comprobaciones PHP, sintaxis PHP/JS, Chromium en seis anchos entre 320 y 1440 px, menú con teclado, foco visible, anchors y ausencia de desplazamiento horizontal. Capturas locales en `.qa/`, sin publicar.

## Pendientes de los dueños

### Diseño y material para el hero

Se mantiene el diseño gráfico de marca cuando no hay fotografía real. No hay espacios reservados de foto ni galería ficticia. El hero, su texto y medios se configuran en `config/site.php` (`hero`); los textos introductorios de planes y horarios también están centralizados. El diseño alterna comunidad/identidad claras con entrenamiento/planes oscuros y cierre verde carbón. Diagonales, órbitas y texturas se dibujan en CSS; no hay nuevas dependencias, fuentes externas ni animaciones continuas. Los hover se limitan a punteros precisos y las transiciones respetan `prefers-reduced-motion`.

- **Foto principal:** incorporada la fotografía del grupo en el hero. El adjunto recibido se conserva sin cambios en `docs/originals/comunidad-xomma-original.jpg` (1368×1824 px tras interpretar su orientación); no equivale al archivo de cámara de mayor resolución. Los WebP de 480, 800 y 1200 px conservan la proporción, sin ampliar, y omiten metadatos. El navegador elige con `srcset`/`sizes`; carga prioritaria, sin lazy loading y dimensiones reales.
- **Reemplazo:** guardar los derivados en `public/assets/images/` y editar `hero.image`, `hero.image_variants` (rutas de la misma fotografía) y `hero.image_alt` en `config/site.php`. Vaciar `image_variants` si solo se dispone de un archivo. Las dimensiones se detectan automáticamente; no hace falta editar la maquetación. Usar texto alternativo vacío solo para imágenes decorativas.
- **Encuadre:** `hero.image_position` y `hero.image_position_mobile` aceptan dos porcentajes horizontal/vertical entre 0 y 100. La foto actual usa `50% 70%` en escritorio y `50% 90%` en móvil: se recorta techo y suelo, conservando al grupo y el letrero. Sin JavaScript se mantienen esos enfoques como respaldo CSS. Texto y botones separados de la imagen: no necesitan degradado oscuro. El panel móvil tiene proporción 1.05 para limitar la altura y evitar cortes de personas.
- **Video opcional:** toma horizontal estable de 10–20 segundos mostrando entrenamiento y ambiente reales, sin rótulos incrustados. Preparar MP4 H.264 o WebM, 720p, idealmente menos de 3 MB y sin pista de audio; guardar en `public/assets/videos/` y completar `hero.video`. Necesita siempre la foto anterior. Tiene controles, `muted`, `playsinline`, `preload="none"` y **no** tiene autoplay ni loop. Si falla la carga, JS deja la foto visible. Movimiento reducido no inicia reproducción: solo se reproduce por decisión del visitante. Confirmar MIME/rangos HTTP de video con hosting al activarlo.
- **Galería:** pedir 4–6 fotos horizontales del espacio, equipamiento y comunidad, más retratos autorizados del equipo cuando existan datos reales. Componer pensando en recorte 3:2 y obtener autorización de las personas reconocibles. No usar rostros o material de otros gimnasios.

Sin medios no se descarga ningún video. Solo se admiten archivos locales con extensión permitida y existentes para el hero; no se admiten URLs remotas ni rutas privadas. La comprobación automática del reproductor valida su HTML y respaldo, no la reproducción de un video real todavía no entregado.

Verificación de la fotografía: sintaxis PHP/JS, 83 comprobaciones de renderizado, rutas y configuración; carga HTTP y Chromium local en anchos de 320, 390, 760, 1024 y 1440 px. Capturas revisadas en escritorio y móvil, sin deformación ni cortes del grupo. Se comprobó apertura/cierre del menú móvil. Se ajustó el título de preguntas en pantallas estrechas para corregir un desbordamiento a 320 px; no queda desplazamiento horizontal en los tamaños probados. Capturas y script temporal en `.qa/` (ignorado por Git). La reproducción con video real sigue pendiente hasta recibir ese material.

Nombre comercial definitivo; dirección y enlace al mapa; WhatsApp y correo; URLs de redes y Boxmagic; planes, condiciones, precios y horarios; nombres y biografías autorizadas de entrenadores; fotos y permisos; dominio; información del hosting. Después: exportación anonimizada de Boxmagic con significado de campos y alcance. Véanse [alcance](docs/alcance.md) y [modelo propuesto](docs/modelo-datos.md).
