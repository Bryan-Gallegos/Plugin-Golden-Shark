<?php
if (!defined('ABSPATH')) exit;

// NOTAS
function golden_shark_render_notas()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $notas = get_option('golden_shark_notas', []);
    $mensaje = '';

    // Guardar nueva nota
    if (isset($_POST['nueva_nota'])) {
        if (!isset($_POST['nota_nonce']) || !wp_verify_nonce($_POST['nota_nonce'], 'guardar_nota_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $notas[] = [
            'contenido' => wp_kses_post($_POST['nota_contenido']),
            'fecha' => current_time('Y-m-d H:i:s')
        ];
        update_option('golden_shark_notas', $notas);
        $nota_id = array_key_last($notas);
        golden_shark_guardar_historial_objeto('notas', $nota_id, __('Creado', 'golden-shark'));
        golden_shark_log('Se agregó una nueva nota interna.');
        update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('✅ Nota guardada correctamente.', 'golden-shark'));
        $mensaje = '<div class="updated" role="alert" aria-live="assertive"><p>' . __('✅ Nota guardada correctamente.', 'golden-shark') . '</p></div>';
    }

    // Editar nota
    if (isset($_POST['editar_nota_guardada'])) {
        if (!isset($_POST['editar_nota_nonce']) || !wp_verify_nonce($_POST['editar_nota_nonce'], 'guardar_edicion_nota_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }

        $id = intval($_POST['nota_id']);
        if (isset($notas[$id])) {
            $notas[$id]['contenido'] = wp_kses_post($_POST['nota_contenido']);
            update_option('golden_shark_notas', $notas);
            golden_shark_guardar_historial_objeto('notas', $id, __('Editado', 'golden-shark'));
            golden_shark_log('Se editó una nota interna.');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('Nota actualizada correctamente.', 'golden-shark'));
            $mensaje = '<div class="updated" role="alert" aria-live="assertive"><p>' . __('Nota actualizada correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Eliminar nota
    if (isset($_GET['eliminar_nota']) && isset($_GET['_nonce'])) {
        $i = intval($_GET['eliminar_nota']);
        if (!wp_verify_nonce($_GET['_nonce'], 'eliminar_nota_' . $i)) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }
        if (isset($notas[$i])) {
            array_splice($notas, $i, 1);
            update_option('golden_shark_notas', $notas);
            golden_shark_guardar_historial_objeto('notas', $i, __('Eliminado', 'golden-shark'));
            golden_shark_log('Se eliminó una nota interna.');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('Nota eliminada correctamente.', 'golden-shark'));
            $mensaje = '<div class="updated" role="alert" aria-live="assertive"><p>' . __('Nota eliminada correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Exportar notas a CSV
    if (isset($_POST['exportar_notas_csv'])) {
        if (!isset($_POST['exportar_notas_nonce_field']) || !wp_verify_nonce($_POST['exportar_notas_nonce_field'], 'exportar_notas_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
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
        <h2><?php _e('Notas Internas 🗒️', 'golden-shark'); ?></h2>
        <?php echo $mensaje; ?>

        <?php if (isset($_GET['editar_nota'])):
            $id = intval($_GET['editar_nota']);
            if (isset($notas[$id])): $nota = $notas[$id]; ?>
                <h3><?php _e('Editar Nota', 'golden-shark'); ?></h3>
                <form method="post">
                    <input type="hidden" name="editar_nota_guardada" value="1">
                    <input type="hidden" name="nota_id" value="<?php echo $id; ?>">
                    <?php wp_nonce_field('guardar_edicion_nota_nonce', 'editar_nota_nonce'); ?>
                    <?php
                    wp_editor($nota['contenido'], 'editar_nota_contenido', [
                        'textarea_name' => 'nota_contenido',
                        'textarea_rows' => 6,
                        'media_buttons' => false,
                        'tinymce'       => true,
                        'quicktags'     => true
                    ]);
                    ?>
                    <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar cambios', 'golden-shark'); ?>"></p>
                </form>
                <hr>
        <?php endif;
        endif; ?>

        <form method="post">
            <label for="nueva_nota_contenido"><?php _e('Nueva nota:', 'golden-shark'); ?></label>
            <input type="hidden" name="nueva_nota" value="1">
            <?php wp_nonce_field('guardar_nota_nonce', 'nota_nonce'); ?>
            <?php
            wp_editor('', 'nueva_nota_contenido', [
                'textarea_name' => 'nota_contenido',
                'textarea_rows' => 6,
                'media_buttons' => false,
                'tinymce'       => true,
                'quicktags'     => true
            ]);
            ?>
            <input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar nota', 'golden-shark'); ?>">
        </form>

        <form method="get" style="margin-top: 15px;">
            <input type="hidden" name="page" value="golden-shark-notas">
            <label for="buscar_nota"><?php _e('Buscar nota:', 'golden-shark'); ?></label>
            <input type="text" id="buscar_nota" name="buscar_nota" value="<?php echo esc_attr($busqueda); ?>" placeholder="<?php esc_attr_e('Buscar por palabra clave...', 'golden-shark'); ?>" style="width:300px;">
            <input type="submit" class="button button-primary" value="<?php esc_attr_e('Buscar', 'golden-shark'); ?>">
        </form>

        <form method="post" style="margin-top: 10px;">
            <?php wp_nonce_field('exportar_notas_nonce', 'exportar_notas_nonce_field'); ?>
            <input type="submit" name="exportar_notas_csv" value="<?php esc_attr_e('📤 Exportar todas las notas a CSV', 'golden-shark'); ?>" class="button button-secondary">
        </form>

        <hr>
        <h3><?php _e('Historial de Notas:', 'golden-shark'); ?></h3>
        <?php if (empty($notas_filtradas)) : ?>
            <p><?php _e('No se encontraron notas con ese criterio.', 'golden-shark'); ?></p>
        <?php else : ?>
            <ul style="list-style: disc; padding-left: 20px;">
                <?php foreach ($notas_filtradas as $nota): ?>
                    <li style="margin-bottom: 10px;">
                        <strong><?php echo esc_html($nota['fecha']); ?>:</strong><br>
                        <?php echo wp_kses_post($nota['contenido']); ?><br>
                        <a href="<?php echo admin_url('admin.php?page=golden-shark-notas&editar_nota=' . $nota['id']); ?>" aria-label="<?php esc_attr_e('Editar esta nota', 'golden-shark'); ?>"><?php _e('Editar', 'golden-shark'); ?></a> |
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-notas&eliminar_nota=' . $nota['id']), 'eliminar_nota_' . $nota['id'], '_nonce'); ?>" onclick="return confirm('<?php echo esc_js(__('¿Eliminar esta nota?', 'golden-shark')); ?>');" aria-label="<?php esc_attr_e('Eliminar esta nota', 'golden-shark'); ?>"><?php _e('Eliminar', 'golden-shark'); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php
}
?>