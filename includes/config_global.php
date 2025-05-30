<?php

if (!defined('ABSPATH')) exit;

// CONFIGURACIÓN GLOBAL PARA MULTISITIO
function golden_shark_render_config_global()
{
    if (!is_super_admin()) {
        wp_die(__('Acceso denegado. Solo el superadministrador puede ver esta acción', 'golden-shark'));
    }

    // GUARDAR CONFIGURACIONES
    if (isset($_POST['gs_guardar_config_global']) && check_admin_referer('gs_config_global_nonce')) {
        $campos = [
            'golden_shark_color_dashboard',
            'golden_shark_mensaje_motivacional',
            'golden_shark_mensaje_correo',
            'golden_shark_habilitar_notificaciones'
        ];

        foreach ($campos as $clave) {
            if ($clave === 'golden_shark_habilitar_notificaciones') {
                $valor = isset($_POST[$clave]) ? '1' : '0';
            } else {
                $valor = isset($_POST[$clave]) ? sanitize_text_field($_POST[$clave]) : '';
            }
            golden_shark_set_config($clave, $valor);
        }

        golden_shark_log(__('Se actualizaron configuraciones globales desde el panel multisite', 'golden-shark'));
        echo '<div class="updated"><p>' . __('✅ Configuraciones globales actualizadas correctamente.', 'golden-shark') . '</p></div>';
    }

    // OBTENER VALORES ACTUALES
    $color_dashboard = golden_shark_get_config('golden_shark_color_dashboard', '#0073aa');
    $mensaje_motivacional = golden_shark_get_config('golden_shark_mensaje_motivacional', __('¡Sigue adelante!', 'golden-shark'));
    $mensaje_correo = golden_shark_get_config('golden_shark_mensaje_correo', __('Gracias por tu mensaje. Te contactaremos pronto.', 'golden-shark'));
    $notificaciones = golden_shark_get_config('golden_shark_habilitar_notificaciones', '1');
?>
    <div class="wrap">
        <h1><?php _e('🌐 Configuración Global', 'golden-shark'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('gs_config_global_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="golden_shark_color_dashboard"><?php _e('Color del Dashboard', 'golden-shark'); ?></label></th>
                    <td><input type="color" id="golden_shark_color_dashboard" name="golden_shark_color_dashboard" value="<?php echo esc_attr($color_dashboard); ?>" aria-label="<?php esc_attr_e('Color del fondo del panel de administración', 'golden-shark'); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_mensaje_motivacional"><?php _e('Mensaje Motivacional', 'golden-shark'); ?></label></th>
                    <td><input type="text" id="golden_shark_mensaje_motivacional" name="golden_shark_mensaje_motivacional" class="regular-text" value="<?php echo esc_attr($mensaje_motivacional); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_mensaje_correo"><?php _e('Mensaje para Correos', 'golden-shark'); ?></label></th>
                    <td><textarea id="golden_shark_mensaje_correo" name="golden_shark_mensaje_correo" class="large-text" rows="3"><?php echo esc_textarea($mensaje_correo); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_habilitar_notificaciones"><?php _e('Mostrar Notificaciones', 'golden-shark'); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="golden_shark_habilitar_notificaciones" name="golden_shark_habilitar_notificaciones" value="1" <?php checked($notificaciones, '1'); ?>>
                            <?php _e('Sí, mostrar notificaciones internas', 'golden-shark'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <p><input type="submit" name="gs_guardar_config_global" class="button button-primary" value="<?php esc_attr_e('Guardar Configuraciones', 'golden-shark'); ?>"></p>
        </form>
    </div>
<?php
}
