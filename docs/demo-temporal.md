# Presentación temporal · 10 de noviembre de 2026

Enlace para compartir: **https://get-winter-month-appear.trycloudflare.com**

La URL es pública para quien la conozca, sin login ni PIN. Es una presentación temporal con Cloudflare Quick Tunnel, sin publicar en hosting, cambiar DNS, abrir puertos del router ni enviar mensajes al cliente. [Documentación oficial de Quick Tunnels](https://developers.cloudflare.com/tunnel/get-started/quick-tunnels/). No tiene garantía de disponibilidad y necesita el computador encendido, despierto y conectado; cambia al crear otro túnel.

## Procesos activos

Se encontró el PHP existente en `127.0.0.1:8080`, PID 22660, servido con `-t public`. Se reinició únicamente ese proceso para aplicar el router restrictivo de presentación y dejarlo oculto e independiente de la terminal. Se conservaron puerto, raíz pública y aplicación existentes; no se modificó ni detuvo MySQL.

- PHP de demostración: **PID 26180**, con `-S 127.0.0.1:8080 -t public bin/demo-router.php`, errores visibles desactivados y registro privado en `storage/`.
- Cloudflared: **PID 8900**, `tunnel --no-autoupdate --url http://127.0.0.1:8080 --protocol http2`. Se reutilizó la instalación oficial existente; no hubo instalación ni autenticación nueva.
- Identificadores, nombre, fecha UTC de inicio y URL: `storage/demo-processes.json`. Logs privados: `storage/demo-tunnel.log`, `storage/demo-php-server.log` y relacionados. Todos quedan fuera de Git y de la raíz pública.

Los procesos se iniciaron con `Start-Process -WindowStyle Hidden` y sus salidas redirigidas a archivos privados. Se verificó que siguieran respondiendo después de terminar los comandos que los lanzaron.

## Restricción temporal

`config/demo.php` tiene `enabled => false` como valor base. El archivo privado, ignorado por Git, **`config/demo.local.php`** lo activa mediante `return ['enabled' => true];`.

La portada y el checkout muestran una advertencia de demostración. Se pueden recorrer los siete planes, sus resúmenes, contenido y agenda. El checkout no abre sesiones ni accede a MySQL/SDK en este modo; sus campos personales, métodos y compra están deshabilitados.

El servidor bloquea toda solicitud operativa, aunque se fuerce desde herramientas o se reactive un botón en el navegador. POST/PUT y demás métodos de escritura devuelven 403 desde el router; el controlador del checkout también bloquea operaciones, retornos y acceso a órdenes anteriores antes de iniciar sesiones o servicios. No se generan órdenes, pagos, instrucciones de transferencia ni correos desde esta vista pública. No existe un endpoint de correo en la aplicación.

`bin/demo-router.php` permite únicamente `/`, `/index.php`, `/checkout.php` y recursos existentes bajo `public/assets/` con extensiones autorizadas. Verifica la ruta real para evitar escapes por enlaces simbólicos y devuelve 404 para archivos ocultos, directorios, configuración, código privado, Git, pruebas, logs y datos. El túnel conecta solo a este servidor restringido, nunca a MySQL ni al directorio completo del proyecto.

Desactivar el modo sin cerrar el túnel causa HTTP 503 en el router: el enlace no se convierte accidentalmente en un checkout operativo.

## Detener el acceso

Desde la raíz del proyecto, ejecutar en PowerShell:

```powershell
.\bin\stop-demo.ps1
```

Detiene primero Cloudflare y luego el PHP de esta presentación. El script valida PID, nombre y fecha de inicio contra el registro para no detener procesos distintos si Windows reutilizó los números. No modifica MySQL ni otros proyectos. Si la política de PowerShell impide ejecutar scripts, leer los identificadores de `storage/demo-processes.json`, verificar que siguen correspondiendo a estos procesos y detener el `cloudflared` registrado desde el Administrador de tareas para cortar el acceso; detener también el PHP registrado si ya no se necesita. No detener todos los procesos PHP o todos los túneles por nombre.

## Volver al checkout local de integración

1. Detener primero la presentación con el script anterior.
2. Cambiar **solo** `config/demo.local.php` a `return ['enabled' => false];` o retirar ese archivo, ya que el valor base es falso.
3. Arrancar el servidor local habitual sin el router de presentación:

```powershell
php -S 127.0.0.1:8080 -t public
```

Esto recupera el checkout local de integración, con MySQL y sus controles originales. No abre cobros reales ni cambia credenciales de Transbank. No volver a conectar un túnel público a ese servidor operativo. Para una nueva presentación, volver a activar demo y usar el router restrictivo; una nueva URL sustituirá a la de este documento.

## Verificaciones realizadas

- **76 comprobaciones por HTTP local y 76 por la URL HTTPS pública** usando `tests/demo-http.php`: portada/checkout, imágenes, CSS, JavaScript, anclas, agenda, siete planes y cada precio/resumen; sin URLs absolutas a localhost.
- Acceso directo a configuración privada, `.env`, `.git`, `.htaccess`, `app/`, `vendor/`, `tests/`, `bin/`, README, migraciones, registros y datos de almacenamiento bloqueado. Las rutas normales responden 404; Cloudflare rechaza algunas rutas de escape codificadas con 400 antes de PHP.
- POST forzados de compra, pago, transferencia, consulta, retorno, nueva compra y correo bloqueados con 403; acciones operativas por GET y consulta de órdenes anteriores también bloqueadas. PUT y POST a portada bloqueados. El checkout demo no emite cookies y su CSP no permite enviar formularios a Transbank.
- Desactivación temporal probada: el router cerró con HTTP 503; se restauró inmediatamente `enabled => true` y volvió HTTP 200.
- **295 comprobaciones de renderizado/enlaces** del proyecto aprobadas, sintaxis PHP/JS y navegación de agenda con DOM simulado aprobadas. Sintaxis PowerShell del script de cierre comprobada; no se ejecutó el cierre porque el enlace debe seguir disponible.
- Pendiente: observación visual del renderizado y funcionamiento en navegador de escritorio/móvil con capturas actuales del usuario. Las comprobaciones HTTP verifican entrega, estructura y bloqueos, no apariencia ni interacción visual real. Sin BMAD ni Playwright. Continúa la advertencia preexistente de `xml` en PHP CLI; no impide estas comprobaciones y no se cambió configuración global.

Repetir validación pública mientras esté activo:

```powershell
php tests/demo-http.php https://get-winter-month-appear.trycloudflare.com
```

Las pruebas operativas `tests/checkout-http.php` deben ejecutarse únicamente después de cerrar esta presentación y restaurar el servidor local normal; con demo activa se espera bloqueo de esos flujos.
