=== Golden Shark Admin Panel ===
Contributors: carlosgallegos
Donate link: https://example.com/
Tags: administración, leads, eventos, frases, notas, panel interno
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.7
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

Pensado para ser una herramienta **privada**, segura y personalizable para equipos de trabajo o franquicias.

== Características ==

* Gestión de eventos internos con exportación a CSV.
* Registro de leads y formulario público mediante shortcode.
* Base de frases motivacionales con shortcode.
* Notas internas con buscador, edición y exportación.
* Historial de cambios automatizado.
* Configuración de mensaje motivacional, color del panel y más.
* Carga de estilos y scripts personalizados.
* Solo accesible para usuarios administradores o editores autorizados.

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

== Seguridad ==

Todas las acciones están protegidas con `nonce` y verificaciones de capacidad de usuario. Solo administradores o editores (según permisos) pueden acceder a cada sección.

== Capturas de pantalla ==

1. Panel principal con resumen y frase diaria.
2. Gestión de eventos.
3. Registro de leads.
4. Frases motivacionales.
5. Notas internas con buscador.
6. Configuraciones del plugin.
7. Historial de actividad.

== Changelog ==

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