<?php
if (!defined('ABSPATH')) exit;

// ⚙️ CONFIGURACIÓN
function golden_shark_render_config()
{
    if (!golden_shark_user_can('golden_shark_configuracion')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    if(isset($_POST['gs_lim pieza_masiva']) && check_admin_referer('gs_limpieza_masiva_nonce', 'gs_limpieza_masiva_nonce_field')){
        $hoy = date('Y-m-d');

        // Filtrar tareas
        $tareas = get_option('golden_shark_tareas', []);
        $tareas = array_filter($tareas, fn($t) => $t['fecha'] >= $hoy);
        update_option('golden_shark_tareas', array_values($tareas));

        // Filtrar eventos
        $eventos = get_option('golden_shark_eventos', []);
        $eventos = array_filter($eventos, fn($e) => substr($e['fecha'], 0, 10) >= $hoy);
        update_option('golden_shark_eventos', array_values($eventos));

        // Filtrar leads
        $leads = get_option('golden_shark_leads', []);
        $leads = array_filter($leads, fn($l) => substr($l['fecha'], 0, 10) >= $hoy);
        update_option('golden_shark_leads', array_values($leads));

        golden_shark_log(' 🧹 Limpieza masiva ejecutada desde Configuración.');
        golden_shark_log_usuario('Ejecutó limpieza masiva de resistros antiguos.');
        echo '<div class="notice notice-warning"><p>' . __('🧹 Se eliminaron todos los registros anteriores a hoy.', 'golden-shark') . '</p></div>';
    }
?>
    <div class="wrap gs-container">
        <h2>⚙️ Configuración del Plugin</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('golden_shark_config_group');
            do_settings_sections('golden_shark_config');
            submit_button(__('💾 Guardar configuración', 'golden-shark'));
            ?>
        </form>
    </div>
    <?php if (current_user_can('golden_shark_configuracion')): ?>
        <hr>
        <h3><?php _e('🧹 Limpieza de registros antiguos', 'golden-shark'); ?></h3>
        <form method="post">
            <?php wp_nonce_field('gs_limpieza_masiva_nonce', 'gs_limpieza_masiva_nonce_field'); ?>
            <p><?php _e('Este botón eliminará los', 'golden-shark'); ?> <strong><?php _e('leads antiguos', 'golden-shark'); ?></strong>, <strong><?php _e('eventos pasados', 'golden-shark'); ?></strong> <?php _e('y', 'golden-shark'); ?> <strong><?php _e('tareas vencidas', 'golden-shark'); ?></strong> <?php _e('hasta la fecha actual.', 'golden-shark'); ?></p>
            <button type="submit" name="gs_limpieza_masiva" class="button button-secondary" onclick="return confirm(<?php esc_js(__('¿Seguro que deseas eliminar todos los datos antiguos? Esta acción no se puede deshacer.', 'golden-shark')); ?>)">
                <?php _e('🧹 Ejecutar limpieza masiva', 'golden-shark'); ?>
            </button>
        </form>
    <?php endif; ?>
<?php
}

// Registrar ajustes
function golden_shark_register_settings()
{
    register_setting('golden_shark_config_group', 'golden_shark_mensaje_motivacional');
    register_setting('golden_shark_config_group', 'golden_shark_color_dashboard');
    register_setting('golden_shark_config_group', 'golden_shark_mensaje_correo');
    register_setting('golden_shark_config_group', 'golden_shark_habilitar_notificaciones');
    register_setting('golden_shark_config_group', 'golden_shark_alerta_eventos_dia');
    register_setting('golden_shark_config_group', 'golden_shark_alerta_leads_pendientes');
    register_setting('golden_shark_config_group', 'golden_shark_webhook_leads_url');
    register_setting('golden_shark_config_group', 'golden_shark_api_key');
    register_setting('golden_shark_config_group', 'golden_shark_webhook_eventos_url');
    register_setting('golden_shark_config_group', 'golden_shark_webhook_campos_leads');

    add_settings_section('golden_shark_config_section', '⚙️ Configuraciones Generales', null, 'golden_shark_config');

    add_settings_field('mensaje_motivacional', '💬 Mensaje Motivacional Diario', 'golden_shark_mensaje_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('color_dashboard', '🎨 Color del Dashboard', 'golden_shark_color_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('mensaje_correo', '📩 Mensaje Automático en Correos', 'golden_shark_mensaje_correo_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('notificaciones', '🔔 ¿Mostrar Notificaciones Internas?', 'golden_shark_notificaciones_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('alerta_eventos_dia', '📅 Alerta por número de eventos al día', 'golden_shark_alerta_eventos_dia_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('alerta_leads_pendientes', '📨 Alerta por leads sin revisar', 'golden_shark_alerta_leads_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('webhook_leads_url', '🔗 Webhook para nuevos leads', 'golden_shark_webhook_url_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('api_key', '🔐 Clave API', 'golden_shark_api_key_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('webhook_eventos_url', '🌐 Webhook para eventos', 'golden_shark_webhook_eventos_url_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('webhook_campos_leads', '📦 Campos del webhook (leads)', 'golden_shark_webhook_campos_leads_field', 'golden_shark_config', 'golden_shark_config_section');
}
add_action('admin_init', 'golden_shark_register_settings');

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
    foreach ($todos as $campo){
        echo '<label><input type="checkbox" name="golden_shark_webhook_campos_leads[]" value="' . esc_attr($campo) . '" ' . checked(in_array($campo, $campos), true, false) . '> ' . ucfirst($campo) . '</label><br>';
    }
    echo '<p class="description">Selecciona los campos que deseas incluir en el webhook de leads.</p>';
}

add_action('update_option', function($option, $old_value, $value){
    // Solo para opciones del plugin Golden Shark
    if (strpos($option, 'golden_shark_') === 0 && $old_value !== $value){
        $usuario = wp_get_current_user()->user_login;
        $msg = "⚙️ Configuración actualizada: $option (por $usuario)";
        golden_shark_log($msg);
        golden_shark_log_usuario($msg);
    }
}, 10, 3);