<?php
if (!defined('ABSPATH')) exit;

// ✍️ FRASES
function golden_shark_render_frases()
{
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $frases = golden_shark_get_frases();

    // Guardar nueva frase
    if (isset($_POST['nueva_frase_guardada'])) {
        if (!isset($_POST['frase_nonce']) || !wp_verify_nonce($_POST['frase_nonce'], 'guardar_frase_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $nueva_frase = sanitize_text_field($_POST['nueva_frase']);
        if (!empty($nueva_frase)) {
            $frases[] = $nueva_frase;
            golden_shark_set_frases($frases);
            golden_shark_log('Se agregó una nueva frase: "' . $nueva_frase . '"');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Frase motivacional guardada.');
            echo '<div class="notice notice-success"><p>✅ Frase agregada correctamente.</p></div>';
        }
    }

    // Editar frase
    if (isset($_POST['editar_frase_guardada'])) {
        if (!isset($_POST['editar_frase_nonce']) || !wp_verify_nonce($_POST['editar_frase_nonce'], 'guardar_edicion_frase_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $id = intval($_POST['frase_id']);
        if (isset($frases[$id])) {
            $frases[$id] = sanitize_text_field($_POST['nueva_frase']);
            golden_shark_set_frases($frases);
            golden_shark_log('Se editó la frase en la posición ' . $id);
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Frase actualizada.');
            echo '<div class="notice notice-info"><p>✏️ Frase actualizada correctamente.</p></div>';
        }
    }

    // Eliminar frase
    if (isset($_GET['eliminar']) && isset($_GET['_nonce'])) {
        $i = intval($_GET['eliminar']);
        $nonce = $_GET['_nonce'];

        if (!wp_verify_nonce($nonce, 'eliminar_frase_' . $i)) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        if (isset($frases[$i])) {
            $frase_eliminada = $frases[$i];
            unset($frases[$i]);
            $frases = array_values($frases);
            golden_shark_set_frases($frases);
            golden_shark_log('Se eliminó la frase: "' . $frase_eliminada . '"');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '🗑️ Frase eliminada correctamente.');
            echo '<div class="notice notice-error"><p>🗑️ Frase eliminada.</p></div>';
        }
    }
?>

    <div class="wrap" id="top">
        <h2>💬 Frases & Mensajes</h2>

        <div class="gs-container">
            <?php if (isset($_GET['editar'])):
                $id = intval($_GET['editar']);
                if (isset($frases[$id])): ?>
                    <h3>✏️ Editar frase</h3>
                    <form method="post">
                        <input type="hidden" name="editar_frase_guardada" value="1">
                        <input type="hidden" name="frase_id" value="<?php echo $id; ?>">
                        <?php wp_nonce_field('guardar_edicion_frase_nonce', 'editar_frase_nonce'); ?>
                        <input type="text" name="nueva_frase" value="<?php echo esc_attr($frases[$id]); ?>" required>
                        <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                    </form>
                    <hr>
            <?php endif;
            endif; ?>
        </div>

        <div class="gs-container">
            <h3>➕ Nueva frase</h3>
            <form method="post">
                <input type="hidden" name="nueva_frase_guardada" value="1">
                <?php wp_nonce_field('guardar_frase_nonce', 'frase_nonce'); ?>
                <label for="nueva_frase">Frase:</label>
                <input type="text" id="nueva_frase" name="nueva_frase" placeholder="Ej. Cree en ti." required>
                <p><input type="submit" class="button button-primary" value="Guardar frase"></p>
            </form>
        </div>

        <div class="gs-container">
            <form method="get" style="margin-bottom: 15px;">
                <input type="hidden" name="page" value="golden-shark-frases">
                <label for="buscar"><strong>Buscar frase:</strong></label>
                <input type="text" name="buscar" id="buscar" value="<?php echo esc_attr($_GET['buscar'] ?? ''); ?>" placeholder="Ej. éxito, creer, motivación...">
                <input type="submit" class="button" value="Buscar">
            </form>

            <h3>📋 Frases guardadas:</h3>
            <?php
            $buscar = strtolower(trim($_GET['buscar'] ?? ''));
            $frases_filtradas = array_filter($frases, function ($f) use ($buscar) {
                return empty($buscar) || stripos($f, $buscar) !== false;
            });
            ?>

            <?php if (empty($frases_filtradas)) : ?>
                <p>No hay frases que coincidan con el filtro.</p>
            <?php else : ?>
                <ul>
                    <?php foreach ($frases_filtradas as $i => $f): ?>
                        <li>
                            <?php echo esc_html($f); ?> –
                            <a href="<?php echo admin_url('admin.php?page=golden-shark-frases&editar=' . $i); ?>">Editar</a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-frases&eliminar=' . $i), 'eliminar_frase_' . $i, '_nonce'); ?>" onclick="return confirm('¿Eliminar esta frase?');">Eliminar</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <a href="#top" class="gs-go-top" title="Volver al inicio">⬆️</a>
    </div>
<?php
}
