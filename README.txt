=== Golden Shark Admin Panel ===
Contributors: carlosgallegos  
Donate link: https://example.com/  
Tags: administración, leads, eventos, frases, notas, panel interno  
Requires at least: 5.8  
Tested up to: 6.5  
Stable tag: 3.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Plugin de administración interna para gestionar eventos, leads, frases, notas y configuraciones desde el panel de WordPress. Diseñado para uso privado en empresas.

== Descripción ==

Golden Shark Admin Panel es un plugin de administración exclusivo que permite a los administradores gestionar:

- Eventos internos de la organización.
- Leads capturados mediante formulario público o manualmente.
- Frases y mensajes motivacionales.
- Notas internas del equipo.
- Historial de actividad con registro automático.
- Panel de configuración para personalizar colores, mensajes y notificaciones.
- Tareas internas con estados, responsable y vista Kanban.
- Shortcodes útiles para front-end y automatizaciones.
- Soporte completo para multisitio y roles personalizados.

Pensado para ser una herramienta **privada**, segura y personalizable para equipos de trabajo o franquicias.

== Características ==

* Gestión de eventos internos con exportación a CSV.
* Registro de leads y formulario público mediante shortcode.
* Base de frases motivacionales con shortcode.
* Notas internas con buscador, edición y exportación.
* Historial de cambios automatizado por objeto y usuario.
* Configuración de mensaje motivacional, color del panel y notificaciones.
* Soporte para tareas internas con vista de lista o Kanban.
* Shortcodes para mostrar historial, tareas, eventos y frases.
* Carga de estilos y scripts personalizados.
* Soporte para adjuntar imágenes en leads y eventos.
* Soporte completo para multisitio: configuración global y edición remota.
* Panel exclusivo para superadministradores con vista de red.
* Roles personalizados (`golden_shark_acceso_basico`, `golden_shark_configuracion`, etc).
* API interna REST para integrar leads y eventos desde servicios externos.
* Reportes automáticos en CSV por email (semanales).
* Bitácora de accesos y acciones críticas con logs detallados.

== Instalación ==

1. Sube la carpeta `golden-shark-admin-panel` a `/wp-content/plugins/`.
2. Activa el plugin desde el menú "Plugins" en WordPress.
3. Ve al nuevo menú “Golden Shark 🦈” en el panel de administración.
4. Configura tus preferencias desde la pestaña “Configuración”.

== Shortcodes ==

* `[frase_motivacional]` – Muestra una frase aleatoria del sistema.
* `[formulario_lead]` – Muestra el formulario público de leads.
* `[lista_eventos]` – Lista todos los eventos internos registrados.
* `[nota_aleatoria]` – Muestra una nota interna aleatoria si las notificaciones están activadas.
* `[total_leads]` – Muestra el número total de leads registrados.
* `[tareas_pendientes]` – Muestra en el front-end la lista de tareas pendientes.
* `[kanban_tareas]` – Muestra las tareas internas en vista tipo Kanban.
* `[mi_historial]` – Muestra el historial personal del usuario conectado.

== Seguridad ==

Todas las acciones están protegidas con `nonce` y verificaciones de capacidad de usuario. Solo administradores o editores (según permisos) pueden acceder a cada sección. Soporte para roles personalizados.

== Capturas de pantalla ==

1. Panel principal con resumen y frase diaria.
2. Gestión de eventos.
3. Registro de leads.
4. Frases motivacionales.
5. Notas internas con buscador.
6. Configuraciones del plugin.
7. Historial de actividad.
8. Tareas internas con vista tipo Kanban.
9. Panel multisitio para superadministrador.

== Changelog ==

= 3.0 =
* Soporte para adjuntar documentos internos (PDF, Word) en eventos.
* Permisos avanzados condicionales por tipo de contenido (eventos, leads, configuración).
* Nuevo editor visual de reportes tipo dashboard con métricas internas personalizables.
* Validación estricta en subida de archivos según extensión y tipo MIME.
* Mejora de seguridad en interacciones multisitio y logs de actividad extendidos.

= 2.9 =
* Control avanzado de roles y permisos por módulo.
* Bitácora de acceso con logs detallados por usuario y objeto.
* Reportes automáticos en CSV enviados semanalmente por email.
* API REST interna para registrar leads y consultar eventos (con autenticación por clave).
* Mejora de validación y seguridad en los formularios del panel.
* Logs más claros y precisos en la bitácora de actividad.

= 2.8 =
* Editor visual para notas internas con soporte TinyMCE.
* Campos personalizados en formularios de leads (text, select, checkbox).
* Filtro de búsqueda global tipo "command palette" en todas las vistas.
* Mejoras de usabilidad en formularios y visualización contextual de campos.

= 2.7 =
* Notificaciones internas por rol asignado (ej. tareas asignadas, cambios críticos).
* Historial detallado de edición por objeto (leads, eventos, notas).
* Soporte para imágenes adjuntas en eventos y leads.
* Mejora de accesibilidad: etiquetas `aria`, navegación por teclado, roles en tablas.
* Optimización de rendimiento en vistas largas y formularios.
* Panel de configuración dividido por pestañas: generales, webhooks y limpieza.
* Registro de cambios de configuración en los logs.

= 2.6 =
* Sistema de etiquetas para eventos, leads y tareas.
* Favoritos por usuario para frases, notas y eventos.
* Filtros combinados inteligentes (tipo + fecha + etiqueta).
* Vista resumen por usuario: historial, eventos, tareas.
* Limpieza programada de registros antiguos.
* Exportación avanzada con selección de columnas y rangos.
* Webhook personalizado con payload configurable.
* Logs extendidos: cambios de configuración y shortcodes ejecutados.
* Soporte completo para internacionalización (.pot, .po, .mo).
* Vista personalizada por usuario (lista o kanban) en tareas internas.

= 2.5 =
* Asistente de tareas: se crean tareas automáticamente según el tipo de evento registrado.
* Webhook de eventos: se dispara un webhook configurado al registrar o editar un evento.
* Recordatorios por correo: se envían correos automáticos diarios a los responsables de tareas próximas (cron interno).
* Vista Kanban de tareas: nueva visualización con columnas para tareas pendientes, en progreso y completadas.
* Informe mensual: se genera un resumen automático en CSV y se envía por email (leads, eventos, tareas).
* Buscador inteligente en logs: búsqueda por IP, usuario, fecha o palabra clave.
* Perfil del usuario: muestra tareas asignadas, historial reciente, última conexión y datos personales.

= 2.4 =
* Se añadió soporte para webhooks personalizados (alta de leads, creación de eventos, etc).
* Nuevo shortcode `[mi_historial]` para mostrar el historial personal del usuario conectado.
* Mejoras visuales y de validación en el formulario público de leads.
* Exportación inteligente de leads y eventos con filtros aplicables.
* Implementación de una API interna REST con autenticación por clave privada.

= 2.3 =
* Añadido soporte para tareas programadas (cron jobs).
* Nuevo sistema de logs con IP, navegador y página de origen.
* Pantalla de visualización de logs para superadministradores.
* Soporte para edición remota de frases y configuración por sitio (modo multisitio).
* Registro de historial remoto por sitio (`gs_historial_sitio_{ID}`).
* Preparación para roles personalizados avanzados.

= 2.2 =
* Rediseño visual moderno en todo el panel.
* Mensajes de acción visuales más claros y contextuales.
* Filtros rápidos para frases, eventos y leads.
* Nuevos estilos CSS y experiencia más intuitiva.
* Botón “Ir al inicio” en pantallas extensas.

= 2.1 =
* Añadido panel exclusivo para superadministrador en el sitio principal.
* Nuevas pantallas para editar frases globales y configuraciones compartidas entre sitios.
* Listado de sitios de la red disponible en el panel.
* Mejora en la seguridad de acceso a configuraciones globales.

= 2.0 =
* Compatibilidad con multisitio (franquicias / redes de sitios).
* Frases y configuraciones ahora se comparten globalmente entre sitios.
* Las notas internas se mantienen locales para cada sitio.
* Se reorganizó el código para soportar `get_site_option()` cuando aplique.

= 1.9 =
* Alertas visuales si hay más de X eventos programados para hoy.
* Alertas si hay más de Y leads sin revisar.
* Tabla en el dashboard con los leads no revisados.
* Opción para marcar un lead como revisado directamente desde el panel principal.

= 1.8 =
* Nuevo módulo “Calendario de Eventos” con vista mensual.
* Soporte para tipos de eventos y colores personalizados.
* Tooltip con información del evento.

= 1.7 =
* Se agregó el módulo de Tareas internas.
* Soporte para edición rápida, historial y eliminación de tareas.
* Nuevo shortcode [tareas_pendientes] para mostrar tareas pendientes.

= 1.6 =
* Nueva opción: exportar historial individual del usuario en CSV.
* Protección de seguridad añadida al formulario con nonce.

= 1.5 =
* Registro de historial individual por usuario.
* Visualización del historial personal en el panel principal.

= 1.4 =
* Se añadió gráfico de resumen al dashboard usando Chart.js.
* Se integró paso de datos de PHP a JavaScript con `wp_localize_script`.

= 1.3 =
* Se agregó widget al escritorio principal de WordPress con resumen de eventos, leads y frases.

= 1.2 =
* Sistema de notificaciones internas tras crear, editar o eliminar elementos.
* Mensajes automáticos visibles en el panel.

= 1.1 =
* Nuevos shortcodes: `[lista_eventos]`, `[nota_aleatoria]`, `[total_leads]`.
* Mejora visual del panel principal.
* Estilos centralizados en `admin-style.css`.

= 1.0 =
* Versión inicial del plugin con funcionalidades completas.

== Licencia ==

Este plugin está licenciado bajo la GPLv2 o posterior.