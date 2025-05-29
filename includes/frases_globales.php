<?php
if (!defined('ABSPATH')) exit;

// 🌐 Pantalla de administración de frases globales
function golden_shark_render_frases_globales()
{
    if (!is_multisite() || !is_main_site() || !is_super_admin()) {
        wp_die(__('⛔ No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $frases = get_site_option('golden_shark_frases', []);

    // Guardar nueva frase
    if (isset($_POST['nueva_frase_guardada'])) {
        check_admin_referer('guardar_frase_global_nonce');
        $nueva_frase = sanitize_text_field($_POST['nueva_frase']);
        if (!empty($nueva_frase)) {
            $frases[] = $nueva_frase;
            update_site_option('golden_shark_frases', $frases);
            golden_shark_log('📝 Se agregó una nueva frase global: "' . $nueva_frase . '"');
            echo '<div class="updated"><p>' . __('✅ Frase global agregada correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Editar frase
    if (isset($_POST['editar_frase_guardada'])) {
        check_admin_referer('guardar_edicion_frase_global_nonce');
        $id = intval($_POST['frase_id']);
        if (isset($frases[$id])) {
            $frases[$id] = sanitize_text_field($_POST['nueva_frase']);
            update_site_option('golden_shark_frases', $frases);
            golden_shark_log('✏️ Se editó una frase global en la posición ' . $id);
            echo '<div class="updated"><p>' . __('✅ Frase global actualizada correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Eliminar frase
    if (isset($_GET['eliminar']) && isset($_GET['_nonce'])) {
        $i = intval($_GET['eliminar']);
        if (wp_verify_nonce($_GET['_nonce'], 'eliminar_frase_global_' . $i)) {
            if (isset($frases[$i])) {
                $frase_eliminada = $frases[$i];
                unset($frases[$i]);
                $frases = array_values($frases);
                update_site_option('golden_shark_frases', $frases);
                golden_shark_log('🗑️ Se eliminó una frase global: "' . $frase_eliminada . '"');
                echo '<div class="updated"><p>' . __('🗑️ Frase global eliminada correctamente.', 'golden-shark') .'</p></div>';
            }
        } else {
            wp_die(__('⛔ Seguridad fallida. Token inválido.', 'golden-shark'));
        }
    }

    ?>
    <div class="wrap">
        <h1>🌐 Frases Motivacionales Globales</h1>

        <?php if (isset($_GET['editar'])):
            $id = intval($_GET['editar']);
            if (isset($frases[$id])): ?>
                <h3>✏️ Editar frase global</h3>
                <form method="post">
                    <input type="hidden" name="editar_frase_guardada" value="1">
                    <input type="hidden" name="frase_id" value="<?php echo $id; ?>">
                    <?php wp_nonce_field('guardar_edicion_frase_global_nonce'); ?>
                    <input type="text" name="nueva_frase" value="<?php echo esc_attr($frases[$id]); ?>" class="regular-text" required>
                    <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                </form>
                <hr>
        <?php endif;
        endif; ?>

        <h3>➕ Nueva frase global</h3>
        <form method="post">
            <input type="hidden" name="nueva_frase_guardada" value="1">
            <?php wp_nonce_field('guardar_frase_global_nonce'); ?>
            <input type="text" name="nueva_frase" class="regular-text" placeholder="Ej. Cree en tu potencial." required>
            <p><input type="submit" class="button button-primary" value="Guardar frase"></p>
        </form>

        <hr>
        <h3>📋 Frases globales registradas:</h3>
        <?php if (empty($frases)) : ?>
            <p>No hay frases globales registradas.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($frases as $i => $f): ?>
                    <li>
                        <?php echo esc_html($f); ?> -
                        <a href="<?php echo admin_url('admin.php?page=golden-shark-multisite-panel&editar=' . $i); ?>">Editar</a> |
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-multisite-panel&eliminar=' . $i), 'eliminar_frase_global_' . $i, '_nonce'); ?>" onclick="return confirm('¿Eliminar esta frase global?');">Eliminar</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}
