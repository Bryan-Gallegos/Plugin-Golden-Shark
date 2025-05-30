<?php

if (!defined('ABSPATH')) exit;

// 👥 Gestión de accesos y capacidades por rol
function golden_shark_render_roles()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('⛔ Acceso denegado.', 'golden-shark'));
    }

    // Definir las capacidades disponibles en el plugin
    $capacidades = [
        'golden_shark_acceso_basico'    => __('🔑 Acceso Básico al Plugin', 'golden-shark'),
        'golden_shark_configuracion'    => __('⚙️ Acceso a Configuración', 'golden-shark'),
        'golden_shark_ver_logs'         => __('📜 Ver Logs del Sistema', 'golden-shark'),
    ];

    // Procesar formulario
    if (isset($_POST['guardar_roles']) && check_admin_referer('guardar_roles_nonce')) {
        $roles = wp_roles()->roles;

        foreach ($roles as $rol_slug => $rol_data) {
            $rol_obj = get_role($rol_slug);
            if (!$rol_obj) continue;

            foreach ($capacidades as $cap => $label) {
                $checkbox_name = 'cap_' . $cap;
                if (isset($_POST[$checkbox_name][$rol_slug])) {
                    $rol_obj->add_cap($cap);
                } else {
                    $rol_obj->remove_cap($cap);
                }
            }
        }

        echo '<div class="notice notice-success"><p>' . __('✅ Capacidades actualizadas correctamente.', 'golden-shark') . '</p></div>';
    }

    // Mostrar formulario
    $roles = wp_roles()->roles;
    ?>
    <div class="wrap">
        <h1><?php echo __('👥 Accesos y Roles', 'golden-shark'); ?></h1>
        <p><?php echo __('Activa o desactiva las capacidades específicas que tiene cada rol en tu instalación de WordPress.', 'golden-shark'); ?></p>

        <form method="post">
            <?php wp_nonce_field('guardar_roles_nonce'); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php echo __('Rol', 'golden-shark'); ?></th>
                        <?php foreach ($capacidades as $cap => $label): ?>
                            <th><?php echo esc_html($label); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $rol_slug => $rol_data):
                        $rol_obj = get_role($rol_slug); ?>
                        <tr>
                            <td><strong><?php echo esc_html($rol_data['name']); ?></strong></td>
                            <?php foreach ($capacidades as $cap => $label): ?>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="cap_<?php echo esc_attr($cap); ?>[<?php echo esc_attr($rol_slug); ?>]"
                                           <?php checked($rol_obj->has_cap($cap)); ?>>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:15px;">
                <input type="submit" name="guardar_roles" class="button button-primary"
                       value="<?php echo esc_attr__('💾 Guardar cambios', 'golden-shark'); ?>">
            </p>
        </form>
    </div>
    <?php
}