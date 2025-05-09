<?php
if (!defined('ABSPATH')) exit;

// ⚙️ CONFIGURACIÓN
function golden_shark_render_config()
{
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }
?>
    <div class="wrap">
        <h2>Configuración del Plugin</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('golden_shark_config_group');
            do_settings_sections('golden_shark_config');
            submit_button();
            ?>
        </form>
    </div>
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

    // Sección única
    add_settings_section('golden_shark_config_section', 'Configuraciones Generales', null, 'golden_shark_config');

    add_settings_field('mensaje_motivacional', 'Mensaje Motivacional', 'golden_shark_mensaje_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('color_dashboard', 'Color del Dashboard', 'golden_shark_color_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('mensaje_correo', 'Mensaje en Correos', 'golden_shark_mensaje_correo_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('notificaciones', '¿Mostrar Notificaciones Internas?', 'golden_shark_notificaciones_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('alerta_eventos_dia', 'Límite de eventos por día', 'golden_shark_alerta_eventos_dia_field', 'golden_shark_config', 'golden_shark_config_section');
    add_settings_field('alerta_leads_pendientes', 'Límite de leads sin revisar', 'golden_shark_alerta_leads_field', 'golden_shark_config', 'golden_shark_config_section');
}
add_action('admin_init', 'golden_shark_register_settings');

// Campos individuales
function golden_shark_mensaje_field()
{
    $valor = get_option('golden_shark_mensaje_motivacional', '¡Tú puedes lograrlo!');
    echo '<input type="text" name="golden_shark_mensaje_motivacional" value="' . esc_attr($valor) . '" class="regular-text">';
}

function golden_shark_color_field()
{
    $valor = get_option('golden_shark_color_dashboard', '#0073aa');
    echo '<input type="color" name="golden_shark_color_dashboard" value="' . esc_attr($valor) . '">';
}

function golden_shark_mensaje_correo_field()
{
    $valor = get_option('golden_shark_mensaje_correo', 'Gracias por tu mensaje. Nos pondremos en contacto pronto.');
    echo '<textarea name="golden_shark_mensaje_correo" rows="4" class="large-text">' . esc_textarea($valor) . '</textarea>';
}

function golden_shark_notificaciones_field()
{
    $valor = get_option('golden_shark_habilitar_notificaciones', '1');
    echo '<label><input type="checkbox" name="golden_shark_habilitar_notificaciones" value="1" ' . checked(1, $valor, false) . '> Sí, mostrar notificaciones internas</label>';
}

function golden_shark_alerta_eventos_dia_field()
{
    $valor = get_option('golden_shark_alerta_eventos_dia', 5);
    echo '<input type="number" name="golden_shark_alerta_eventos_dia" value="' . esc_attr($valor) . '" class="small-text" min="1">';
}

function golden_shark_alerta_leads_field()
{
    $valor = get_option('golden_shark_alerta_leads_pendientes', 5);
    echo '<input type="number" name="golden_shark_alerta_leads_pendientes" value="' . esc_attr($valor) . '" min="1" step="1" class="small-text">';
}