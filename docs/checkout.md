# Checkout local de planes

Implementado con PHP 8.3, PDO MySQL y `transbank/transbank-sdk` 5.1.0, fijado en `composer.lock`. Sin BMAD, Playwright, correo, publicación ni cobros reales. El constructor de la pasarela usa exclusivamente `Options::ENVIRONMENT_INTEGRATION`, `Options::INTEGRATION_API_KEY` y `WebpayPlus::INTEGRATION_COMMERCE_CODE`. La configuración rechaza un ambiente distinto de `integration`; no hay selector de producción.

## Ejecutar localmente

1. `composer install --no-interaction --prefer-dist` desde la raíz. Se necesitan PHP 8.3+, PDO MySQL, curl, mbstring, JSON y sesiones. No usar `php -n` para este flujo.
2. Configurar una base MySQL/MariaDB con InnoDB y `utf8mb4`. Copiar `config/checkout.example.php` a `config/checkout.local.php`, completar DSN/usuario/contraseña y `base_url`. Este archivo está fuera de `public/` e ignorado por Git. No sobrescribir el archivo local ya preparado si se desea conservar esta instancia.
3. `php bin/migrate.php`. El ejecutor registra versiones y tolera repetir la migración; no elimina tablas. Para hosting que importe SQL, existe `database/migrations/001_checkout.sql`. Respaldar antes de migrar cualquier base con datos.
4. `php -S 127.0.0.1:8080 -t public`. Abrir `http://127.0.0.1:8080/checkout.php`. Usar siempre el mismo host: `localhost` y `127.0.0.1` tienen cookies distintas.
5. Elegir uno de los siete planes, revisar el resumen, ingresar datos de prueba, crear orden y continuar a Webpay de pruebas. La pantalla intermedia envía `token_ws` por POST a la URL oficial; no hay formulario local de tarjeta.

`storage/checkout-sessions/` se crea automáticamente y debe ser escribible por PHP. `public/` debe ser la única raíz servida: nunca exponer `config/`, `vendor/`, `database/`, `app/`, `storage/`, `bin/` ni `tests/`. Para el wrapper del README, copiar también la entrada `checkout.php` al directorio privado y crear su wrapper público con `XOMMA_ROOT`.

### Instancia preparada en esta entrega

Como no había servicio MySQL en 3306, se inicializó **una instancia independiente** usando el MySQL 8.0.30 ya instalado en Laragon. Se vinculó únicamente a `127.0.0.1:3307`, con datos en `storage/mysql-checkout/`, base `xommafitnes` y base exclusiva de tests `xommafitnes_checkout_test`. Se generaron contraseñas aleatorias; no se imprimieron ni se agregaron a Git. La cuenta de aplicación tiene permisos sobre esas dos bases, sin administración global. La configuración de Laragon y su base habitual no se modificaron.

Si la instancia está detenida, arrancarla en una terminal local (el proceso queda en primer plano):

```powershell
& 'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqld.exe' --no-defaults --basedir=C:/laragon/bin/mysql/mysql-8.0.30-winx64 --datadir=C:/Users/mauro/Desktop/OXMANIT/xommafitnes/storage/mysql-checkout --bind-address=127.0.0.1 --port=3307 --mysqlx=OFF --console
```

No volver a inicializar el directorio. La contraseña de administración local quedó en `storage/mysql-admin.php`, privado e ignorado por Git. Esta instancia es de desarrollo; no usar ese motor antiguo ni esta cuenta de migración como configuración de producción. El PHP instalado emite una advertencia preexistente sobre una entrada `xml` inválida de `php.ini`; no impidió las pruebas. No se modificó configuración global.

## Órdenes, pagos y protección

- Catálogo único: `load_site()` y `config/site.php`. Identificadores estables derivados de clases (`full-day-8` … `full-day-32`). El servidor ignora cualquier precio enviado por el navegador. Guarda título, identificador, clases, precio y condición en snapshot, más clases y monto consultables en columnas.
- `checkout_orders` separa `payment_status` de `activation_status`. La activación siempre queda `pending`; no se invoca ninguna API de Boxmagic. El resultado exige confirmar la habilitación con el gimnasio antes de reservar.
- `checkout_payment_attempts` guarda token, identificador de compra, sesión de Transbank, estado, retorno, fecha de intento de confirmación y campos permitidos de respuesta. No se guarda la respuesta completa ni datos de tarjeta. Cada reintento confirmado como fallido usa otro identificador y token dentro de la misma orden.
- Nonce estable de compra y clave única evitan duplicar órdenes al repetir POST. «Iniciar otra compra» expresa una compra nueva. `GET_LOCK` por orden serializa pagos y retornos entre procesos. Refrescar/volver reutiliza el intento en curso; una orden aprobada no inicia otro pago.
- Una cookie de propietario aleatoria de 256 bits dura 30 días, HttpOnly y SameSite=Lax; solo su hash queda en la orden. La referencia sola no autoriza acceso. La cookie usa Secure cuando `base_url` es HTTPS. La sesión privada protege formularios con CSRF y modo estricto. Respuestas privadas sin caché, CSP limitada a origen propio y formulario oficial de integración, sin referentes ni indexación.
- Conservar la URL de la orden y las cookies del navegador. Cerrar y abrir el navegador conserva el acceso mediante la cookie de propietario. Recuperación desde otro dispositivo, cookies borradas y recuperación por correo requieren un flujo posterior de identidad; no se inventó uno inseguro.

## Retorno, confirmación y consulta

El retorno contiene un identificador aleatorio de intento en `return_url`, de modo que incluso el GET sin campos por timeout se puede correlacionar. Se verifica el token contra el persistido; se rechazan token/retorno ambiguos, arrays y campos de orden/sesión discordantes. La devolución de Webpay no depende de la cookie de sesión enviada en POST entre sitios: procesa el intento conocido y responde 303; la siguiente vista exige propietario.

Antes de confirmar se consulta estado. Se envía `commit` ante retorno normal de ese intento o al recuperar una autorización remota, con monto/orden/sesión coincidentes y sin confirmación iniciada previamente. Nunca se confirma un pago meramente iniciado sin retorno normal. Se persiste `commit_started_at` **antes** de enviar la operación: después de una interrupción se usa `status`, nunca se repite ciegamente `commit`. Para aprobar se exige `AUTHORIZED`, código de respuesta entero `0`, código de autorización y coincidencia de monto/orden/sesión.

Rechazos y anulaciones se reflejan por estado remoto. Cancelación y expiración se registran como eventos de retorno; solo un estado remoto final fallido habilita reintento. Si Transbank aún responde `INITIALIZED` después de cancelar/expirar, o hay timeout/error/discrepancia, queda `verification_pending`. No se muestra éxito ni se permite otro cobro. Un estado `INITIALIZED` consultado antes de pagar conserva el token y permite continuar el mismo pago.

Consultar una orden desde su botón o ejecutar:

```powershell
php bin/check-payments.php
```

Este comando privado consulta hasta 100 intentos pendientes con token, priorizando los consultados hace más tiempo; no requiere navegador, no crea transacciones ni inicia cobros. Puede confirmar una autorización recuperada o una operación cuyo retorno normal ya se registró y cuya confirmación aún no se envió. No está publicado como endpoint. La consulta de Transbank tiene una ventana documentada de hasta 7 días; fuera de ella, una respuesta incierta queda pendiente y requiere investigación con el proveedor. Una creación con respuesta perdida **sin token** queda bloqueada para revisión técnica; no es posible consultar `status` sin token ni inventar un resultado. Esta entrega no incorpora panel administrativo.

## Transferencia

La configuración bancaria viene vacía. UI y servidor deshabilitan el método si falta titular, RUT, banco, tipo/número de cuenta o correo válido. No hay datos bancarios ficticios en configuración ni instrucciones públicas.

Al completar los seis campos privados, una transferencia crea solo una orden pendiente y copia esos datos bancarios al momento de compra. El resumen contiene monto y referencia para el abono. Ni subir un comprobante ni volver del navegador aprueban un pago. La revisión del abono, responsable y panel administrativo están pendientes de definición.

## Pruebas y evidencia

```powershell
php -n tests/smoke.php
node tests/agenda.js
node --check public/assets/js/site.js
php tests/checkout.php
php tests/checkout-http.php
composer validate --no-check-publish
composer check-platform-reqs
```

`tests/checkout.php` usa **MySQL real + pasarela simulada**. Requiere una base exclusiva `xommafitnes_checkout_test` en el mismo servidor y permisos para crear tablas. Crea y limpia únicamente sus órdenes sintéticas. Verificó 40 condiciones: siete planes, snapshot, precio alterado, validación de datos, banco incompleto, propietario, POST repetido, mismo token, aprobado/rechazado/cancelado/expirado, retorno repetido sin doble commit, reintentos, activación pendiente, discrepancia de monto/orden/sesión/autorización, timeout, confirmación con respuesta perdida y recuperación por consulta con/sin retorno. Una fixture bancaria explícitamente simulada comprueba la orden pendiente con banco completo y se elimina al terminar.

`tests/checkout-http.php` necesita el servidor local en 8080. Verificó 13 condiciones HTTP: formulario y siete planes, privacidad/CSP, CSRF, rechazo backend de transferencia forzada, redirección 303, precio del servidor, repetición sin duplicar orden, activación pendiente, acceso denegado desde otro navegador, arrays y retorno inventado. Limpia su orden sintética sin pagos. No llama a Transbank.

Operación **real** ejecutada contra integración el 10 de noviembre de 2026:

```powershell
php tests/transbank-integration.php --create
```

Creó una transacción real de prueba mediante SDK, guardó el token en MySQL y consultó `INITIALIZED`. No realizó commit, ingreso de tarjeta ni pago aprobado. El script deja el intento para trazabilidad en la base de tests. Solo se ejecuta con `--create`; no forma parte de los tests rutinarios. Composer instaló y auditó dependencias sin alertas conocidas; la sintaxis PHP y las pruebas preexistentes también pasaron.

**Pendiente de operación real:** aprobación, rechazo y cancelación desde el formulario de Webpay, además de repetir el retorno real. Computer Use detuvo el acceso a Chrome porque no pudo verificar la URL activa con suficiente confianza; no se usó Playwright ni se evitó ese bloqueo. Las pruebas simuladas no sustituyen esa certificación. También queda pendiente la revisión visual mediante capturas de escritorio/móvil compartidas por el usuario; solo se verificaron estructura y reglas responsive en código.

### Pasos manuales para cerrar la validación de integración

1. Con MySQL y PHP activos, abrir checkout, usar un nombre/correo de prueba, crear orden y conservar su URL. Hacer un pago usando las [tarjetas oficiales de integración](https://github.com/TransbankDevelopers/transbank-developers-docs/blob/master/documentacion/como_empezar/README.md#ambientes). Los datos se ingresan solo en `https://webpay3gint.transbank.cl/`. No usar tarjetas reales.
2. Con VISA de prueba aprobada: verificar pago aprobado y activación pendiente. Refrescar resultado y volver desde Webpay; no debe surgir otra orden ni otro cobro. Consultar estado de nuevo y comparar monto con snapshot de la orden.
3. Iniciar explícitamente otra compra y usar Mastercard de prueba rechazada: verificar rechazo, habilitar reintento en la misma orden y comprobar otro registro de intento.
4. Iniciar otra compra y cancelar en Webpay: revisar evento de cancelación y consulta. Si el estado aún no es final, debe permanecer pendiente de verificación. Probar abandono hasta timeout (el ejemplo oficial indica 10 minutos en integración) y volver.
5. Cerrar navegador después de pasar por Webpay, abrir la URL de la orden con el mismo perfil y consultar estado. Para aislar el retorno, se puede ejecutar el comando CLI privado. No forzar un nuevo cobro si el estado es incierto.
6. Confirmar otra vez transferencia deshabilitada y acceso a orden denegado desde otro perfil. Manipular `price`/`amount` en una solicitud: el monto debe seguir siendo el del catálogo. Las pruebas automatizadas ya cubren esos ataques y pérdida de comunicación sin alterar pagos reales.

## Pendientes antes de producción

No habilitar producción con esta entrega. Faltan datos bancarios reales; confirmación de vigencia comercial de los planes; responsable y mecanismo de conciliación bancaria; contrato/certificación Transbank y credenciales productivas; HTTPS/dominio de retorno; PHP/MySQL mantenidos y configuración segura del hosting; usuario de aplicación de privilegios mínimos separado del migrador; respaldos y política de retención de datos; monitoreo/reconciliación periódica y alertas; límites de solicitudes y recuperación verificada de órdenes. Configurar logs para excluir tokens, cookies y datos personales. La habilitación productiva requiere trabajo separado, sin cambiar simplemente un flag.

Boxmagic: confirmar primero integración disponible, autenticación, identificación de alumno nuevo/existente y mecanismo de habilitación/confirmación, sin asumir endpoints. Hasta entonces toda activación queda pendiente.

## Fuentes oficiales consultadas

- [SDK PHP oficial, requisitos e instalación actual](https://github.com/TransbankDevelopers/transbank-sdk-php) y código **instalado** de `Options`, `WebpayPlus`, `Transaction` y respuestas para las firmas de 5.1.0.
- [Referencia REST oficial](https://github.com/TransbankDevelopers/transbank-developers-docs/blob/master/referencia/webpay/README.md), estados y validación de respuesta. Algunos fragmentos del portal aún muestran SDK 2.x; se usaron las firmas de la versión instalada.
- [Ejemplo PHP oficial de creación](https://proyecto-ejemplo-php.transbankdevelopers.cl/webpay-plus/create), formulario de redirección, y [retorno por timeout](https://proyecto-ejemplo-php.transbankdevelopers.cl/webpay-plus/commit).
- [Referencia de consulta oficial](https://proyecto-ejemplo-php.transbankdevelopers.cl/api-reference/webpay-plus), ventana de consulta de 7 días. El portal original devolvió 403/502 en algunas páginas; se consultaron sus repositorios y ejemplos oficiales.
