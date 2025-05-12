<?php
if (!defined('ABSPATH')) exit;

function golden_shark_render_sites_list() {
    if (!is_multisite() || !is_main_site() || !is_super_admin()) {
        wp_die('No tienes permisos para acceder a esta sección.');
    }

    echo '<div class="wrap">';
    echo '<h1>🌐 Sitios en la Red</h1>';

    $sites = get_sites(['public' => 1]);

    if (empty($sites)) {
        echo '<p>No se encontraron sitios en la red.</p>';
    } else {
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>#</th><th>Nombre</th><th>URL</th><th>ID</th></tr></thead><tbody>';
        foreach ($sites as $index => $site) {
            $info = get_blog_details($site->blog_id);
            echo '<tr>';
            echo '<td>' . ($index + 1) . '</td>';
            echo '<td>' . esc_html($info->blogname ?? '(sin nombre)') . '</td>';
            echo '<td><a href="' . esc_url($info->siteurl) . '" target="_blank">' . esc_html($info->siteurl) . '</a></td>';
            echo '<td>' . intval($site->blog_id) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    echo '</div>';
}