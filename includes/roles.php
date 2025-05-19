<?php

if (!defined('ABSPATH')) exit;

// 👥 Gestión de accesos y capacidades por rol
function golden_shark_render_roles()
{
    if (!current_user_can('manage_options')) {
        wp_die('⛔ Acceso denegado.');
    }

    // Definir las capacidades disponibles en el plugin
    $capacidades = [
        'golden_shark_acceso_basico'    => '🔑 Acceso Básico al Plugin',
        'golden_shark_configuracion'    => '⚙️ Acceso a Configuración',
        'golden_shark_ver_logs'         => '📜 Ver Logs del Sistema',
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

        echo '<div class="notice notice-success"><p>✅ Capacidades actualizadas correctamente.</p></div>';
    }

    // Mostrar formulario
    $roles = wp_roles()->roles;
    ?>
    <div class="wrap">
        <h1>👥 Accesos y Roles</h1>
        <p>Activa o desactiva las capacidades específicas que tiene cada rol en tu instalación de WordPress.</p>

        <form method="post">
            <?php wp_nonce_field('guardar_roles_nonce'); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Rol</th>
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
                <input type="submit" name="guardar_roles" class="button button-primary" value="💾 Guardar cambios">
            </p>
        </form>
    </div>
    <?php
}
