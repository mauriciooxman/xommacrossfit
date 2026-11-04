# Skills de diseño y revisión inicial

Estado actual: las tres skills se migraron a `C:/Users/mauro/.agents/skills/` para uso en todos los proyectos, conservando sus archivos y versiones. Ya no hay copias en `.agents/skills/` del proyecto ni copias adicionales en `.codex/skills/` del usuario. Las rutas locales y los comandos de instalación descritos más abajo son el registro histórico de la instalación inicial.

Configuración del 2 de noviembre de 2026. Objetivo: incorporar guías de diseño y revisar el código existente sin rediseñar. Aceptación: skills individuales sin duplicados, reglas en AGENTS.md, sin dependencias del sitio ni cambios en PHP/CSS/JS, hasta cinco propuestas y validación sin Playwright.

## Instalación y uso

Se revisaron las skills disponibles en la sesión y los directorios de usuario `.agents/skills` y `.codex/skills`. No había copias de estas tres. No existía AGENTS.md en el proyecto ni en los directorios antecesores consultados; se conservaron allí las instrucciones suministradas por el usuario.

| Fuente oficial | Skill y ruta de origen | Destino local / invocación en el chat |
| --- | --- | --- |
| [Impeccable](https://github.com/pbakaus/impeccable#install) | `.agents/skills/impeccable` | `.agents/skills/impeccable` / `$impeccable audit` o `$impeccable polish` |
| [Emil Kowalski](https://github.com/emilkowalski/skills#install) | `skills/emil-design-eng` | `.agents/skills/emil-design-eng` / `$emil-design-eng` seguido de una tarea de interacción |
| [Taste Skill](https://github.com/Leonxlnx/taste-skill#install) | `skills/taste-skill`, nombre declarado `design-taste-frontend` | `.agents/skills/design-taste-frontend` / `$design-taste-frontend` seguido de la dirección que se desea explorar |

Impeccable documenta la copia manual para Codex a `.agents/skills`. Emil documenta `npx skills@latest add emilkowalski/skills`, sin instrucciones separadas para Codex. Taste documenta tanto la instalación selectiva con `npx skills` como un plugin Codex y enlaces desde un clon. Se eligió el instalador de skills incluido en este entorno, con rutas oficiales y destino local explícito; no se ejecutaron npx, instaladores de plugins ni paquetes completos.

Comandos ejecutados con el helper `C:/Users/mauro/.codex/skills/.system/skill-installer/scripts/install-skill-from-github.py`:

```powershell
python C:/Users/mauro/.codex/skills/.system/skill-installer/scripts/install-skill-from-github.py --repo pbakaus/impeccable --path .agents/skills/impeccable --dest C:/Users/mauro/Desktop/OXMANIT/xommafitnes/.agents/skills --method download
python C:/Users/mauro/.codex/skills/.system/skill-installer/scripts/install-skill-from-github.py --repo emilkowalski/skills --path skills/emil-design-eng --dest C:/Users/mauro/Desktop/OXMANIT/xommafitnes/.agents/skills --method download
python C:/Users/mauro/.codex/skills/.system/skill-installer/scripts/install-skill-from-github.py --repo Leonxlnx/taste-skill --path skills/taste-skill --name design-taste-frontend --dest C:/Users/mauro/Desktop/OXMANIT/xommafitnes/.agents/skills --method download
```

El helper confirmó las tres instalaciones. Copia desde `main` en esa fecha; no hay actualización automática. Estarán disponibles para descubrimiento en el siguiente turno; si no aparecen en el selector, abrir una nueva sesión en este proyecto. Las invocaciones anteriores son mensajes de chat, no comandos PowerShell. También se puede pedir por nombre que se lea su SKILL.md local.

Impeccable 4.8.0 incluye un lanzador que descarga un motor ejecutable en su primera ejecución. No se ejecutó ni se descargó el motor: se usaron sus instrucciones y la referencia de auditoría como guía de revisión estática. No se habilitaron hooks. Emil y Taste se instalaron para uso condicional; sus workflows no se ejecutaron. Las reglas de AGENTS.md limitan expresamente sus activaciones genéricas.

## Propuestas priorizadas, sin aplicar

Todas se basan en código; no se observó el renderizado ni se reutilizaron las capturas históricas de `.qa/`. La revisión visual queda pendiente de las capturas del usuario.

| Prioridad | Problema comprobado por código | Cambio propuesto | Sección o archivo |
| --- | --- | --- | --- |
| 1 | Con WhatsApp y correo vacíos, Contacto anuncia que los canales oficiales llegarán pronto, aunque ya muestra Instagram configurado. El CTA principal y los de disciplinas llegan a ese bloque. | Presentar el Instagram existente como acción principal en ese caso y ajustar el mensaje al canal disponible, sin inventar contactos. | Contacto y destino de consultas; `app/views/home.php`, usando `config/site.php` sin cambiar los datos comerciales. |
| 2 | La nota del hero baja a 8 px en móvil, los rótulos a 9 px y el pie usa 10 px. | Probar una escala auxiliar de 12–14 px, ajustar interletrado y permitir saltos naturales. Conservar la tipografía y el protagonismo del título. | `.hero-note`, `.eyebrow`, `.panel-label`, `.contact-details dt`, figcaptions y pie; `public/assets/css/site.css`. |
| 3 | Instagram y «Volver arriba» son enlaces sin padding ni altura mínima propia; el enlace social usa texto de 13 px. No hay evidencia de su área efectiva renderizada. | Darles área táctil cómoda, idealmente 44 px de alto, y separación suficiente manteniendo el foco existente. Confirmar con capturas y prueba manual posterior; no afirmar incumplimiento WCAG sin medir. | `.social-links a`, enlaces de contacto y `.footer-inner>a:last-child`; `public/assets/css/site.css`. |
| 4 | Hay muchos colores secundarios, bordes y sombras declarados directamente; las tarjetas de planes elevan también al enfocar y al pasar el ratón, aunque la interacción real es el enlace interno. | Reducir la elevación de paneles informativos y compartir unos pocos valores de borde y texto secundario para reforzar jerarquía. Mantener negro/amarillo y las protecciones de movimiento reducido. Confirmar primero el efecto visual con capturas. | `.info-panel`, `.program`, estados hover/focus y variables existentes; `public/assets/css/site.css`. |

Se conserva el hero con foto real, las cuatro disciplinas, carga prioritaria/srcset, imágenes diferidas fuera de portada, foco visible, menú con Escape y soporte de movimiento reducido ya presentes. El botón flotante depende de WhatsApp, actualmente vacío: no se presenta como un problema visible actual. Los enlaces sobre fondos claros ya tienen reglas propias de hover y foco.

## Límites de validación

Resultado de esta entrega: sintaxis correcta en los cinco archivos PHP y en `site.js`; 138 comprobaciones de `tests/smoke.php` aprobadas. Tres SKILL.md con nombres distintos y sin referencias locales inexistentes en sus enlaces Markdown. Git muestra únicamente las tres incorporaciones de configuración/documentación (`.agents/`, `AGENTS.md` y este registro), sin diferencias en los archivos existentes del sitio. La excepción de propiedad de Git se pasó solo al comando con `-c safe.directory=...`; no se cambió configuración global.

Las comprobaciones PHP existentes cubren renderizado, enlaces, recursos locales, datos condicionales y escape. La sintaxis JavaScript se verifica con Node. Estas pruebas no demuestran encuadre, contraste renderizado, tamaños táctiles efectivos, ausencia de solapamientos ni comportamiento real en móvil. No se comprobaron destinos externos en vivo ni se ejecutó un navegador. Las verificaciones históricas descritas en README.md no equivalen a validación de esta entrega.

## Refinamientos aplicados tras autorización

El usuario autorizó implementar mejoras conservando el diseño. No llegaron capturas accesibles en ese mensaje: esta pasada utiliza exclusivamente evidencia del código y las propuestas anteriores. Se leyeron Impeccable, `reference/polish.md` y `reference/craft-floor.md`, sin ejecutar el lanzador ni el motor. Las preferencias del proyecto prevalecen sobre las preferencias genéricas de la skill; se mantienen fuentes, composición, rótulos y recursos existentes.

- `public/assets/css/site.css`: textos auxiliares de 12 px, enlaces de 14 px y botones de 15 px; menos compresión entre letras y líneas de los títulos. Tamaños de disciplinas ajustados para el nombre largo Halterofilia, separación de índices reducida y consultas alineadas a todo el ancho de cada tarjeta. Se mantienen las cuatro disciplinas y las columnas existentes.
- Navegación: áreas de al menos 44 px de alto y menú plegable desde 900 px para acomodar enlaces mayores; altura desplazable en pantallas bajas. Se conserva la navegación disponible sin JavaScript y el script existente de Escape/cierre.
- Contacto y pie: enlaces con área táctil ampliada; acción principal hacia Instagram si faltan WhatsApp y correo, alternativa Facebook únicamente si está configurada y ausencia de botón cuando no hay canales válidos. No se cambiaron datos comerciales.
- Paneles informativos: sin elevación al pasar el ratón ni sombras de tarjeta que compitan con los enlaces. El foco de los enlaces y `prefers-reduced-motion` se conservan.
- Fotos, puntos de enfoque, variantes y proporciones sin cambios: no existe evidencia visual nueva para justificar otro recorte.

Validación posterior: sintaxis de cinco PHP y del JavaScript correcta; 145 comprobaciones PHP aprobadas, incluidas siete nuevas sobre prioridades, ausencia de canales y URL insegura. HTTP local: portada, cuatro disciplinas, acción de Instagram, CSS, JS, logo y foto respondieron correctamente; POST devuelve 405. Script del menú ejercitado con DOM simulado en Node: apertura, Escape con devolución de foco, cierre por enlace y clic exterior. Esto no comprueba CSS ni interacción en un navegador real. No se instaló un validador CSS ni se comprobó el renderizado. Sin publicación ni push.

Pendiente de capturas posteriores a los cambios: escritorio a 1440 px (cabecera/hero, cuatro disciplinas y contacto/pie); móvil a 390 px y, si es posible, 320 px (mismas secciones y menú abierto); ancho intermedio de 900–1024 px para la transición de navegación. Revisar saltos de título, alineación de consultas, altura y desplazamiento del menú, legibilidad, foco y encuadre real. Las capturas iniciales, si se comparten después, servirán de comparación, no de validación del resultado actual.

## Planes y accesos a Boxmagic (2026-11-04)

Cambio acotado: título «Planes», texto confirmado y consulta directa al canal de Contacto (actualmente Instagram). Botón amarillo a todo el ancho en móvil según CSS. `contact.boxmagic_store_url` habilita compra/renovación; `contact.boxmagic_url` conserva su función de reservas y habilita «Agendar clase». Ambos siguen en `null`. Se preservan `#planes`, la navegación y el renderizado de horarios. Sin tienda incrustada ni operaciones comerciales propias.

Validación local: cinco PHP sin errores de sintaxis, JavaScript sin errores de sintaxis, 252 comprobaciones aprobadas y `git diff --check` sin errores. Los fixtures en memoria cubren tienda y reservas por separado, ambas, URLs inválidas, persistencia de consultas y horarios. HTTP 200 de portada, CSS y JS; el HTML servido enlaza «Consultar planes» al Instagram configurado. La consulta externa de Instagram no permitió verificar su disponibilidad: conexión remota fallida y lectura web bloqueada por robots.txt. Pendiente comprobar apertura manual del perfil, capturas actuales de escritorio/móvil y compra/reserva reales cuando el dueño entregue los enlaces. Sin BMAD, Playwright, descargas, publicación ni push.

## Tabla mensual Full Day (2026-11-04)

Se reemplazó el listado de tarjetas por una tabla HTML con encabezados de columna/fila, título y condición asociados. Los siete pares de clases y precios provienen de la transcripción de la imagen entregada por el usuario; `plans` guarda clases y CLP como enteros. La vista formatea los miles. Se conserva negro/amarillo, precios a la derecha, una consulta al Instagram configurado, horarios, anclas y accesos condicionales a Boxmagic. No se añadieron disciplinas incluidas, horarios de acceso, matrícula ni equivalencias semanales.

Validación: 268 comprobaciones aprobadas; sintaxis PHP/JS correcta; HTTP 200 de portada y CSS, siete pares exactos en orden y consulta a Instagram comprobados en el HTML servido. CSS de tabla de ancho fijo, encabezados con salto de línea y sin ancho mínimo: revisión estática para móvil, sin evidencia visual nueva. Pendiente capturas actuales de escritorio/móvil y confirmación de vigencia de la tarifa por el dueño antes de publicar. Cambios exclusivamente locales, sin publicación ni push; sin BMAD ni Playwright.

## Horarios de la imagen y portada solicitada

Se transcribieron los horarios visibles a `schedule` y se mantuvo la tabla accesible existente y su ancla. Se conservaron los vacíos del lunes por la mañana; sábados, domingos y feriados a las 10:00 sin atribuirles disciplina. Consulta de horarios directa al contacto configurado. Validación: sintaxis de los cinco PHP y JavaScript correcta, 274 comprobaciones aprobadas, incluyendo cada fila de horarios. No se ejecutó revisión visual.

Actualización tras recibir los archivos: se incorporaron `box-xomma.jpeg` como portada (900 × 1600) y `horarios-xomma.jpeg` como cartel ampliable (1600 × 1131), sin modificar los píxeles. Se eliminaron de la configuración las variantes de la foto anterior. La tabla de horarios permanece en un desplegable accesible. La nueva imagen difiere de la primera: viernes a las 21:00 vacío y CrossFit los sábados, domingos y feriados a las 10:00; la transcripción se actualizó a esta versión. Sintaxis PHP/JS correcta y 297 comprobaciones aprobadas. Pendiente revisión visual del renderizado en escritorio/móvil. Sin BMAD, Playwright, publicación ni push.

## Disponibilidad de usuario

### Rediseño de tarifas y agenda diaria

Sección `#planes`: título «Planes y horarios», dos columnas desde 901 px y bloques apilados por debajo. Se retiraron los paneles con borde de esta sección. Tarifas con cantidad y precio en la misma fila, ancho máximo de 440 px en escritorio, ancho disponible en móvil, cifras tabulares y una indicación de moneda/periodicidad. Datos comerciales y enlaces de Boxmagic intactos.

La agenda se genera únicamente desde `schedule`, con días y feriados explícitos. JavaScript habilita pestañas accesibles (selección por clic, flechas, Home/End y foco); sin JS los ocho días permanecen visibles. El afiche queda como enlace secundario en otra pestaña. No se añadieron horarios ni disciplinas.

Validación actual: 311 comprobaciones PHP, sintaxis de cinco PHP y JavaScript, prueba de interacción con DOM simulado en `tests/agenda.js`, y HTTP 200 de portada, CSS, JS y afiche. Agenda contrastada por día con los datos originales, incluyendo lunes, miércoles, viernes y feriados. Tareas verificadas en HTML/datos: 12 clases cuestan $50.000 mensuales; miércoles después de las 18:00: 19:00 Halterofilia, 20:00 Fatburn, 21:00 CrossFit. No se observó el resultado en navegador: rapidez de lectura y aspecto real en móvil/escritorio pendientes de capturas actuales. Sin BMAD, Playwright, publicación ni push.

### Ajuste de lectura de horarios

El cartel ocupa ahora todo el ancho de la grilla, en una fila propia. Se retiraron el enlace de la imagen y «Ampliar horarios», que abandonaban la página. La tabla de texto queda siempre visible con fuente de 16 px, sin desplegable. Comprobaciones: 285 aprobadas, sintaxis PHP/JS correcta y HTTP local 200; sin enlace al archivo de imagen en el HTML. Pendiente revisión visual del resultado en escritorio y móvil. Sin publicación ni push.

Se comprobó que `CODEX_HOME` no está definido, no hay un perfil alternativo declarado en `config.toml` ni `AGENTS.override.md` global. La configuración global es `C:/Users/mauro/.codex/AGENTS.md`. Se añadió únicamente el bloque `oxman-diseno-global-v1`, manteniendo byte a byte todo el contenido previo y respaldándolo en `C:/Users/mauro/.codex/AGENTS.md.before-design-global.bak`. Las reglas particulares de este proyecto permanecen en su AGENTS.md local.

Se migraron las tres carpetas completas a la ubicación de usuario documentada por Codex, sin descargar nuevas versiones, ejecutar motores ni instalar plugins. Integridad SHA-256 verificada para los 103 archivos trasladados: Impeccable (63), Emil (1) y Taste (39). No quedan instalaciones duplicadas por nombre en las ubicaciones de proyecto/usuario revisadas.

Para comprobarlo en otro proyecto, abrir una sesión nueva de Codex y pedir: «Indica qué AGENTS.md cargaste y qué skills de diseño tienes disponibles, con sus rutas, sin ejecutarlas». Debe reconocer el AGENTS.md global y `impeccable`, `emil-design-eng`, `design-taste-frontend` bajo `C:/Users/mauro/.agents/skills/`. En CLI/IDE también se puede usar `/skills` o `$` para consultar el selector. Si no aparecen, reiniciar Codex. La verificación de archivos no equivale a haber abierto y probado una sesión en otro proyecto.

Fuentes oficiales: [ubicaciones y descubrimiento de skills](https://learn.chatgpt.com/docs/build-skills#where-codex-loads-local-skills) y [instrucciones globales](https://learn.chatgpt.com/docs/agent-configuration/agents-md#how-codex-discovers-guidance).
