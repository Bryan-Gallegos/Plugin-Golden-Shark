# 🛣️ Golden Shark Admin Panel – Roadmap

Este documento describe el plan de evolución del plugin **Golden Shark Admin Panel**, orientado a mejoras progresivas, usabilidad, visualización y preparación para entornos de franquicia o multisitio.

---

## ✅ Versión actual
**v2.9** – Seguridad y reportes avanzados

## ✅ v1.0 - Lanzamiento Base
- [x] Sistema básico de leads, eventos y frases.
- [x] Shortcodes esenciales ( `[lista_eventos]`, `[nota_aleatoria]`, `[total_leads]`).
- [x] Estilos CSS unificados en `admin-style.css`.

---

## ✅ v1.1 - Personalización y Shortcodes
- [x] Nuevos shortcodes: `[lista_eventos]`, `[nota_aleatoria]`, `[total_leads]`.
- [x] Rediseño visual del dashboard.

---

## ✅ v12 - Notificaciones Internas
- [x] Notificaciones automáticas al crear/editar/eliminar registros.
- [x] Mensajes de éxito personalizados por usuario.

---

## ✅ v1.3 - Widget de Resumen
- [x] Widget de WordPress con resumen de eventos, leads y frases.

---

## ✅ v1.4 - Gráficos en Dashboard
- [x] Integración de Chart.js para gráficos interactivos.
- [x] Paso seguro de datos PHP -> JavaScript con `wp_localize_script`.

---

## ✅ v1.5 - Historial Personalizado
- [x] Historial individual por usuario.
- [x] Visualización del historial en el dashboard.
- [x] Base para futuras exportaciones (CSV/PDF).

---

## ✅ v1.6 – Exportaciones avanzadas y mejoras UX
- [x] Exportar historial individual por usuario (CSV).
- [x] Mejoras visuales con íconos o badges en listas.
- [x] Autoenfoque en campos al editar.
- [x] Mensajes de validación más claros.

---

## ✅ v1.7 – Módulo de tareas / pendientes
- [x] Nuevo módulo “Tareas internas”.
- [x] Campos: título, estado, fecha y responsable.
- [x] Historial de tareas y edición rápida.
- [x] Shortcode: `[tareas_pendientes]`.

---

## ✅ v1.8 – Calendario de eventos
- [x] Vista tipo calendario con FullCalendar.
- [x] Filtro por mes o tipo de evento.
- [x] Colores por tipo de evento.

---

## ✅ v1.9 – Sistema de alertas / notificaciones visuales
- [x] Alerta visual si hay más de X eventos hoy.
- [x] Alerta si hay más de Y leads sin revisar.
- [x] Tabla de leads sin revisar con botón de marcar como revisado.

---

## ✅ v2.0 – Multisitio (fase 1 completada)
- [x] Compartir frases y configuración entre sitios.
- [x] Migración automática de frases y configuración a `site_option()`.
- [x] Panel central para administrar múltiples sitios (fase 2).
- [x] Mejoras adicionales de rendimiento y seguridad en red.

---

## ✅ v2.1 – Multisite Control Panel (Fase 2)
**Objetivo:** dotar al superadministrador de una vista global centralizada.

- [x] Agregar menú exclusivo “🌐 Panel Multisitio” solo visible en el sitio principal.
- [x] Crear pantalla para editar frases globales (`frases_globales.php`).
- [x] Crear pantalla para editar configuración global (`config_global.php`).
- [x] Mejorar funciones de seguridad con verificación de superadmin.
- [x] Añadir listado de sitios de la red (solo vista).

🎯 Esta versión finaliza el soporte base multisitio para entornos de franquicia.

---

## ✅ v2.2 – Mejora de experiencia de usuario (UX) y visualización  
**Objetivo:** hacer el plugin más intuitivo y claro para cualquier administrador o editor.  
- [x] Rediseño visual de tablas y formularios con estilo más moderno.  
- [x] Mostrar mensajes de acción más descriptivos y visuales.  
- [x] Nuevas etiquetas o filtros rápidos en listas largas (eventos, leads, frases).  
- [x] Agrupar módulos similares por secciones (UX).  
- [x] Añadir botón “Ir al inicio” o anclajes en vistas largas.

---

## ✅ v2.3 – Automatizaciones, seguridad avanzada y multisitio inteligente  
**Objetivo:** aumentar la eficiencia del sistema y reforzar su solidez.  
- [x] Soporte para tareas programadas (ej. borrar frases antiguas, enviar resumen diario).  
- [x] Logs con IP, navegador y origen para actividades críticas.  
- [x] Protección adicional con roles personalizados (`gs_editor`, `gs_supervisor`).  
- [x] Panel Multisitio con edición remota por sitio (opcional).  
- [x] Widgets dinámicos según tipo de usuario o contexto del sitio.

---

## ✅ v2.4 – Integración, extensibilidad y eficiencia operativa  
**Objetivo:** facilitar la interoperabilidad del plugin con otros sistemas, mejorar su modularidad y acelerar tareas frecuentes.

- [x] Soporte para Webhooks personalizados (alta de leads, creación de eventos, etc).
- [x] Shortcode `[mi_historial]` para mostrar el historial personal del usuario conectado.
- [x] Mejoras en el formulario público de leads: campos dinámicos, validaciones visuales.
- [x] Exportación inteligente de leads y eventos con filtros.
- [x] Nueva API interna para programadores (endpoint interno con autenticación básica).

---

## ✅ v2.5 – Productividad y automatización avanzada
**Objetivo:** facilitar el trabajo en equipo, reducir tareas repetitivas y mejorar la gestión interna con funciones inteligentes.

- [x] Asistente de tareas: sugerencias automáticas de tareas según eventos o leads registrados.
- [x] Recordatorios por correo: enviar correos automáticos a responsables de tareas próximas (con cron interno).
- [x] Webhook de eventos: al registrar o editar eventos, se dispara un webhook opcional configurado.
- [x] Vista Kanban de tareas internas: alternativa visual al listado plano de tareas (pendiente, en progreso, completado).
- [x] Informe mensual automático (en PDF o CSV): resumen por email del total de leads, eventos y tareas.
- [x] Buscador inteligente en el historial y logs (filtro por IP, usuario, fecha o palabra clave).
- [x] Perfil de usuario interno: mostrar en el panel info personal del usuario (tareas asignadas, acciones recientes).

---

## ✅ v2.6 - Integración avanzada y personalización por usuario
**Objetivo:** mejorar el rendimiento interno, permitir vistas personalizadas y aumentar la interoperabilidad del plugin con servicios externos.

- [x] Sistema de **etiquetas** para eventos, leads y tareas (filtrado más preciso).
- [x] **Favoritos** por usuario: marcar frases, notas o eventos destacados.
- [x] Filtros combinados inteligentes en listas (tipo + fecha + etiqueta).
- [x] Vista resumen por usuario: tareas asignadas, eventos relevantes, historial y últimas acciones.
- [x] Limpieza programada de registros antiguos (leads, eventos, tareas completadas).
- [x] Exportación avanzada: permite elegir columnas y rango de fechas.
- [x] Webhook personalizado con payload ajustable (por sección).
- [x] Logs extendidos: guardar cambios de configuración y ejecuciones de shortcodes.
- [x] Soporte para traducción (`.pot`) e internacionalización.
- [x] Vista personalizada por usuario (lista o kanban) en el módulo de tareas.

---

## ✅ v2.7 - Notificaciones, historial y multimedia
**Objetivo:** fortalecer la trazabilidad de acciones, mejorar la comunicación interna según roles y permitir archivos multimedia en notas y eventos.

- [x] **Notificaciones por roles específicos** (tareas asignadas, cambios críticos).
- [x] **Historial de edición detallado** por objeto (evento, lead, nota).
- [x] **Soporte para imágenes adjuntas** en eventos y leads.
- [x] **Mejoras en accesibilidad y rendimiento.**
- [x] **Panel de configuración mejorado con pestañas.**

---

## 🚀 v2.8 – Sincronización externa y personalización visual
**Objetivo:** conectar con herramientas externas y permitir mayor personalización visual en el flujo de trabajo.

- [x] Añadir editor visual para notas internas (TinyMCE o Markdown).
- [x] Permitir campos personalizados en formularios de leads (text, select, checkbox).
- [x] Añadir filtro de búsqueda global tipo “command palette” desde cualquier vista.

---

## 🧠 v2.9 – Seguridad y reportes avanzados
**Objetivo:** monitorear con más precisión, prevenir fallos humanos y generar reportes automáticos útiles.

- [x] Control avanzado de roles y permisos por módulo.
- [x] Bitácora de acceso al sistema con alertas de seguridad.
- [x] Reportes semanales programados (PDF o CSV por email).
- [x] Integración con servicios externos vía API REST.

---

## 📌 v3.0 - Soporte de documentos y permisos
**Objetivo:** transformar el plugin en un hub colaborativo con gestión de archivos, permisos contextuales y dashboards personalizables para equipos multisitio.
- [x] Soporte para adjuntar documentos internos (PDF, Word).
- [x] Mejorar permisos con condiciones por tipo de contenido.
- [x] Editor visual de reportes personalizados (tipo dashboard).

---

# 🧠 Filosofía del Plugin

Golden Shark Admin Panel busca ser un **sistema interno robusto, privado y modular**, adaptable a redes multisitio y equipos de trabajo que requieren organización, control y mejora continua.

---

*Última actualización: 2025-06-09*
