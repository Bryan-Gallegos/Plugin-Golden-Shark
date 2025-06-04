<?php
if (!defined('ABSPATH')) exit;

/**
 * Renderiza el historial de cambios por sitio dentro de una red multisitio.
 * Solo accesible por superadministradores.
 */
function golden_shark_render_historial_sitios() {
    if (!is_multisite() || !is_super_admin()) {
        wp_die(__('⛔ Acceso restringido solo a superadministradores', 'golden-shark'));
    }

    echo '<div class="wrap gs-container">';
    echo '<h2>' . __('📜 Historial por sitio remoto', 'golden-shark') . '</h2>';
    echo '<p>' . __('Este registro muestra los últimos cambios realizados en cada sitio de la red multisitio.', 'golden-shark') . '</p>';

    $sitios = get_sites(['public' => 1]);

    if (empty($sitios)) {
        echo '<p>' . __('No hay sitios disponibles en la red.', 'golden-shark') . '</p>';
        echo '</div>';
        return;
    }

    foreach ($sitios as $sitio) {
        $id = $sitio->blog_id;
        $info = get_blog_details($id);
        $historial = get_site_option("gs_historial_site_$id", []);

        if (empty($historial)) continue;

        $nombre = esc_html($info->blogname);
        $url = esc_url($info->siteurl);

        echo '<section class="gs-subbox" role="region" aria-label="' . esc_attr__("Historial del sitio", 'golden-shark') . ' ' . $nombre . '">';
        echo '<h3>🌐 ' . $nombre . ' <small>(' . $url . ')</small></h3>';

        echo '<ul style="margin-left: 20px;">';
        $ultimos_registros = array_slice(array_reverse($historial), 0, 10);
        foreach ($ultimos_registros as $registro) {
            $fecha = esc_html($registro['fecha']);
            $usuario = esc_html($registro['usuario']);
            $cambios = esc_html($registro['cambios']);
            echo "<li><strong>$fecha</strong> – <strong>$usuario</strong>: $cambios</li>";
        }
        echo '</ul>';

        if (count($historial) > 10) {
            echo '<p><em>' . __('⚠️ Solo se muestran los últimos 10 registros para este sitio.', 'golden-shark') . '</em></p>';
        }

        echo '</section>';
    }

    echo '</div>';
}
