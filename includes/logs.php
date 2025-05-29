<?php

if(!defined('ABSPATH')) exit;

function golden_shark_render_logs()
{
    if (!golden_shark_user_can('golden_shark_ver_logs')) {
        wp_die(__('Acceso restringido', 'golden-shark'));
    }

    $logs = get_option('golden_shark_logs', []);
    $usuario_filtro = sanitize_text_field($_GET['filtro_usuario'] ?? '');
    $ip_filtro = sanitize_text_field($_GET['filtro_ip'] ?? '');
    $fecha_filtro = sanitize_text_field($_GET['filtro_fecha'] ?? '');
    $palabra_clave = sanitize_text_field($_GET['filtro_palabra'] ?? '');

    // Aplicar filtros
    $logs_filtrados = array_filter($logs, function ($log) use ($usuario_filtro, $ip_filtro, $fecha_filtro, $palabra_clave) {
        if ($usuario_filtro && stripos($log['usuario'], $usuario_filtro) === false) return false;
        if ($ip_filtro && stripos($log['ip'], $ip_filtro) === false) return false;
        if ($fecha_filtro && strpos($log['fecha'], $fecha_filtro) === false) return false;
        if ($palabra_clave && stripos($log['mensaje'], $palabra_clave) === false) return false;
        return true;
    });
    ?>
    <div class="wrap gs-container">
        <h2><?php __('📜 Logs del sistema', 'golden-shark') ?></h2>

        <form method="get" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="golden-shark-logs">
            <input type="text" name="filtro_usuario" placeholder="Filtrar por usuario" value="<?php echo esc_attr($usuario_filtro); ?>" style="margin-right:10px;">
            <input type="text" name="filtro_ip" placeholder="Filtrar por IP" value="<?php echo esc_attr($ip_filtro); ?>" style="margin-right:10px;">
            <input type="date" name="filtro_fecha" value="<?php echo esc_attr($fecha_filtro); ?>" style="margin-right:10px;">
            <input type="text" name="filtro_palabra" placeholder="Palabra clave" value="<?php echo esc_attr($palabra_clave); ?>" style="margin-right:10px;">
            <input type="submit" class="button" value="🔍 Filtrar">
            <a href="<?php echo admin_url('admin.php?page=golden-shark-logs'); ?>" class="button"><?php __('❌ Limpiar', 'golden-shark') ?></a>
        </form>

        <?php if (empty($logs_filtrados)) : ?>
            <p><?php __('No se encontraron registros con los filtros aplicados.', 'golden-shark') ?></p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php __('Fecha', 'golden-shark') ?></th>
                        <th><?php __('Usuario', 'golden-shark') ?></th>
                        <th><?php __('IP', 'golden-shark') ?></th>
                        <th><?php __('Navegador', 'golden-shark') ?></th>
                        <th><?php __('Origen', 'golden-shark') ?></th>
                        <th><?php __('Mensaje', 'golden-shark') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($logs_filtrados) as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['fecha']); ?></td>
                            <td><?php echo esc_html($log['usuario']); ?></td>
                            <td><?php echo esc_html($log['ip']); ?></td>
                            <td><small><?php echo esc_html(wp_trim_words($log['navegador'], 12)); ?></small></td>
                            <td><small><?php echo esc_html(wp_trim_words($log['origen'], 10)); ?></small></td>
                            <td><?php echo esc_html($log['mensaje']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
    <?php
}