# 🛣️ Golden Shark Admin Panel – Roadmap

Este documento describe el plan de evolución del plugin **Golden Shark Admin Panel**, orientado a mejoras progresivas, usabilidad, visualización y preparación para entornos de franquicia o multisitio.

---

## ✅ Versión actual
**v2.3** – Automatizaciones, seguridad avanzada y multisitio inteligente 

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

## 🌐 v2.0 – Multisitio (fase 1 completada)
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

## 🧠 v2.2 – Mejora de experiencia de usuario (UX) y visualización  
**Objetivo:** hacer el plugin más intuitivo y claro para cualquier administrador o editor.  
- [x] Rediseño visual de tablas y formularios con estilo más moderno.  
- [x] Mostrar mensajes de acción más descriptivos y visuales.  
- [x] Nuevas etiquetas o filtros rápidos en listas largas (eventos, leads, frases).  
- [x] Agrupar módulos similares por secciones (UX).  
- [x] Añadir botón “Ir al inicio” o anclajes en vistas largas.

---

## 🤖 v2.3 – Automatizaciones, seguridad avanzada y multisitio inteligente  
**Objetivo:** aumentar la eficiencia del sistema y reforzar su solidez.  
- [x] Soporte para tareas programadas (ej. borrar frases antiguas, enviar resumen diario).  
- [x] Logs con IP, navegador y origen para actividades críticas.  
- [x] Protección adicional con roles personalizados (`gs_editor`, `gs_supervisor`).  
- [x] Panel Multisitio con edición remota por sitio (opcional).  
- [x] Widgets dinámicos según tipo de usuario o contexto del sitio.

---

## 🧩 v2.4 – Integración, extensibilidad y eficiencia operativa  
**Objetivo:** facilitar la interoperabilidad del plugin con otros sistemas, mejorar su modularidad y acelerar tareas frecuentes.

- [x] Soporte para Webhooks personalizados (alta de leads, creación de eventos, etc).
- [x] Shortcode `[mi_historial]` para mostrar el historial personal del usuario conectado.
- [x] Mejoras en el formulario público de leads: campos dinámicos, validaciones visuales.
- [x] Exportación inteligente de leads y eventos con filtros.
- [x] Nueva API interna para programadores (endpoint interno con autenticación básica).

---

## 🚀 v2.5 – Productividad y automatización avanzada
**Obejtivo:** facilitar el trabajo en equipo, reducir tareas repetitivas y mejorar la gestión interna con funciones inteligentes.

- [ ] Asistente de tareas: sugerencias automáticas de tareas según eventos o leads registrados.
- [ ] Recordatorios por correo: enviar correos automáticos a responsables de tareas próximas (con cron interno).
- [ ] Webhook de eventos: al registrar o editar eventos, se dispara un webhook opcional configurado.
- [ ] Vista Kanban de tareas internas: alternativa visual al listado plano de tareas (pendiente, en progreso, completado).
- [ ] Informe mensual automático (en PDF o CSV): resumen por email del total de leads, eventos y tareas.
- [ ] Buscador inteligente en el historial y logs (filtro por IP, usuario, fecha o palabra clave).
- [ ] Perfil de usuario interno: mostrar en el panel info personal del usuario (tareas asignadas, acciones recientes).

---

*Última actualización: 2025-05-19*
