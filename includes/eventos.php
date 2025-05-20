<?php
if (!defined('ABSPATH')) exit;

// 📅 EVENTOS
function golden_shark_render_eventos()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $eventos = get_option('golden_shark_eventos', []);

    // Exportar CSV
    if (isset($_POST['exportar_csv'])) {
        $tipo_filtro = sanitize_text_field($_POST['filtro_tipo'] ?? '');

        $eventos_filtrados = array_filter($eventos, function ($evento) use ($tipo_filtro) {
            if (empty($tipo_filtro)) return true;
            return $evento['tipo'] === $tipo_filtro;
        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="eventos_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Título', 'Fecha', 'Ubicación']);
        foreach ($eventos_filtrados as $evento) {
            fputcsv($output, [$evento['titulo'], $evento['fecha'], $evento['lugar']]);
        }
        fclose($output);
        golden_shark_log('Se exportaron los eventos filtrados a CSV.');
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
            'lugar' => sanitize_text_field($_POST['evento_lugar']),
            'tipo' => sanitize_text_field($_POST['evento_tipo'])
        ];
        update_option('golden_shark_eventos', $eventos);
        golden_shark_log('Se registró un nuevo evento: ' . $_POST['evento_titulo']);
        update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Evento guardado correctamente.');
        echo '<div class="updated"><p>Evento guardado correctamente.</p></div>';
        if (function_exists('golden_shark_disparar_webhook_evento')) {
            golden_shark_disparar_webhook_evento(end($eventos));
        }
        if (function_exists('golden_shark_crear_tareas_automaticas')) {
            golden_shark_crear_tareas_automaticas(end($eventos));
        }
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
                'lugar' => sanitize_text_field($_POST['evento_lugar']),
                'tipo' => sanitize_text_field($_POST['evento_tipo'])
            ];
            update_option('golden_shark_eventos', $eventos);
            golden_shark_log('Se editó el evento: ' . $_POST['evento_titulo']);
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Evento actualizado correctamente.');
            echo '<div class="updated"><p>Evento actualizado correctamente.</p></div>';
            if (function_exists('golden_shark_disparar_webhook_evento')) {
                golden_shark_disparar_webhook_evento($eventos[$id]);
            }
            if (function_exists('golden_shark_crear_tareas_automaticas')) {
                golden_shark_crear_tareas_automaticas($eventos[$id]);
            }
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
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '🗑️ Evento eliminado correctamente.');
            echo '<div class="updated"><p>Evento eliminado.</p></div>';
        }
    }


?>
    <div class="wrap" id="top">
        <h2>Gestión de Eventos Internos</h2>

        <?php if (isset($_GET['editar_evento'])):
            $id = intval($_GET['editar_evento']);
            if (isset($eventos[$id])): $evento = $eventos[$id]; ?>
                <div class="gs-container">
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
                            <tr>
                                <th>Tipo:</th>
                                <td>
                                    <select name="evento_tipo">
                                        <option value="interno" <?php selected($evento['tipo'], 'interno'); ?>>Interno</option>
                                        <option value="reunion"> <?php selected($evento['tipo'], 'reunion'); ?>Reunión</option>
                                        <option value="lanzamiento" <?php selected($evento['tipo'], 'lanzamiento'); ?>>Lanzamiento</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                    </form>
                    <hr>
                </div>
        <?php endif;
        endif; ?>


        <div class="gs-container">
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
                    <tr>
                        <th>Tipo:</th>
                        <td>
                            <select name="evento_tipo">
                                <option value="interno">Interno</option>
                                <option value="reunion">Reunion</option>
                                <option value="lanzamiento">Lanzamiento</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p><input type="submit" class="button button-primary" value="Guardar evento"></p>
            </form>
        </div>

        <div class="gs-container">
            <form method="post" style="margin-top:20px;">
                <input type="hidden" name="exportar_csv" value="1">
                <select name="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="interno" <?php selected($_GET['tipo'] ?? '', 'interno'); ?>>Interno</option>
                    <option value="reunion" <?php selected($_GET['tipo'] ?? '', 'reunion'); ?>>Reunión</option>
                    <option value="lanzamiento" <?php selected($_GET['tipo'] ?? '', 'lanzamiento'); ?>>Lanzamiento</option>
                </select>
                <input type="submit" class="button button-secondary" value="📤 Exportar eventos filtrados">
            </form>
        </div>

        <hr>
        <div class="gs-container">
            <form method="get" style="margin-bottom: 15px;">
                <input type="hidden" name="page" value="golden-shark-eventos">
                <label for="filtro_tipo"><strong>Filtrar por tipo:</strong></label>
                <select name="tipo" id="filtro_tipo" onchange="this.form.submit();">
                    <option value="">Todos</option>
                    <option value="interno" <?php selected($_GET['tipo'] ?? '', 'interno'); ?>>Interno</option>
                    <option value="reunion" <?php selected($_GET['tipo'] ?? '', 'reunion'); ?>>Reunión</option>
                    <option value="lanzamiento" <?php selected($_GET['tipo'] ?? '', 'lanzamiento'); ?>>Lanzamiento</option>
                </select>
            </form>

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
                        <?php
                        $tipo_filtrado = $_GET['tipo'] ?? '';
                        foreach ($eventos as $i => $evento) :
                            if ($tipo_filtrado && $evento['tipo'] !== $tipo_filtrado) continue;
                        ?>
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

        <a href="#top" class="gs-go-top" title="Volver al inicio">⬆️</a>
    </div>
<?php
}
