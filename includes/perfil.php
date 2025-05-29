<?php
if (!defined('ABSPATH')) exit;

function golden_shark_render_perfil_usuario()
{
    if (!is_user_logged_in()) {
        wp_die(__('Debes iniciar sesión para ver tu perfil.', 'golden-shark'));
    }

    $usuario = wp_get_current_user();
    $user_id = $usuario->ID;
    $tareas = get_option('golden_shark_tareas', []);
    $historial = get_user_meta($user_id, 'gs_historial_usuario', true);
    if (!is_array($historial)) $historial = [];

    $mis_tareas = array_filter($tareas, fn($t) => isset($t['responsable']) && $t['responsable'] == $user_id);
    $ultima_conexion = get_user_meta($user_id, 'last_login', true);

    ?>
    <div class="wrap gs-container">
        <h2><?php _e('👤 Mi perfil', 'golden-shark'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e('Nombre:', 'golden-shark'); ?></th>
                <td><?php echo esc_html($usuario->display_name); ?></td>
            </tr>
            <tr>
                <th><?php _e('Correo:', 'golden-shark'); ?></th>
                <td><?php echo esc_html($usuario->user_email); ?></td>
            </tr>
            <tr>
                <th><?php _e('Tareas asignadas:', 'golden-shark'); ?></th>
                <td><?php echo count($mis_tareas); ?></td>
            </tr>
            <tr>
                <th><?php _e('Última conexión:', 'golden-shark'); ?></th>
                <td><?php echo $ultima_conexion ? esc_html($ultima_conexion) : esc_html__('N/A', 'golden-shark'); ?></td>
            </tr>
        </table>

        <h3><?php _e('📝 Historial reciente:', 'golden-shark'); ?></h3>
        <?php if (empty($historial)) : ?>
            <p><?php _e('No hay historial registrado.', 'golden-shark'); ?></p>
        <?php else : ?>
            <ul style="margin-left:20px;">
                <?php foreach (array_slice(array_reverse($historial), 0, 10) as $h) : ?>
                    <li><strong><?php echo esc_html($h['fecha']); ?></strong>: <?php echo esc_html($h['mensaje']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}