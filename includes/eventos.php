<?php
if (!defined('ABSPATH')) exit;

// 📅 EVENTOS
function golden_shark_render_eventos()
{
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $eventos = get_option('golden_shark_eventos', []);

    // Exportar CSV
    if (isset($_POST['exportar_csv'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="eventos_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Título', 'Fecha', 'Ubicación']);
        foreach ($eventos as $evento) {
            fputcsv($output, [$evento['titulo'], $evento['fecha'], $evento['lugar']]);
        }
        fclose($output);
        golden_shark_log('Se exportaron los eventos a CSV.');
        exit;
    }

    // Guardar nuevo evento
    if (isset($_POST['nuevo_evento'])) {
        if (!isset($_POST['evento_nonce']) || !wp_verify_nonce($_POST['evento_nonce'], 'guardar_evento_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $eventos[] = [
            'titulo' => sanitize_text_field($_POST['evento_titulo']),
            'fecha' => sanitize_text_field($_POST['evento_fecha']),
            'lugar' => sanitize_text_field($_POST['evento_lugar'])
        ];
        update_option('golden_shark_eventos', $eventos);
        golden_shark_log('Se registró un nuevo evento: ' . $_POST['evento_titulo']);
        echo '<div class="updated"><p>Evento guardado correctamente.</p></div>';
    }


    // Editar evento
    if (isset($_POST['editar_evento_guardado'])) {
        if (!isset($_POST['editar_evento_nonce']) || !wp_verify_nonce($_POST['editar_evento_nonce'], 'guardar_edicion_evento_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $id = intval($_POST['evento_id']);
        if (isset($eventos[$id])) {
            $eventos[$id] = [
                'titulo' => sanitize_text_field($_POST['evento_titulo']),
                'fecha' => sanitize_text_field($_POST['evento_fecha']),
                'lugar' => sanitize_text_field($_POST['evento_lugar'])
            ];
            update_option('golden_shark_eventos', $eventos);
            golden_shark_log('Se editó el evento: ' . $_POST['evento_titulo']);
            echo '<div class="updated"><p>Evento actualizado correctamente.</p></div>';
        }
    }


    // Eliminar evento
    if (isset($_GET['eliminar_evento']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_evento']);
        $nonce = $_GET['_nonce'];

        if (!wp_verify_nonce($nonce, 'eliminar_evento_' . $id)) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        if (isset($eventos[$id])) {
            unset($eventos[$id]);
            $eventos = array_values($eventos);
            update_option('golden_shark_eventos', $eventos);
            golden_shark_log('Se eliminó un evento con ID: ' . $id);
            echo '<div class="updated"><p>Evento eliminado.</p></div>';
        }
    }


?>
    <div class="wrap">
        <h2>Gestión de Eventos Internos</h2>

        <?php if (isset($_GET['editar_evento'])):
            $id = intval($_GET['editar_evento']);
            if (isset($eventos[$id])): $evento = $eventos[$id]; ?>
                <h3>Editar Evento</h3>
                <form method="post">
                    <input type="hidden" name="editar_evento_guardado" value="1">
                    <input type="hidden" name="evento_id" value="<?php echo $id; ?>">
                    <?php wp_nonce_field('guardar_edicion_evento_nonce', 'editar_evento_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th>Título:</th>
                            <td><input type="text" name="evento_titulo" value="<?php echo esc_attr($evento['titulo']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td><input type="date" name="evento_fecha" value="<?php echo esc_attr($evento['fecha']); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Ubicación:</th>
                            <td><input type="text" name="evento_lugar" value="<?php echo esc_attr($evento['lugar']); ?>" class="regular-text" required></td>
                        </tr>
                    </table>
                    <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                </form>
                <hr>
        <?php endif;
        endif; ?>

        <h3>Nuevo Evento</h3>
        <form method="post">
            <input type="hidden" name="nuevo_evento" value="1">
            <?php wp_nonce_field('guardar_evento_nonce', 'evento_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Título:</th>
                    <td><input type="text" name="evento_titulo" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Fecha:</th>
                    <td><input type="date" name="evento_fecha" required></td>
                </tr>
                <tr>
                    <th>Ubicación:</th>
                    <td><input type="text" name="evento_lugar" class="regular-text" required></td>
                </tr>
            </table>
            <p><input type="submit" class="button button-primary" value="Guardar evento"></p>
        </form>

        <form method="post" style="margin-top:20px;">
            <input type="hidden" name="exportar_csv" value="1">
            <input type="submit" class="button button-secondary" value="Exportar eventos a CSV">
        </form>

        <hr>
        <h3>Eventos Registrados:</h3>
        <?php if (empty($eventos)) : ?>
            <p>No hay eventos registrados.</p>
        <?php else : ?>
            <table class="widefat fixed">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th>Ubicación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $i => $evento) : ?>
                        <tr>
                            <td><?php echo esc_html($evento['titulo']); ?></td>
                            <td><?php echo esc_html($evento['fecha']); ?></td>
                            <td><?php echo esc_html($evento['lugar']); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=golden-shark-eventos&editar_evento=' . $i); ?>">Editar</a> |
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-eventos&eliminar_evento=' . $i), 'eliminar_evento_' . $i, '_nonce'); ?>" onclick="return confirm('¿Eliminar este evento?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php
}
