<?php
if (!defined('ABSPATH')) exit;

// 🗂 MÓDULO DE TAREAS INTERNAS
function golden_shark_render_tareas() {
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $tareas = get_option('golden_shark_tareas', []);

    // Eliminar tarea
    if (isset($_GET['eliminar_tarea']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_tarea']);
        if (wp_verify_nonce($_GET['_nonce'], 'eliminar_tarea_' . $id) && isset($tareas[$id])) {
            unset($tareas[$id]);
            $tareas = array_values($tareas);
            update_option('golden_shark_tareas', $tareas);
            golden_shark_log('Se eliminó una tarea interna.');
            golden_shark_log_usuario('Eliminó una tarea interna.');
            echo '<div class="updated"><p>Tarea eliminada.</p></div>';
        }
    }

    // Guardar nueva tarea
    if (isset($_POST['nueva_tarea'])) {
        check_admin_referer('guardar_tarea_nonce', 'tarea_nonce');

        $tareas[] = [
            'titulo' => sanitize_text_field($_POST['tarea_titulo']),
            'estado' => sanitize_text_field($_POST['tarea_estado']),
            'fecha'  => sanitize_text_field($_POST['tarea_fecha']),
            'responsable' => sanitize_text_field($_POST['tarea_responsable']),
        ];
        update_option('golden_shark_tareas', $tareas);
        golden_shark_log('Se registró una nueva tarea interna.');
        golden_shark_log_usuario('Registró una nueva tarea.');
        echo '<div class="updated"><p>Tarea guardada correctamente.</p></div>';
    }

    // Edición rápida
    if (isset($_POST['editar_tarea'])) {
        check_admin_referer('editar_tarea_nonce', 'editar_tarea_nonce_field');

        $id = intval($_POST['tarea_id']);
        if (isset($tareas[$id])) {
            $tareas[$id] = [
                'titulo' => sanitize_text_field($_POST['tarea_titulo']),
                'estado' => sanitize_text_field($_POST['tarea_estado']),
                'fecha'  => sanitize_text_field($_POST['tarea_fecha']),
                'responsable' => sanitize_text_field($_POST['tarea_responsable']),
            ];
            update_option('golden_shark_tareas', $tareas);
            golden_shark_log('Se actualizó una tarea interna.');
            golden_shark_log_usuario('Editó una tarea.');
            echo '<div class="updated"><p>Tarea actualizada.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h2>Tareas internas 🗂️</h2>

        <h3>Nueva tarea</h3>
        <form method="post">
            <?php wp_nonce_field('guardar_tarea_nonce', 'tarea_nonce'); ?>
            <table class="form-table">
                <tr><th>Título:</th><td><input type="text" name="tarea_titulo" class="regular-text" required></td></tr>
                <tr><th>Estado:</th>
                    <td>
                        <select name="tarea_estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="completado">Completado</option>
                        </select>
                    </td>
                </tr>
                <tr><th>Fecha:</th><td><input type="date" name="tarea_fecha" required></td></tr>
                <tr><th>Responsable:</th><td><input type="text" name="tarea_responsable" class="regular-text"></td></tr>
            </table>
            <p><input type="submit" name="nueva_tarea" value="Guardar tarea" class="button button-primary"></p>
        </form>

        <hr>
        <h3>Tareas registradas</h3>
        <?php if (empty($tareas)): ?>
            <p>No hay tareas registradas.</p>
        <?php else: ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $i => $t): ?>
                        <tr>
                            <form method="post">
                                <td><input type="text" name="tarea_titulo" value="<?php echo esc_attr($t['titulo']); ?>"></td>
                                <td>
                                    <select name="tarea_estado">
                                        <option value="pendiente" <?php selected($t['estado'], 'pendiente'); ?>>Pendiente</option>
                                        <option value="completado" <?php selected($t['estado'], 'completado'); ?>>Completado</option>
                                    </select>
                                </td>
                                <td><input type="date" name="tarea_fecha" value="<?php echo esc_attr($t['fecha']); ?>"></td>
                                <td><input type="text" name="tarea_responsable" value="<?php echo esc_attr($t['responsable']); ?>"></td>
                                <td>
                                    <?php wp_nonce_field('editar_tarea_nonce', 'editar_tarea_nonce_field'); ?>
                                    <input type="hidden" name="tarea_id" value="<?php echo $i; ?>">
                                    <input type="submit" name="editar_tarea" value="Guardar" class="button">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-tareas&eliminar_tarea=' . $i), 'eliminar_tarea_' . $i, '_nonce'); ?>" class="button-link-delete" onclick="return confirm('¿Eliminar esta tarea?')">Eliminar</a>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php
}

// 🔽 Shortcode: tareas pendientes
function golden_shark_shortcode_tareas_pendientes() {
    $tareas = get_option('golden_shark_tareas', []);
    $pendientes = array_filter($tareas, fn($t) => $t['estado'] === 'pendiente');

    if (empty($pendientes)) {
        return '<p>No hay tareas pendientes por ahora.</p>';
    }

    ob_start();
    echo '<ul class="gs-tareas-pendientes">';
    foreach ($pendientes as $t) {
        echo '<li><strong>' . esc_html($t['titulo']) . '</strong> — ' . esc_html($t['fecha']) . '</li>';
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('tareas_pendientes', 'golden_shark_shortcode_tareas_pendientes');
