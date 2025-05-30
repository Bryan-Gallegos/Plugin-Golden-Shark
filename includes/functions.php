<?php
if (!defined('ABSPATH')) exit;

// 🔐 Verificar permisos del usuario
function golden_shark_user_can($capability = 'golden_shark_acceso_basico')
{
    if (is_multisite() && is_super_admin()) return true;
    if (current_user_can($capability)) return true;
    return current_user_can('administrator');
}

// 📝 Registrar una acción en el historial general
function golden_shark_log($mensaje, $tipo = 'info')
{
    $logs = get_option('golden_shark_logs', []);
    $usuario = wp_get_current_user();
    $logs[] = [
        'fecha'    => current_time('Y-m-d H:i:s'),
        'usuario'  => $usuario && !empty($usuario->user_login) ? $usuario->user_login : 'sistema',
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'navegador'=> $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        'origen'   => $_SERVER['HTTP_REFERER'] ?? 'N/A',
        'mensaje'  => $mensaje,
        'tipo'     => $tipo
    ];
    update_option('golden_shark_logs', array_slice($logs, -200));
}

function golden_shark_log_shortcode($shortcode_name)
{
    $url = $_SERVER['REQUEST_URI'] ?? 'URL desconocida';
    $usuario = is_user_logged_in() ? wp_get_current_user()->user_login : 'anónimo';
    $mensaje = sprintf(__('🔍 Shortcode [%s] ejecutado por %s en %s', 'golden-shark'), $shortcode_name, $usuario, $url);
    golden_shark_log($mensaje);
}

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

function golden_shark_log_cambio_configuracion($option, $old_value, $value)
{
    if ($old_value === $value) return;
    $mensajes = [
        'golden_shark_mensaje_motivacional'      => __('Se actualizó el mensaje motivacional', 'golden-shark'),
        'golden_shark_color_dashboard'           => __('Se actualizó el color del dashboard', 'golden-shark'),
        'golden_shark_mensaje_correo'            => __('Se actualizó el mensaje para correos', 'golden-shark'),
        'golden_shark_habilitar_notificaciones'  => __('Se actualizó la opción de notificaciones internas', 'golden-shark')
    ];
    if (isset($mensajes[$option])) {
        $texto = $mensajes[$option] . ': "' . sanitize_text_field($value) . '"';
        golden_shark_log($texto);
        golden_shark_log_usuario($texto);
    }
}
add_action('updated_option', 'golden_shark_log_cambio_configuracion', 10, 3);

function golden_shark_admin_assets($hook)
{
    if (strpos($hook, 'golden-shark') === false) return;

    wp_enqueue_style('golden-shark-modern-style', plugin_dir_url(__FILE__) . '../assets/css/modern-style.css', [], '2.2');
    wp_enqueue_style('golden-shark-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', [], '1.0');
    wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css');

    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js', [], null, true);
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_script('golden-shark-admin-script', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', [], '1.0', true);

    $frases_count  = count(golden_shark_get_frases());
    $eventos_count = get_transient('gs_eventos_count');
    $leads_count   = get_transient('gs_leads_count');

    if ($eventos_count === false) {
        $eventos_count = count(get_option('golden_shark_eventos', []));
        set_transient('gs_eventos_count', $eventos_count, HOUR_IN_SECONDS);
    }

    if ($leads_count === false) {
        $leads_count = count(get_option('golden_shark_leads', []));
        set_transient('gs_leads_count', $leads_count, HOUR_IN_SECONDS);
    }

    wp_localize_script('golden-shark-admin-script', 'gsData', [
        'frases'  => $frases_count,
        'eventos' => $eventos_count,
        'leads'   => $leads_count
    ]);
}
add_action('admin_enqueue_scripts', 'golden_shark_admin_assets');

function golden_shark_dashboard_widget()
{
    static $eventos, $leads, $frases;
    $eventos = $eventos ?? get_option('golden_shark_eventos', []);
    $leads   = $leads ?? get_option('golden_shark_leads', []);
    $frases  = $frases ?? golden_shark_get_frases();

    $total_eventos = count($eventos);
    $total_leads   = count($leads);
    $total_frases  = count($frases);

    $hoy = date('Y-m-d');
    $eventos_hoy = array_filter($eventos, fn($e) => isset($e['fecha']) && $e['fecha'] === $hoy);
    $leads_sin_revisar = array_filter($leads, fn($l) => empty($l['revisado']) || $l['revisado'] === 'no');

    $limite_eventos = intval(golden_shark_get_config('golden_shark_alerta_eventos_dia', 5));
    $limite_leads   = intval(golden_shark_get_config('golden_shark_alerta_leads_pendientes', 5));

    echo '<ul style="margin-left: 20px;">';
    echo '<li aria-label="Total de eventos">📅 <strong>' . $total_eventos . '</strong> ' . __('eventos registrados', 'golden-shark') . '</li>';
    echo '<li aria-label="Total de leads">📨 <strong>' . $total_leads . '</strong> ' . __('leads capturados', 'golden-shark') . '</li>';
    echo '<li aria-label="Total de frases">💬 <strong>' . $total_frases . '</strong> ' . __('frases guardadas', 'golden-shark') . '</li>';

    if (count($eventos_hoy) > $limite_eventos) {
        echo '<li aria-label="Eventos de hoy" style="color: #a00">⚠️ <strong>' . count($eventos_hoy) . '</strong> ' . __('eventos programados para hoy', 'golden-shark') . '</li>';
    }
    if (count($leads_sin_revisar) > $limite_leads) {
        echo '<li aria-label="Leads sin revisar" style="color: #b36b00">🔔 <strong>' . count($leads_sin_revisar) . '</strong> ' . __('leads sin revisar', 'golden-shark') . '</li>';
    }
    echo '</ul>';
}

function golden_shark_register_widget()
{
    if (current_user_can('edit_posts')) {
        wp_add_dashboard_widget('golden_shark_resumen_widget', 'Resumen - Golden Shark 🦈', 'golden_shark_dashboard_widget');
    }
}
add_action('wp_dashboard_setup', 'golden_shark_register_widget');

// Funciones multisitio
function golden_shark_get_frases_globales() { return get_site_option('golden_shark_frases', []); }
function golden_shark_set_frases_globales($frases) { return update_site_option('golden_shark_frases', $frases); }
function golden_shark_get_config_global($clave, $default = '') { return get_site_option($clave, $default); }
function golden_shark_set_config_global($clave, $valor) { return update_site_option($clave, $valor); }

function golden_shark_guardar_historial_sitio($sitio_id, $description)
{
    if (!is_multisite()) return;
    $usuario = wp_get_current_user();
    $historial = get_site_option("gs_historial_site_$sitio_id", []);
    $historial[] = [
        'fecha' => current_time('Y-m-d H:i:s'),
        'usuario' => $usuario && !empty($usuario->user_login) ? $usuario->user_login : 'sistema',
        'cambios' => $description
    ];
    update_site_option("gs_historial_site_$sitio_id", array_slice($historial, -50));
}

add_action('golden_shark_enviar_recordatorios_diarios', 'golden_shark_enviar_recordatorios_tareas');
add_action('wp_login', function($user_login, $user) {
    update_user_meta($user->ID, 'last_login', current_time('Y-m-d H:i:s'));
}, 10, 2);

function golden_shark_toggle_evento_favorito($evento_id) {
    $user_id = get_current_user_id();
    $favoritos = get_user_meta($user_id, 'gs_eventos_favoritos', true) ?: [];
    if (in_array($evento_id, $favoritos)) {
        $favoritos = array_diff($favoritos, [$evento_id]);
    } else {
        $favoritos[] = $evento_id;
    }
    update_user_meta($user_id, 'gs_eventos_favoritos', array_values($favoritos));
}

function golden_shark_es_evento_favorito($evento_id) {
    $user_id = get_current_user_id();
    $favoritos = get_user_meta($user_id, 'gs_eventos_favoritos', true) ?: [];
    return in_array($evento_id, $favoritos);
}

function golden_shark_marcar_notificaciones_como_leidas() {
    delete_user_meta(get_current_user_id(), 'gs_notificaciones_usuario');
    update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Todas las notificaciones fueron marcadas como leídas.');
}

function golden_shark_guardar_historial_objeto($tipo, $id, $accion, $usuario = null) {
    if (!$usuario) {
        $usuario = wp_get_current_user();
    }
    $historial = get_option("gs_historial_{$tipo}", []);
    $registro = [
        'fecha'   => current_time('Y-m-d H:i:s'),
        'usuario' => $usuario->user_login,
        'id'      => $id,
        'accion'  => $accion
    ];
    $historial[] = $registro;
    update_option("gs_historial_{$tipo}", $historial);
}
