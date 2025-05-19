<?php
if (!defined('ABSPATH')) exit;

// NOTAS
function golden_shark_render_notas()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $notas = get_option('golden_shark_notas', []);
    $mensaje = '';

    // Guardar nueva nota
    if (isset($_POST['nueva_nota'])) {
        if (!isset($_POST['nota_nonce']) || !wp_verify_nonce($_POST['nota_nonce'], 'guardar_nota_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $notas[] = [
            'contenido' => sanitize_textarea_field($_POST['nota_contenido']),
            'fecha' => current_time('Y-m-d H:i:s')
        ];
        update_option('golden_shark_notas', $notas);
        golden_shark_log('Se agregó una nueva nota interna.');
        update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Nota guardada correctamente.');
        $mensaje = '<div class="updated"><p>Nota guardada correctamente.</p></div>';
    }

    // Editar nota
    if (isset($_POST['editar_nota_guardada'])) {
        if (!isset($_POST['editar_nota_nonce']) || !wp_verify_nonce($_POST['editar_nota_nonce'], 'guardar_edicion_nota_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $id = intval($_POST['nota_id']);
        if (isset($notas[$id])) {
            $notas[$id]['contenido'] = sanitize_textarea_field($_POST['nota_contenido']);
            update_option('golden_shark_notas', $notas);
            golden_shark_log('Se editó una nota interna.');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Nota actualizada correctamente.');
            $mensaje = '<div class="updated"><p>Nota actualizada correctamente.</p></div>';
        }
    }

    // Eliminar nota
    if (isset($_GET['eliminar_nota']) && isset($_GET['_nonce'])) {
        $i = intval($_GET['eliminar_nota']);
        if (!wp_verify_nonce($_GET['_nonce'], 'eliminar_nota_' . $i)) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }
        if (isset($notas[$i])) {
            unset($notas[$i]);
            $notas = array_values($notas);
            update_option('golden_shark_notas', $notas);
            golden_shark_log('Se eliminó una nota interna.');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '🗑️ Nota eliminada correctamente.');
            $mensaje = '<div class="updated"><p>Nota eliminada.</p></div>';
        }
    }

    // Exportar notas a CSV
    if (isset($_POST['exportar_notas_csv'])) {
        if (!isset($_POST['exportar_notas_nonce_field']) || !wp_verify_nonce($_POST['exportar_notas_nonce_field'], 'exportar_notas_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="notas_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'Contenido']);
        foreach ($notas as $nota) {
            fputcsv($output, [$nota['fecha'], $nota['contenido']]);
        }
        fclose($output);
        golden_shark_log('Se exportaron las notas a CSV.');
        exit;
    }

    // Buscar notas
    $busqueda = isset($_GET['buscar_nota']) ? sanitize_text_field($_GET['buscar_nota']) : '';
    $notas_filtradas = [];

    if ($busqueda !== '') {
        foreach ($notas as $i => $nota) {
            if (stripos($nota['contenido'], $busqueda) !== false) {
                $nota['id'] = $i;
                $notas_filtradas[] = $nota;
            }
        }
    } else {
        foreach ($notas as $i => $nota) {
            $nota['id'] = $i;
            $notas_filtradas[] = $nota;
        }
    }
?>
    <div class="wrap">
        <h2>Notas Internas 🗒️</h2>
        <?php echo $mensaje; ?>

        <?php if (isset($_GET['editar_nota'])):
            $id = intval($_GET['editar_nota']);
            if (isset($notas[$id])): $nota = $notas[$id]; ?>
                <h3>Editar Nota</h3>
                <form method="post">
                    <input type="hidden" name="editar_nota_guardada" value="1">
                    <input type="hidden" name="nota_id" value="<?php echo $id; ?>">
                    <?php wp_nonce_field('guardar_edicion_nota_nonce', 'editar_nota_nonce'); ?>
                    <textarea name="nota_contenido" rows="5" style="width:100%;" required><?php echo esc_textarea($nota['contenido']); ?></textarea>
                    <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                </form>
                <hr>
        <?php endif;
        endif; ?>

        <form method="post">
            <input type="hidden" name="nueva_nota" value="1">
            <?php wp_nonce_field('guardar_nota_nonce', 'nota_nonce'); ?>
            <textarea name="nota_contenido" rows="5" style="width:100%;" placeholder="Escribe aquí una nota interna..." required></textarea>
            <p><input type="submit" class="button button-primary" value="Guardar nota"></p>
        </form>

        <form method="get" style="margin-top: 15px;">
            <input type="hidden" name="page" value="golden-shark-notas">
            <input type="text" name="buscar_nota" value="<?php echo esc_attr($busqueda); ?>" placeholder="Buscar por palabra clave..." style="width:300px;">
            <input type="submit" class="button" value="Buscar">
        </form>

        <form method="post" style="margin-top: 10px;">
            <?php wp_nonce_field('exportar_notas_nonce', 'exportar_notas_nonce_field'); ?>
            <input type="submit" name="exportar_notas_csv" value="📤 Exportar todas las notas a CSV" class="button button-secondary">
        </form>

        <hr>
        <h3>Historial de Notas:</h3>
        <?php if (empty($notas_filtradas)) : ?>
            <p>No se encontraron notas con ese criterio.</p>
        <?php else : ?>
            <ul style="list-style: disc; padding-left: 20px;">
                <?php foreach ($notas_filtradas as $nota): ?>
                    <li style="margin-bottom: 10px;">
                        <strong><?php echo esc_html($nota['fecha']); ?>:</strong><br>
                        <?php echo nl2br(esc_html($nota['contenido'])); ?><br>
                        <a href="<?php echo admin_url('admin.php?page=golden-shark-notas&editar_nota=' . $nota['id']); ?>">Editar</a> |
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-notas&eliminar_nota=' . $nota['id']), 'eliminar_nota_' . $nota['id'], '_nonce'); ?>" onclick="return confirm('¿Eliminar esta nota?');">Eliminar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php 
} 
?>