<?php
if (!defined('ABSPATH')) exit;

// HISTORIAL
function golden_shark_render_historial() {
    if (!golden_shark_user_can('golden_shark_ver_logs')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $historial = get_option('golden_shark_historial', []);

    // Exportar historial a CSV
    if (isset($_POST['exportar_historial_csv'])) {
        if (!isset($_POST['historial_nonce']) || !wp_verify_nonce($_POST['historial_nonce'], 'exportar_historial_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="historial_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'Acción']);
        foreach ($historial as $item) {
            fputcsv($output, [$item['fecha'], $item['mensaje']]);
        }
        fclose($output);
        golden_shark_log('🗂️ Se exportó el historial de actividad.');
        exit;
    }
    ?>

    <div class="wrap gs-container" id="gs-historial">
        <h2><?php echo esc_html__('📜 Historial de Actividad', 'golden-shark'); ?></h2>

        <form method="post" style="margin-bottom: 20px;">
            <?php wp_nonce_field('exportar_historial_nonce', 'historial_nonce'); ?>
            <input type="submit" name="exportar_historial_csv" class="button button-secondary" value="<?php echo esc_attr__('📤 Exportar historial a CSV', 'golden-shark'); ?>">
        </form>

        <?php if (empty($historial)) : ?>
            <p><?php echo esc_html__('No hay actividades registradas aún.', 'golden-shark'); ?></p>
        <?php else : 
            $historial_limitado = array_slice(array_reverse($historial), 0, 100); ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Fecha', 'golden-shark'); ?></th>
                        <th><?php echo esc_html__('Acción', 'golden-shark'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial_limitado as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item['fecha']); ?></td>
                            <td><?php echo esc_html($item['mensaje']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($historial) > 100): ?>
                <p><em><?php echo esc_html__('⚠️ Solo se muestran los últimos 100 registros.', 'golden-shark'); ?></em></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php
}