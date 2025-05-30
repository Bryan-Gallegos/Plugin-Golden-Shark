<?php
if (!defined('ABSPATH')) exit;

// ⚙️ CONFIGURACIÓN
function golden_shark_render_config() {
    if (!golden_shark_user_can('golden_shark_configuracion')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $tab = $_GET['tab'] ?? 'generales';

    echo '<div class="wrap gs-container">';
    echo '<h2>' . __('⚙️ Configuración del Plugin', 'golden-shark') . '</h2>';

    echo '<nav class="nav-tab-wrapper">';
    $tabs = [
        'generales' => '⚙️ Generales',
        'webhooks' => '🌐 Webhooks',
        'limpieza' => '🧹 Limpieza',
    ];
    foreach ($tabs as $slug => $label) {
        $active = ($tab === $slug) ? 'nav-tab-active' : '';
        echo '<a href="?page=golden-shark-config&tab=' . $slug . '" class="nav-tab ' . $active . '">' . $label . '</a>';
    }
    echo '</nav><br>';

    switch ($tab) {
        case 'webhooks':
            echo '<form method="post" action="options.php">';
            settings_fields('golden_shark_config_group');
            do_settings_sections('golden_shark_config_webhooks');
            submit_button(__('💾 Guardar configuración', 'golden-shark'));
            echo '</form>';
            break;

        case 'limpieza':
            if (isset($_POST['gs_limpieza_masiva']) && check_admin_referer('gs_limpieza_masiva_nonce', 'gs_limpieza_masiva_nonce_field')) {
                $hoy = date('Y-m-d');
                update_option('golden_shark_tareas', array_values(array_filter(get_option('golden_shark_tareas', []), fn($t) => $t['fecha'] >= $hoy)));
                update_option('golden_shark_eventos', array_values(array_filter(get_option('golden_shark_eventos', []), fn($e) => substr($e['fecha'], 0, 10) >= $hoy)));
                update_option('golden_shark_leads', array_values(array_filter(get_option('golden_shark_leads', []), fn($l) => substr($l['fecha'], 0, 10) >= $hoy)));
                golden_shark_log('🧹 Limpieza masiva ejecutada desde Configuración.');
                golden_shark_log_usuario('Ejecutó limpieza masiva de registros antiguos.');
                echo '<div class="notice notice-warning"><p>' . __('🧹 Se eliminaron todos los registros anteriores a hoy.', 'golden-shark') . '</p></div>';
            }

            echo '<h3>' . __('🧹 Limpieza de registros antiguos', 'golden-shark') . '</h3>';
            echo '<form method="post" name="form_limpieza">';
            wp_nonce_field('gs_limpieza_masiva_nonce', 'gs_limpieza_masiva_nonce_field');
            echo '<p>' . __('Este botón eliminará los', 'golden-shark') . ' <strong>' . __('leads antiguos', 'golden-shark') . '</strong>, <strong>' . __('eventos pasados', 'golden-shark') . '</strong> ' . __('y', 'golden-shark') . ' <strong>' . __('tareas vencidas', 'golden-shark') . '</strong> ' . __('hasta la fecha actual.', 'golden-shark') . '</p>';
            echo '<button type="submit" name="gs_limpieza_masiva" class="button button-secondary" onclick="return confirm(\'' . esc_js(__('¿Seguro que deseas eliminar todos los datos antiguos? Esta acción no se puede deshacer.', 'golden-shark')) . '\')">';
            _e('🧹 Ejecutar limpieza masiva', 'golden-shark');
            echo '</button></form>';
            break;

        case 'generales':
        default:
            echo '<form method="post" action="options.php">';
            settings_fields('golden_shark_config_group');
            do_settings_sections('golden_shark_config_generales');
            submit_button(__('💾 Guardar configuración', 'golden-shark'));
            echo '</form>';
            break;
    }

    echo '</div>';
}

// REGISTRO DE CAMPOS
add_action('admin_init', function () {
    // Registro de opciones
    $opciones = [
        'golden_shark_mensaje_motivacional',
        'golden_shark_color_dashboard',
        'golden_shark_mensaje_correo',
        'golden_shark_habilitar_notificaciones',
        'golden_shark_alerta_eventos_dia',
        'golden_shark_alerta_leads_pendientes',
        'golden_shark_webhook_leads_url',
        'golden_shark_api_key',
        'golden_shark_webhook_eventos_url',
        'golden_shark_webhook_campos_leads',
        'golden_shark_leads_custom_fields',
    ];
    foreach ($opciones as $opt) register_setting('golden_shark_config_group', $opt);

    // Secciones
    add_settings_section('golden_shark_config_section_general', '⚙️ Configuraciones Generales', null, 'golden_shark_config_generales');
    add_settings_section('golden_shark_config_section_webhooks', '🌐 Webhooks y API', null, 'golden_shark_config_webhooks');

    // Campos generales
    add_settings_field('mensaje_motivacional', '💬 Mensaje Motivacional Diario', 'golden_shark_mensaje_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('color_dashboard', '🎨 Color del Dashboard', 'golden_shark_color_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('mensaje_correo', '📩 Mensaje Automático en Correos', 'golden_shark_mensaje_correo_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('notificaciones', '🔔 ¿Mostrar Notificaciones Internas?', 'golden_shark_notificaciones_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('alerta_eventos_dia', '📅 Alerta por número de eventos al día', 'golden_shark_alerta_eventos_dia_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('alerta_leads_pendientes', '📨 Alerta por leads sin revisar', 'golden_shark_alerta_leads_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');
    add_settings_field('leads_custom_fields', '🧩 Campos Personalizados de Leads', 'golden_shark_leads_custom_fields_field', 'golden_shark_config_generales', 'golden_shark_config_section_general');    

    // Webhooks
    add_settings_field('webhook_leads_url', '🔗 Webhook para nuevos leads', 'golden_shark_webhook_url_field', 'golden_shark_config_webhooks', 'golden_shark_config_section_webhooks');
    add_settings_field('api_key', '🔐 Clave API', 'golden_shark_api_key_field', 'golden_shark_config_webhooks', 'golden_shark_config_section_webhooks');
    add_settings_field('webhook_eventos_url', '🌐 Webhook para eventos', 'golden_shark_webhook_eventos_url_field', 'golden_shark_config_webhooks', 'golden_shark_config_section_webhooks');
    add_settings_field('webhook_campos_leads', '📦 Campos del webhook (leads)', 'golden_shark_webhook_campos_leads_field', 'golden_shark_config_webhooks', 'golden_shark_config_section_webhooks');
});

// Campos individuales con estilos aplicados automáticamente
function golden_shark_mensaje_field()
{
    $valor = get_option('golden_shark_mensaje_motivacional', '¡Tú puedes lograrlo!');
    echo '<input type="text" name="golden_shark_mensaje_motivacional" value="' . esc_attr($valor) . '" required>';
}

function golden_shark_color_field()
{
    $valor = get_option('golden_shark_color_dashboard', '#0073aa');
    echo '<input type="color" name="golden_shark_color_dashboard" value="' . esc_attr($valor) . '">';
}

function golden_shark_mensaje_correo_field()
{
    $valor = get_option('golden_shark_mensaje_correo', 'Gracias por tu mensaje. Nos pondremos en contacto pronto.');
    echo '<textarea name="golden_shark_mensaje_correo" rows="4">' . esc_textarea($valor) . '</textarea>';
}

function golden_shark_notificaciones_field()
{
    $valor = get_option('golden_shark_habilitar_notificaciones', '1');
    echo '<label><input type="checkbox" name="golden_shark_habilitar_notificaciones" value="1" ' . checked(1, $valor, false) . '> Sí, mostrar notificaciones internas</label>';
}

function golden_shark_alerta_eventos_dia_field()
{
    $valor = get_option('golden_shark_alerta_eventos_dia', 5);
    echo '<input type="number" name="golden_shark_alerta_eventos_dia" value="' . esc_attr($valor) . '" min="1">';
}

function golden_shark_alerta_leads_field()
{
    $valor = get_option('golden_shark_alerta_leads_pendientes', 5);
    echo '<input type="number" name="golden_shark_alerta_leads_pendientes" value="' . esc_attr($valor) . '" min="1">';
}

function golden_shark_webhook_url_field()
{
    $valor = get_option('golden_shark_webhook_leads_url', '');
    echo '<input type="url" name="golden_shark_webhook_leads_url" value="' . esc_attr($valor) . '" class="eregular-text" placeholder="https://goldenshark.es/webhook">'; //CAMBIAR CON EL NOMBRE DE SU SERVIDOR
    echo '<p class="description">URL a la que se enviarán automáticamente los datos del lead al registrarlo.</p>';
}

function golden_shark_api_key_field()
{
    $key = get_option('golden_shark_api_key', wp_generate_password(16, false));
    echo '<input type="text" name="golden_shark_api_key" value="' . esc_attr($key) . '" class="regular-text" readonly>';
    echo '<p class="description">Utiliza esta clave para autenticar solicitudes a la API interna.</p>';
}

function golden_shark_webhook_eventos_url_field()
{
    $valor = get_option('golden_shark_webhook_eventos_url', '');
    echo '<input type="url" name="golden_shark_webhook_eventos_url" value="' . esc_attr($valor) . '" class="regular-text" placeholder="https://midominio.com/webhook-eventos">';
    echo '<p class="description">Se enviará un POST con los datos del evento cuando se registre o edite.</p>';
}

function golden_shark_webhook_campos_leads_field()
{
    $campos = get_option('golden_shark_webhook_campos_leads', ['nombre', 'correo', 'mensaje']);
    $todos = ['nombre', 'correo', 'mensaje', 'fecha', 'etiquetas'];
    foreach ($todos as $campo) {
        echo '<label><input type="checkbox" name="golden_shark_webhook_campos_leads[]" value="' . esc_attr($campo) . '" ' . checked(in_array($campo, $campos), true, false) . '> ' . ucfirst($campo) . '</label><br>';
    }
    echo '<p class="description">Selecciona los campos que deseas incluir en el webhook de leads.</p>';
}

add_action('update_option', function ($option, $old_value, $value) {
    // Solo para opciones del plugin Golden Shark
    if (strpos($option, 'golden_shark_') === 0 && $old_value !== $value) {
        $usuario = wp_get_current_user()->user_login;
        $msg = "⚙️ Configuración actualizada: $option (por $usuario)";
        golden_shark_log($msg);
        golden_shark_log_usuario($msg);
    }
}, 10, 3);

function golden_shark_leads_custom_fields_field() {
    $valor = get_option('golden_shark_leads_custom_fields', '');
    echo '<textarea name="golden_shark_leads_custom_fields" rows="4" cols="60" placeholder="campo1:text&#10;campo2:select&#10;campo3:checkbox">' . esc_textarea($valor) . '</textarea>';
    echo '<p class="description">' . __('Define los campos personalizados para leads. Usa el formato: nombre:tipo. Tipos válidos: text, select, checkbox.', 'golden-shark') . '</p>';
}