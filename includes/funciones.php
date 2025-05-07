<?php
if (!defined('ABSPATH')) exit;

// 🔐 Verificar permisos del usuario
function golden_shark_user_can($capability = 'manage_options') {
    return current_user_can($capability);
}

// 📝 Registrar una acción en el historial
function golden_shark_log($mensaje) {
    $historial = get_option('golden_shark_historial', []);
    $historial[] = [
        'mensaje' => sanitize_text_field($mensaje),
        'fecha'   => current_time('Y-m-d H:i:s')
    ];
    update_option('golden_shark_historial', $historial);
}

// 🔄 Registrar cambios de configuración (puedes añadir más opciones en el futuro)
function golden_shark_log_cambio_configuracion($option, $old_value, $value) {
    if ($old_value === $value) return;

    $mensajes = [
        'golden_shark_mensaje_motivacional' => 'Se actualizó el mensaje motivacional',
        'golden_shark_color_dashboard'      => 'Se actualizó el color del dashboard',
        'golden_shark_mensaje_correo'       => 'Se actualizó el mensaje para correos',
        'golden_shark_habilitar_notificaciones' => 'Se actualizó la opción de notificaciones internas'
    ];

    if (isset($mensajes[$option])) {
        golden_shark_log($mensajes[$option] . ': "' . sanitize_text_field($value) . '"');
    }
}
add_action('updated_option', 'golden_shark_log_cambio_configuracion', 10, 3);

// 📦 Cargar estilos y scripts solo en páginas del plugin
function golden_shark_admin_assets($hook) {
    if (strpos($hook, 'golden-shark') === false) return;

    wp_enqueue_style(
        'golden-shark-admin-style',
        plugin_dir_url(__FILE__) . '../assets/css/admin-style.css',
        [],
        '1.0'
    );

    wp_enqueue_script(
        'golden-shark-admin-script',
        plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js',
        [],
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'golden_shark_admin_assets');
