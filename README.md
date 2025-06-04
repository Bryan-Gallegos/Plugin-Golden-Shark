# 🦈 Golden Shark Admin Panel

**Plugin de administración interna para WordPress** que permite gestionar eventos, leads, frases motivacionales, notas internas y configuraciones personalizadas desde el panel de administración.

> Diseñado como una herramienta privada y flexible para equipos de trabajo, empresas o franquicias.

---

## 📦 Características principales

- ✅ Gestión de **eventos internos** con edición, eliminación y exportación a CSV.
- 📨 Registro y administración de **leads** manuales o desde formulario público vía shortcode.
- 💬 Sistema de **frases motivacionales** con visualización aleatoria.
- 🗒️ Panel de **notas internas** con buscador, edición y exportación.
- 🛠️ Módulo de **configuración** para personalizar color del dashboard, mensajes y notificaciones.
- 🕓 Registro automático en el **historial de actividad**.
- 🔐 Protección mediante **verificación de permisos** y `nonce` para seguridad en cada acción.
- 🌐 Panel multisitio para superadministradores con edición global de frases y configuración.
- 🧭 Listado central de sitios en red WordPress.

---

## 🚀 Instalación

1. Clona este repositorio o descarga el ZIP.
2. Sube la carpeta `golden-shark-admin-panel` a `/wp-content/plugins/`.
3. Activa el plugin desde el menú “Plugins” en WordPress.
4. Accede al nuevo panel lateral **“Golden Shark 🦈”** en el admin de WordPress.
5. Personaliza desde la pestaña “Configuración”.

---

## 🧩 Shortcodes disponibles

| Shortcode               | Función                                                                 |
|------------------------|------------------------------------------------------------------------|
| `[frase_motivacional]` | Muestra una frase aleatoria desde la base de datos                     |
| `[formulario_lead]`    | Muestra el formulario público de leads                                 |
| `[lista_eventos]`      | Lista todos los eventos internos guardados                             |
| `[nota_aleatoria]`     | Muestra una nota interna aleatoria (si las notificaciones están activas) |
| `[total_leads]`        | Muestra el total actual de leads capturados                            |
| `[tareas_pendientes]`  | Muestra una lista de tareas internas marcadas como pendientes           |
| `[mi_historial]`       | Muestra el historial personal del usuario conectado                    |
| `[kanban_tareas]`      | Muestra las tareas internas en vista tipo Kanban                       |

---

## 🔐 Seguridad

- Uso de `current_user_can()` para control de acceso.
- Validación de formularios con `wp_nonce_field()` y `wp_verify_nonce()`.
- Solo usuarios con roles de administrador o editor (según el caso) pueden acceder.

---

## 🖼️ Capturas de pantalla

1. 🧠 Panel principal con resumen y frase diaria  
2. 📅 Gestión de eventos  
3. 📨 Registro de leads  
4. 💬 Frases motivacionales  
5. 🗒️ Notas internas con buscador  
6. ⚙️ Configuraciones del plugin  
7. 📜 Historial de actividad  

---

## 🛠️ Personalización

Todas las configuraciones principales (colores, mensajes y notificaciones) están centralizadas en la pestaña **Configuración** del plugin.

---

## 🗓️ Historial de cambios

### v2.9 - Seguridad y reportes avanzados
- Control avanzado de roles y permisos por módulo (Ej. acceso básico, configuración, logs).
- Bitácora de accesos al sistema con logs detallados por usuario, acción, IP y origen.
- Reportes automáticos semanales generados en CSV y enviados por email (eventos, leads, tareas).
- API REST interna con autenticación por clave (API Key): permite registrar leads y consultar eventos de forma segura.
- Valdación de seguridad extra para todas las rutas expuestas y campos críticos.
- Mejoras en la trazabilidad de logs y en la depuración de eventos programados.

### v2.8 – Sincronización externa y personalización visual
- Editor visual para notas internas con soporte TinyMCE (más intuitivo y completo).
- Campos personalizados en formularios de leads: tipos `text`, `select` y `checkbox` desde la configuración.
- Filtro de búsqueda global tipo “command palette” disponible en todas las vistas principales.
- Mejoras en experiencia de usuario para campos condicionales y visualización de datos.

### v2.7 – Notificaciones, historial y multimedia
- Notificaciones internas por rol asignado (ej. tareas asignadas, cambios críticos).
- Historial detallado de edición por objeto (leads, eventos, notas).
- Soporte para imágenes adjuntas en eventos y leads.
- Mejora de accesibilidad: etiquetas `aria`, navegación por teclado, roles en tablas.
- Optimización de rendimiento en vistas largas y formularios.
- Panel de configuración mejorado con pestañas: Generales, Webhooks y Limpieza.
- Registro de cambios de configuración en los logs del sistema.

### v2.6 - Integración avanzada y personalización del usuario
- Sistema de **etiquetas** apra eventos, leads y tareas (filtrado más preciso).
- **Favoritos** por usuario: marcar frases, notas o eventos destacados.
- Filtros combinados inteligentes en listas (tipo + fecha + etiqueta).
- Vista resumen por usuario: tareas asignadas, eventos relevantes, historial y últimas acciones.
- Limpieza programada de registros antiguos (leads, eventos, tareas completadas).
- Exportación avanzada: permite elegir columnasy rangos de fecha.
- **Webhook** personalizado con payload ajustable (por sección).
- Logs extendidos: guardar cambios de configuración y ejecuciones de shortcodes.
- Soporte para traducción (**`.pot`**) e internacionalización.
- Vista personalizada por usuario (lista o kanban) en el módulo de tareas.

### v2.5 – Productividad y automatización avanzada
- Asistente de tareas: se crean tareas automáticamente según el tipo de evento registrado.
- Webhook de eventos: se dispara un webhook configurado al registrar o editar un evento.
- Recordatorios por correo: se envían correos automáticos diarios a los responsables de tareas próximas (cron interno).
- Vista Kanban de tareas: nueva visualización con columnas para tareas pendientes, en progreso y completadas.
- Informe mensual: se genera un resumen automático en CSV y se envía por email (leads, eventos, tareas).
- Buscador inteligente en logs: búsqueda por IP, usuario, fecha o palabra clave.
- Perfil del usuario: muestra tareas asignadas, historial reciente, última conexión y datos personales.

### v2.4
- Se añadió soporte para **webhooks personalizados** para alta de leads y creación de eventos.
- Nuevo **shortcode `[mi_historial]`** para mostrar el historial personal del usuario conectado.
- Mejoras en el **formulario público de leads**: validación visual y campos más claros.
- **Exportación inteligente** de leads y eventos con filtros aplicables.
- Implementación de una **API interna REST** con autenticación por clave privada para desarrolladores.

### v2.3
- Tarea programada semanal para borrar frases antiguas.
- Nuevo sistema de logs extendido con IP, navegador y origen.
- Se agregó `logs.php` con visualización para superadmins.
- Vista remota de sitios y edición de frases/config por sitio.
- Historial remoto individual (`gs_historial_sitio_{ID}`).
- Preparación para roles personalizados (`gs_editor`, `gs_supervisor`).

### v2.2
- Rediseño visual completo con interfaz moderna.
- Filtros rápidos en listas de eventos, leads y frases.
- Nuevos mensajes visuales con íconos y estilo WordPress.
- Botón de “Ir al inicio” en pantallas largas.
- Agrupación UX de módulos similares y limpieza visual.

### v2.1
- Añadido panel exclusivo para superadministradores desde el sitio principal.
- Edición centralizada de frases globales y configuración compartida.
- Nueva vista: listado de sitios de la red WordPress.
- Seguridad reforzada en accesos multisitio.

### v2.0
- Compatibilidad con multisitio (red de sitios).
- Las frases motivacionales y configuraciones ahora se almacenan a nivel global.
- Las notas internas se mantienen por sitio individual.
- Migración automática de datos locales a `site_option()` cuando se activa multisite.

### v1.9
- Se añadieron alertas visuales si hay más de cierto número de eventos o leads sin revisar.
- Nueva tabla en el dashboard con los leads no revisados.
- Preparado para añadir botón de "marcar como revisado" directamente desde el panel.

### v1.8
- Se agregó módulo de calendario de eventos con vista tipo FullCalendar.
- Eventos ahora tienen un campo de tipo (interno, reunión, lanzamiento).
- Colores personalizados por tipo de evento en el calendario.

### v1.7
- Se agregó el módulo de tareas internas con edición rápida.
- Se añadió el shortcode `[tareas_pendientes]` para listar tareas pendientes.

### v1.6
- Nueva opción: exportación del historial individual del usuario en CSV desde el panel principal.
- Añadida protección de seguridad con `wp_nonce_field` al formulario de exportación.

### v1.5
- Registro individual de historial por usuario.
- Visualización del historial personal en el Dashboard.
- Preparado para futuras exportaciones de historial personal.

### v1.4
- Se añadió gráfico de resumen al dashboard usando Chart.js.
- Se integró paso de datos de PHP a JavaScript con `wp_localize_script`.

### v1.3
- Se agregó un widget en el escritorio de WordPress con resumen de eventos, leads y frases.

### v1.2
- Sistema de notificaciones internas tras acciones clave (crear, editar, eliminar).
- Mensajes de éxito automáticos por usuario.

### v1.1
- Nuevos shortcodes: `[lista_eventos]`, `[nota_aleatoria]`, `[total_leads]`.
- Mejora visual del dashboard.
- Estilos admin centralizados en `admin-style.css`.

### v1.0
- Versión inicial completa y funcional del plugin Golden Shark Admin Panel.

---

## 📄 Licencia

Este plugin está licenciado bajo los términos de la [GPLv2 o posterior](https://www.gnu.org/licenses/gpl-2.0.html).

---

## ✍️ Autor

Desarrollado por **Carlos Gallegos**  
📫 [LinkedIn](https://www.linkedin.com/in/carlos-bryan-gallegos-batallanos-397223290)
