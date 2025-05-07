<?php
if (!defined('ABSPATH')) exit;

// HISTORIAL
function golden_shark_render_historial() {
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $historial = get_option('golden_shark_historial', []);
    $mensaje = '';

    // Exportar historial a CSV
    if (isset($_POST['exportar_historial_csv'])) {
        if (!isset($_POST['historial_nonce']) || !wp_verify_nonce($_POST['historial_nonce'], 'exportar_historial_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="historial_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'Acción']);
        foreach ($historial as $item) {
            fputcsv($output, [$item['fecha'], $item['mensaje']]);
        }
        fclose($output);
        golden_shark_log('Se exportó el historial de actividad.');
        exit;
    }
    ?>

    <div class="wrap">
        <h2>Historial de Actividad 📜</h2>

        <form method="post" style="margin-bottom: 20px;">
            <?php wp_nonce_field('exportar_historial_nonce', 'historial_nonce'); ?>
            <input type="submit" name="exportar_historial_csv" class="button button-secondary" value="📤 Exportar historial a CSV">
        </form>

        <?php if (empty($historial)) : ?>
            <p>No hay actividades registradas aún.</p>
        <?php else : ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($historial) as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item['fecha']); ?></td>
                            <td><?php echo esc_html($item['mensaje']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php
}
