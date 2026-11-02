<!-- oxman-eficiencia-v1:inicio -->
## Preferencias personales: eficiencia y aprendizaje
- Responde en espanol, de forma breve. Cierra con cambios, validacion y pendientes reales.
- Si pido aprender, explica el concepto y un ejemplo pequeno; si pido implementar, ejecuta sin tutorial extenso.
- Delimita objetivo y criterios de aceptacion. Haz cambios pequenos y evita refactorizaciones ajenas.
- Localiza primero archivos y simbolos relevantes; lee fragmentos dirigidos. Amplia solo cuando falte evidencia.
- Excluye dependencias, builds, binarios y logs de busquedas generales. No omitas instrucciones aplicables.
- Limita salida de herramientas. No imprimas registros completos, secretos ni catalogos completos de herramientas.
- Reutiliza lecturas y resultados vigentes; repite verificaciones si hay cambios o dudas concretas.
- Ejecuta pruebas proporcionadas al riesgo y todos los checks obligatorios. No reduzcas validacion para ahorrar tokens.
- Usa un agente por defecto. Delega solo por solicitud explicita o requisito de un workflow aplicable; evita duplicar trabajo.
- Al delegar, entrega objetivo, archivos, criterios y formato breve; evita heredar historia innecesaria si puedes elegir.
- Carga skills y conectores relevantes a la tarea. No invoques workflows adicionales por rutina.
- Con BMAD, usa los comandos y opciones de la version instalada; conserva sus pasos requeridos.
- Una solicitud de edicion manual o aprendizaje paso a paso no debe activar BMAD salvo que lo pida.
- Mantiene notas breves de decisiones y siguiente paso cuando sirvan para retomar; no dupliques documentacion.
- No cambies modelos, permisos, dependencias o configuracion global solo para ahorrar consumo sin mi solicitud.
- Distingue lo preparado de lo aplicado y lo ejecutado de lo verificado. No inventes ahorros porcentuales.
<!-- oxman-eficiencia-v1:fin -->

## Diseño de Xommafitnes
- Impeccable (`C:/Users/mauro/.agents/skills/impeccable/`): guía principal de diseño, revisión y acabado. Leer solo la referencia necesaria.
- Emil Kowalski (`emil-design-eng`): consultar únicamente para animaciones, transiciones o detalles de interacción.
- Taste (`design-taste-frontend`): consultar únicamente cuando el usuario solicite explorar otra dirección visual. Su descripción genérica no activa su uso en otras tareas.
- No cargar las tres skills completas automáticamente. Las instrucciones del usuario y la identidad del proyecto prevalecen sobre las preferencias y workflows genéricos de las skills.
- Conservar PHP, CSS y JavaScript actuales; logo, negro y amarillo, fotos reales y CrossFit, Halterofilia, HYROX y Musculación. Priorizar legibilidad, jerarquía, espaciado y coherencia.
- Sin BMAD, migraciones a React/Tailwind u otro stack, nuevas dependencias por diseño, Figma MCP, animaciones pesadas o efectos solo decorativos. Respetar `prefers-reduced-motion`. No inventar datos comerciales ni sustituir material real.
- No instalar, ejecutar ni usar Playwright ni su MCP. Omitir cualquier paso de skill que lo requiera. La revisión visual queda pendiente de capturas de escritorio y móvil compartidas por el usuario; distinguir evidencia de código de observación visual.
- Usar validaciones de sintaxis, recursos y enlaces sin Playwright. No descargar motores, habilitar hooks ni añadir herramientas adicionales como efecto secundario de consultar una skill; si faltan, leer sus referencias y el contexto local e indicar la limitación.
- La configuración inicial propuso mejoras; el usuario autorizó después refinamientos visuales acotados. No interpretar esto como permiso para un rediseño completo. Registro y validación pendiente: `docs/skills-diseno.md`.
