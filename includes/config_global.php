<?php

if(!defined('ABSPATH')) exit;

// CONFIGURACIÓN GLOBAL PARA MULTISITIO;
function golden_shark_render_config_global(){
    if(!is_super_admin()){
        wp_die('Acceso denegado. Solo el superadministrador puede ver esta acción');
    }

    // GUARDAR CONFIGURACIONES
    if(isset($_POST['gs_guardar_config_global']) && check_admin_referer('gs_config_global_nonce')){
        $campos = [
            'golden_shark_color_dashboard',
            'golden_shark_mensaje_motivacional',
            'golden_shark_mensaje_correo',
            'golden_shark_habilitar_notificaciones'
        ];

        foreach ($campos as $clave){
            $valor = isset($_POST[$clave]) ? sanitize_text_field($_POST[$clave]) : '';
            golden_shark_set_config($clave, $valor);
        }

        golden_shark_log('Seactualizaron configuraciones globales desde el panel multisite');
        echo '<div class="updated"><p>✅ Configuraciones globales actualizadas correctamente.</p></div>';
    }

    // OBTENER VALORES ACTUALES
    $color_dashboard = golden_shark_get_config('golden_shark_color_dashboard', '#0073aa');
    $mensaje_motivacional = golden_shark_get_config('golden_shark_mensaje_motivacional', '¡Sigue adelante!');
    $mensaje_correo = golden_shark_get_config('golden_shark_mensaje_correo', 'Gracias por tu mensaje. Te contactaremos pronto.');
    $notificaciones = golden_shark_get_config('golden_shark_habilitar_notificaciones', '1');
    ?>
    <div class="wrap">
        <h1>🌐 Configuración Global</h1>
        <form method="post">
            <?php wp_nonce_field('gs_config_global_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="golden_shark_color_dashboard">Color del Dasboard</label></th>
                    <td><input type="color" name="golden_shark_color_dashboard" value="<?php echo esc_attr($color_dashboard); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_mensaje_motivacional">Mensaje Motivacional</label></th>
                    <td><input type="text" name="golden_shark_mensaje_motivacional" class="regular-text" value="<?php echo esc_attr($mensaje_motivacional); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_mensaje_correo">Mensaje para Correos</label></th>
                    <td><textarea name="golden_shark_mensaje_correo" class="large-text" rows="3"><?php echo esc_textarea($mensaje_correo); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="golden_shark_habilitar_notificaciones">Mostrar Notificaciones</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="golden_shark_habilitar_notificaciones" value="1" <?php checked($notificaciones, '1'); ?>>
                            Sí, mostrar notificaciones internas
                        </label>
                    </td>
                </tr>
            </table>
            <p><input type="submit" name="gs_guardar_config_global" class="button button-primary" value="Guardar Configuraciones"></p>
        </form>
    </div>
<?php
}