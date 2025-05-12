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
| `[total_leads]`        | Muestra el total actual de leads capturados                           |
| `[tareas_pendientes]` | Muestra una lista de tareas internas marcadas como pendientes. |

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