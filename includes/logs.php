<?php

if (!defined('ABSPATH')) exit;

function golden_shark_render_logs()
{
    if (!golden_shark_user_can('golden_shark_ver_logs')) {
        wp_die(__('⛔ Acceso restringido', 'golden-shark'));
    }

    $logs = get_option('golden_shark_logs', []);
    $usuario_filtro = sanitize_text_field($_GET['filtro_usuario'] ?? '');
    $ip_filtro = sanitize_text_field($_GET['filtro_ip'] ?? '');
    $fecha_filtro = sanitize_text_field($_GET['filtro_fecha'] ?? '');
    $palabra_clave = sanitize_text_field($_GET['filtro_palabra'] ?? '');

    $logs_filtrados = array_filter($logs, function ($log) use ($usuario_filtro, $ip_filtro, $fecha_filtro, $palabra_clave) {
        if ($usuario_filtro && stripos($log['usuario'], $usuario_filtro) === false) return false;
        if ($ip_filtro && stripos($log['ip'], $ip_filtro) === false) return false;
        if ($fecha_filtro && strpos($log['fecha'], $fecha_filtro) === false) return false;
        if ($palabra_clave && stripos($log['mensaje'], $palabra_clave) === false) return false;
        return true;
    });
    ?>

    <div class="wrap gs-container">
        <h2><?php echo esc_html__('📜 Logs del sistema', 'golden-shark'); ?></h2>

        <form method="get" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="golden-shark-logs">

            <label for="filtro_usuario"><?php echo esc_html__('Usuario:', 'golden-shark'); ?></label>
            <input type="text" id="filtro_usuario" name="filtro_usuario" placeholder="<?php esc_attr_e('Filtrar por usuario', 'golden-shark'); ?>" value="<?php echo esc_attr($usuario_filtro); ?>" style="margin-right:10px;">

            <label for="filtro_ip"><?php echo esc_html__('IP:', 'golden-shark'); ?></label>
            <input type="text" id="filtro_ip" name="filtro_ip" placeholder="<?php esc_attr_e('Filtrar por IP', 'golden-shark'); ?>" value="<?php echo esc_attr($ip_filtro); ?>" style="margin-right:10px;">

            <label for="filtro_fecha"><?php echo esc_html__('Fecha:', 'golden-shark'); ?></label>
            <input type="date" id="filtro_fecha" name="filtro_fecha" value="<?php echo esc_attr($fecha_filtro); ?>" style="margin-right:10px;">

            <label for="filtro_palabra"><?php echo esc_html__('Palabra clave:', 'golden-shark'); ?></label>
            <input type="text" id="filtro_palabra" name="filtro_palabra" placeholder="<?php esc_attr_e('Mensaje, acción, evento...', 'golden-shark'); ?>" value="<?php echo esc_attr($palabra_clave); ?>" style="margin-right:10px;">

            <input type="submit" class="button" value="<?php esc_attr_e('🔍 Filtrar', 'golden-shark'); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=golden-shark-logs')); ?>" class="button"><?php echo esc_html__('❌ Limpiar filtros', 'golden-shark'); ?></a>
        </form>

        <?php if (empty($logs_filtrados)) : ?>
            <p><?php echo esc_html__('No se encontraron registros con los filtros aplicados.', 'golden-shark'); ?></p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('📅 Fecha', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('👤 Usuario', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('🌐 IP', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('🧭 Navegador', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('🔗 Origen', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('📝 Mensaje', 'golden-shark'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($logs_filtrados) as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['fecha']); ?></td>
                            <td><?php echo esc_html($log['usuario']); ?></td>
                            <td><?php echo esc_html($log['ip']); ?></td>
                            <td><small><?php echo esc_html(wp_strip_all_tags(wp_trim_words($log['navegador'], 12))); ?></small></td>
                            <td><small><?php echo esc_html(wp_strip_all_tags(wp_trim_words($log['origen'], 10))); ?></small></td>
                            <td><?php echo esc_html($log['mensaje']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>

<?php
}