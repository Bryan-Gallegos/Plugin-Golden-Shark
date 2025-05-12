<?php
if (!defined('ABSPATH')) exit;

// 🔐 Verificar permisos del usuario
function golden_shark_user_can($capability = 'manage_options')
{
    return current_user_can($capability);
}

// 📝 Registrar una acción en el historial general
function golden_shark_log($mensaje)
{
    $historial = get_option('golden_shark_historial', []);
    $historial[] = [
        'mensaje' => sanitize_text_field($mensaje),
        'fecha'   => current_time('Y-m-d H:i:s')
    ];
    update_option('golden_shark_historial', $historial);
}

// 🧍 Registrar una acción en el historial personal del usuario actual
function golden_shark_log_usuario($mensaje)
{
    $user_id = get_current_user_id();
    if (!$user_id) return;

    $historial = get_user_meta($user_id, 'gs_historial_usuario', true);
    if (!is_array($historial)) $historial = [];

    $historial[] = [
        'mensaje' => sanitize_text_field($mensaje),
        'fecha'   => current_time('Y-m-d H:i:s')
    ];

    if (count($historial) > 50) {
        $historial = array_slice($historial, -50);
    }

    update_user_meta($user_id, 'gs_historial_usuario', $historial);
}

// 🔄 Registrar cambios de configuración
function golden_shark_log_cambio_configuracion($option, $old_value, $value)
{
    if ($old_value === $value) return;

    $mensajes = [
        'golden_shark_mensaje_motivacional' => 'Se actualizó el mensaje motivacional',
        'golden_shark_color_dashboard'      => 'Se actualizó el color del dashboard',
        'golden_shark_mensaje_correo'       => 'Se actualizó el mensaje para correos',
        'golden_shark_habilitar_notificaciones' => 'Se actualizó la opción de notificaciones internas'
    ];

    if (isset($mensajes[$option])) {
        $texto = $mensajes[$option] . ': "' . sanitize_text_field($value) . '"';
        golden_shark_log($texto);
        golden_shark_log_usuario($texto);
    }
}
add_action('updated_option', 'golden_shark_log_cambio_configuracion', 10, 3);

// 📦 Cargar estilos y scripts solo en páginas del plugin
function golden_shark_admin_assets($hook)
{
    if (strpos($hook, 'golden-shark') === false) return;

    wp_enqueue_style(
        'golden-shark-admin-style',
        plugin_dir_url(__FILE__) . '../assets/css/admin-style.css',
        [],
        '1.0'
    );

    // FullCalendar (solo si está en una página del plugin)
    wp_enqueue_style(
        'fullcalendar-css',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css'
    );

    wp_enqueue_script(
        'fullcalendar-js',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'golden-shark-admin-script',
        plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js',
        [],
        '1.0',
        true
    );

    // Pasar datos al script
    wp_localize_script('golden-shark-admin-script', 'gsData', [
        'frases'  => count(golden_shark_get_frases()),
        'eventos' => count(get_option('golden_shark_eventos', [])),
        'leads'   => count(get_option('golden_shark_leads', [])),
    ]);
}
add_action('admin_enqueue_scripts', 'golden_shark_admin_assets');

// 🧩 Widget de resumen en el Escritorio de WordPress
function golden_shark_dashboard_widget()
{
    $total_eventos = count(get_option('golden_shark_eventos', []));
    $total_leads   = count(get_option('golden_shark_leads', []));
    $total_frases  = count(golden_shark_get_frases());

    echo '<ul style="margin-left: 20px;">';
    echo '<li>📅 <strong>' . $total_eventos . '</strong> eventos registrados</li>';
    echo '<li>📨 <strong>' . $total_leads . '</strong> leads capturados</li>';
    echo '<li>💬 <strong>' . $total_frases . '</strong> frases guardadas</li>';
    echo '</ul>';
}

function golden_shark_register_widget()
{
    if (current_user_can('edit_posts')) {
        wp_add_dashboard_widget(
            'golden_shark_resumen_widget',
            'Resumen - Golden Shark 🦈',
            'golden_shark_dashboard_widget'
        );
    }
}
add_action('wp_dashboard_setup', 'golden_shark_register_widget');

// 🌐 Funciones auxiliares para panel multisitio (fase 2)

// Obtener frases globales
function golden_shark_get_frases_globales() {
    return get_site_option('golden_shark_frases', []);
}

// Guardar frases globales
function golden_shark_set_frases_globales($frases) {
    return update_site_option('golden_shark_frases', $frases);
}

// Obtener configuración global (opcional, alias)
function golden_shark_get_config_global($clave, $default = '') {
    return get_site_option($clave, $default);
}

// Guardar configuración global (opcional, alias)
function golden_shark_set_config_global($clave, $valor) {
    return update_site_option($clave, $valor);
}
