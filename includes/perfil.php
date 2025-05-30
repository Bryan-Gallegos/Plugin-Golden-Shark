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
    <div class="wrap gs-container" aria-label="<?php esc_attr_e('Perfil del usuario actual', 'golden-shark'); ?>">
        <h2><?php esc_html_e('👤 Mi perfil', 'golden-shark'); ?></h2>

        <table class="form-table" aria-describedby="info-perfil">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Nombre:', 'golden-shark'); ?></th>
                    <td><?php echo esc_html($usuario->display_name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Correo:', 'golden-shark'); ?></th>
                    <td><?php echo esc_html($usuario->user_email); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Tareas asignadas:', 'golden-shark'); ?></th>
                    <td><?php echo number_format_i18n(count($mis_tareas)); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Última conexión:', 'golden-shark'); ?></th>
                    <td><?php echo $ultima_conexion ? esc_html($ultima_conexion) : esc_html__('N/A', 'golden-shark'); ?></td>
                </tr>
            </tbody>
        </table>

        <h3><?php esc_html_e('📝 Historial reciente:', 'golden-shark'); ?></h3>
        <?php if (empty($historial)) : ?>
            <p><?php esc_html_e('No hay historial registrado.', 'golden-shark'); ?></p>
        <?php else : ?>
            <ul class="gs-historial-lista" style="margin-left:20px;" aria-label="<?php esc_attr_e('Lista de historial del usuario', 'golden-shark'); ?>">
                <?php foreach (array_slice(array_reverse($historial), 0, 10) as $h) : ?>
                    <li>
                        <time datetime="<?php echo esc_attr($h['fecha']); ?>"><?php echo esc_html($h['fecha']); ?></time>:
                        <?php echo esc_html($h['mensaje']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}