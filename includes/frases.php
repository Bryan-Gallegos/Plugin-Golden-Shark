<?php
if (!defined('ABSPATH')) exit;

// ✍️ FRASES
function golden_shark_render_frases()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $frases = golden_shark_get_frases();

    // Guardar nueva frase
    if (isset($_POST['nueva_frase_guardada'])) {
        if (!isset($_POST['frase_nonce']) || !wp_verify_nonce($_POST['frase_nonce'], 'guardar_frase_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
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
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
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
        if (!wp_verify_nonce($_GET['_nonce'], 'eliminar_frase_' . $i)) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
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

    $buscar = strtolower(trim($_GET['buscar'] ?? ''));
    $frases_filtradas = array_filter($frases, fn($f) => empty($buscar) || stripos($f, $buscar) !== false);
?>
    <div class="wrap" id="top">
        <h2><?php __('💬 Frases & Mensajes', 'golden-shark') ?></h2>

        <section class="gs-container" aria-labelledby="editar-frase">
            <?php if (isset($_GET['editar'])) :
                $id = intval($_GET['editar']);
                if (isset($frases[$id])) : ?>
                    <h3 id="editar-frase"><?php __('✏️ Editar frase', 'golden-shark') ?></h3>
                    <form method="post" aria-label="Formulario para editar frase">
                        <input type="hidden" name="editar_frase_guardada" value="1">
                        <input type="hidden" name="frase_id" value="<?php echo $id; ?>">
                        <?php wp_nonce_field('guardar_edicion_frase_nonce', 'editar_frase_nonce'); ?>
                        <label for="editar_frase_input"><?php __('Frase', 'golden-shark') ?>:</label>
                        <input type="text" id="editar_frase_input" name="nueva_frase" value="<?php echo esc_attr($frases[$id]); ?>" required>
                        <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                    </form>
                    <hr>
            <?php endif;
            endif; ?>
        </section>

        <section class="gs-container" aria-labelledby="nueva-frase">
            <h3 id="nueva-frase"><?php __('➕ Nueva frase', 'golden-shark') ?></h3>
            <form method="post" aria-label="Formulario para agregar nueva frase">
                <input type="hidden" name="nueva_frase_guardada" value="1">
                <?php wp_nonce_field('guardar_frase_nonce', 'frase_nonce'); ?>
                <label for="nueva_frase"><?php __('Frase', 'golden-shark') ?>:</label>
                <input type="text" id="nueva_frase" name="nueva_frase" placeholder="Ej. Cree en ti." required>
                <p><input type="submit" class="button button-primary" value="Guardar frase"></p>
            </form>
        </section>

        <section class="gs-container" aria-labelledby="buscar-frase">
            <h3 id="buscar-frase">🔍 <?php __('Buscar frases', 'golden-shark') ?></h3>
            <form method="get" style="margin-bottom: 15px;" role="search" aria-label="Formulario de búsqueda de frases">
                <input type="hidden" name="page" value="golden-shark-frases">
                <label for="buscar"><strong><?php __('Buscar frase', 'golden-shark') ?>:</strong></label>
                <input type="text" name="buscar" id="buscar" value="<?php echo esc_attr($buscar); ?>" placeholder="Ej. éxito, creer, motivación...">
                <input type="submit" class="button" value="Buscar">
            </form>

            <h3><?php __('📋 Frases guardadas', 'golden-shark') ?>:</h3>
            <?php if (empty($frases_filtradas)) : ?>
                <p><?php __('No hay frases que coincidan con el filtro', 'golden-shark') ?>.</p>
            <?php else : ?>
                <ul>
                    <?php foreach ($frases_filtradas as $i => $f) : ?>
                        <li>
                            <?php echo esc_html($f); ?> –
                            <a href="<?php echo admin_url('admin.php?page=golden-shark-frases&editar=' . $i); ?>"><?php __('Editar', 'golden-shark') ?></a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-frases&eliminar=' . $i), 'eliminar_frase_' . $i, '_nonce'); ?>" onclick="return confirm('¿Eliminar esta frase?');"><?php __('Eliminar', 'golden-shark') ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <nav role="navigation" aria-label="Volver al inicio">
            <a href="#top" class="gs-go-top" title="Volver al inicio">⬆️</a>
        </nav>
    </div>
<?php
}