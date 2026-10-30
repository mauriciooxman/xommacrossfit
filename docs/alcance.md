# Alcance y decisiones

## Etapa 1 · implementada localmente

Web pública renderizada en PHP: inicio, identidad y comunidad, CrossFit, planes y horarios, identidad visual/galería, preguntas frecuentes, ubicación y contacto. Entrenadores, fotos y enlaces oficiales se muestran al completar información real en `config/site.php`. Sin precios, ubicaciones, clases adicionales, credenciales ni testimonios inventados. No se presupone afiliación oficial a CrossFit.

La referencia https://www.accioncrossfit.cl/ se pudo consultar el 30-10-2026: se tomó su recorrido de navegación hacia comunidad, entrenamiento, horarios, planes y contacto. No se usaron su código, imágenes, testimonios, promesas ni textos. Colores y recursos provienen del logo entregado.

Aceptación: navegación sin enlaces ficticios; teclado y menú móvil accesibles; contraste; HTML semántico en español; un H1; metadatos; imágenes WebP locales con dimensiones, lazy loading fuera de portada; movimiento reducido; página útil sin JS. Ningún dato externo, formulario operativo, recepción de mensajes ni dependencia de base de datos. Falta contenido oficial para convertir la presentación en captación efectiva.

Arquitectura: `public/index.php` → carga privada/configuración → vista escapada. Etapas con persistencia incorporarán servicios y repositorios PDO separados, no consultas en vistas. No se añadió una capa de datos vacía ni un framework. Aplicación preparada para MySQL/MariaDB; sin Docker, Redis, SPA ni Composer obligatorio ahora.

## Etapa 2 · solo diseño

Base propia alimentada por exportaciones **autorizadas**; Boxmagic sigue siendo fuente de verdad. Revisar una muestra anonimizada antes de crear SQL, importador o panel. [Modelo y reglas](modelo-datos.md). Reservas y cobros siguen en Boxmagic.

Administradores: `password_hash`/`password_verify`, autorización por rol en servidor, cookies Secure/HttpOnly/SameSite, rotación del ID tras login, expiración e intentos limitados. CSRF en toda mutación, escape contextual, PDO preparado, errores genéricos y registros privados minimizados. Secretos fuera de Git y del área pública. Formularios de interesados solo al decidir receptor o almacenamiento, finalidad, aviso de privacidad, validación de servidor, límites de frecuencia y antispam (honeypot + tiempo mínimo, con alternativa accesible). Mostrar éxito exclusivamente tras persistencia o aceptación real por SMTP, aclarando que aceptación SMTP no garantiza entrega.

## Etapa 3 · solo diseño

Primera entrega: resumen **interno** a administrador configurado. Campañas/promociones desactivadas. Sin correos enviados en esta implementación.

Reglas propuestas, sujetas a confirmar semántica de exportación:

| Estado | Criterio |
| --- | --- |
| Próxima a vencer | Vigente, con fin confirmado dentro de `warning_days` configurable. |
| Vencida | Pasó el fin de vigencia confirmado; no equivale a abandono. |
| Posible no renovación | Vencida, superó `grace_days`, datos recientes y cobertura suficiente, sin renovación ni membresía posterior. Es una hipótesis revisable. |
| Baja confirmada | Estado explícito con evidencia/origen y fecha; nunca inferido por ausencia. |
| Desconocido | Actualización mayor que `max_data_age_hours`, cobertura insuficiente o fechas/identidad ambiguas. Solicitar nueva exportación; no afirmar no renovación. |

Usar fecha local America/Santiago, valores de umbral configurables aprobados por los dueños; no fijarlos arbitrariamente. Evaluar primero frescura/cobertura por cliente y fuente, bajas explícitas y membresías sucesoras. Una membresía futura, prórroga o renovación cancela/suprime la hipótesis previa. Exportar solo activos no permite determinar no renovaciones: requiere cobertura adecuada de historial y renovaciones o revisión humana. No usar únicamente la hora de subida como prueba de actualidad.

Proceso futuro: simulación → candidatos con evidencia, última fecha de fuente y motivo → comprobación de renovación/frescura otra vez antes de enviar → registro de resultado. Resumen interno con mínima información personal. Regla versionada y clave única por cliente/membresía/regla/ventana impiden duplicados. Cron si está disponible; alternativamente botón administrativo con CSRF y autorización que invoca el mismo servicio. Nunca endpoint público disparador.

SMTP configurable mediante biblioteca mantenida (por ejemplo PHPMailer, a seleccionar al implementar), credenciales privadas, TLS, tiempos límite, 3 intentos como máximo con espera creciente y clasificación de errores temporales/permanentes. Reserva transaccional del aviso y bloqueo con vencimiento para concurrencia; reintentar el mismo aviso, no crear otro. SMTP no garantiza exactamente una entrega: si se corta tras aceptar el mensaje, marcar resultado incierto y revisar manualmente antes de reenviar. Simulación no envía ni consume la clave definitiva.

Campañas a alumnos requieren aprobación explícita de dueños, condiciones y vigencia de promociones, base de autorización por canal/finalidad, preferencias, exclusiones, baja sencilla y límites de frecuencia. Estar en Boxmagic no implica consentimiento comercial. No se diseñan ni activan campañas ejecutables aquí.
