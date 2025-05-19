<?php

if(!defined('ABSPATH')) exit;

function golden_shark_render_logs(){
    if (!golden_shark_user_can('golden_shark_ver_logs')) {
        wp_die('Acceso restringido');
    }

    $logs = get_option('golden_shark_logs', []);
    ?>
    <div class="wrap gs-container">
        <h2>📜 Logs del sistema</h2>

        <?php if(empty($logs)) : ?>
            <p>No hay registros disponibles</p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>IP</th>
                        <th>Navegador</th>
                        <th>Origen</th>
                        <th>Mensaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($logs) as $log): ?>
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