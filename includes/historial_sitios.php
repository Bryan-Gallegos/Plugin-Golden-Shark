<?php

if(!defined('ABSPATH')) exit;

// Historial global de cambios en sitios de red
function golden_shark_render_historial_sitios(){
    if(!is_multisite() || !is_super_admin()){
        wp_die('Aceso restringido solo a superadministradores');
    }

    echo '<div class="wrap gs-container">';
    echo '<h3>📜 Historial por sitio remoto</h3>';
    echo '<p>Este registro muestra los últimos cambios realizados en cada sitio de la web</p>';

    $sitios = get_sites(['public' => 1]);

    if(empty($sitios)){
        echo '<p>No hay sitios disponibles.</p>';
        echo '</div>';
        return;
    }

    foreach($sitios as $sitio){
        $id = $sitio->blog_id;
        $info = get_blog_details($id);
        $historial = get_site_option("gshistorial_sitio_$id", []);

        if(empty($historial)) continue;

        echo '<div class="gs-subbox">';
        echo '<h3>🌐 ' . esc_html($info->blogname) . ' <small>(' . esc_url($info->siteurl) . ')</small></h3>';
        echo '<ul style="margin-left: 20px;">';
        foreach(array_reverse($historial) as $registro){
            echo '<li><strong>' . esc_html($registro['fecha']) . '</strong> - ';
            echo esc_html($registro['usuario']) . ': ';
            echo esc_html($registro['cambios']) . '</li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    echo '</div>';
}