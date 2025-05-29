<?php

if (!defined('ABSPATH')) exit;

// RESUMEN POR USUARIO
function golden_shark_render_resumen()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $current_user = wp_get_current_user();
    $username = strtolower(trim($current_user->user_login));

    $tareas = get_option('golden_shark_tareas', []);
    $eventos = get_option('golden_shark_eventos', []);
    $logs = get_option('golden_shark_logs', []);

    // Filtrar tareas asignadas al usuario y pendientes
    $mis_tareas = array_filter($tareas, function ($t) use ($username) {
        return strtolower(trim($t['responsable'] ?? '')) === $username && $t['estado'] === 'pendiente';
    });

    // Filtrar eventos futuros (a partir de hoy)
    $hoy = date('Y-m-d');
    $eventos_relevantes = array_filter($eventos, function ($e) use ($hoy) {
        return isset($e['fecha']) && $e['fecha'] >= $hoy;
    });

    // Obtener últimos logs del usuario actual
    $mis_logs = array_filter($logs, function ($log) use ($current_user) {
        return isset($log['user_id']) && $log['user_id'] == $current_user->ID;
    });
    $mis_logs = array_slice(array_reverse($mis_logs), 0, 10); // últimos 10

?>
    <div class="wrap gs-container" id="top">
    <h2><?php __('📊 Resumen de Actividad', 'golden-shark'); ?></h2>

        <h3><?php __('🗂️ Tareas asignadas a ti', 'golden-shark') ?></h3>
        <?php if (empty($mis_tareas)) : ?>
            <p><?php __('No tienes tareas pendientes.', 'golden-shark'); ?></p>
        <?php else : ?>
            <ul>
                <?php foreach ($mis_tareas as $t) : ?>
                    <li><strong><?php echo esc_html($t['titulo']); ?></strong> — <?php echo esc_html($t['fecha']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h3><?php __('📅 Eventos relevantes', 'golden-shark') ?></h3>
        <?php if (empty($eventos_relevantes)) : ?>
            <p><?php __('No hay eventos próximos.', 'golden-shark') ?></p>
        <?php else : ?>
            <ul>
                <?php foreach ($eventos_relevantes as $e) : ?>
                    <li><strong><?php echo esc_html($e['titulo']); ?></strong> — <?php echo esc_html($e['fecha']); ?> en <?php echo esc_html($e['lugar']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h3><?php __('📋 Tus últimas acciones', 'golden-shark') ?></h3>
        <?php if (empty($mis_logs)) : ?>
            <p><?php __('No se encontraron logs recientes.', 'golden-shark') ?></p>
        <?php else : ?>
            <ul>
                <?php foreach ($mis_logs as $log) : ?>
                    <li><em><?php echo esc_html($log['fecha']); ?></em> — <?php echo esc_html($log['accion']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <a href="#top" class="gs-go-top" title=<?php __('Volver al inicio', 'golden-shark') ?>>⬆️</a>
<?php
}
