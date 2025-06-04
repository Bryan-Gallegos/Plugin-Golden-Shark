<?php
if (!defined('ABSPATH')) exit;

function golden_shark_render_sites_list()
{
    if (!is_multisite() || !is_main_site() || !is_super_admin()) {
        wp_die(__('No tienes permisos para acceder a esta sección.', 'golden-shark'));
    }

    // Exportar a CSV si se solicita
    if (isset($_GET['exportar_csv']) && $_GET['exportar_csv'] === '1') {
        $sites = get_sites(['public' => 1, 'orderby' => 'registered', 'order' => 'DESC']);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="lista_sitios.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['#', 'Nombre', 'URL', 'ID', 'Estado']);
        foreach ($sites as $index => $site) {
            $info = get_blog_details($site->blog_id);
            $nombre = isset($info->blogname) ? $info->blogname : '(' . __('sin nombre', 'golden-shark') . ')';
            $url = $info->siteurl;
            $estado = [];
            if ($info->archived) $estado[] = __('Archivado', 'golden-shark');
            if ($info->deleted) $estado[] = __('Eliminado', 'golden-shark');
            if ($info->spam) $estado[] = __('Spam', 'golden-shark');
            if (empty($estado)) $estado[] = __('Activo', 'golden-shark');
            fputcsv($output, [
                $index + 1,
                $nombre,
                $url,
                $site->blog_id,
                implode(', ', $estado)
            ]);
        }
        fclose($output);
        exit;
    }

    echo '<div class="wrap">';
    echo '<h1>' . __('🌐 Sitios en la Red', 'golden-shark') . '</h1>';

    $sites = get_sites(['public' => 1, 'orderby' => 'registered', 'order' => 'DESC']);

    echo '<p>' . sprintf(__('Total de sitios en la red: %d', 'golden-shark'), count($sites)) . '</p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=golden-shark-sites-list&exportar_csv=1')) . '" class="button button-secondary">' . __('📤 Exportar a CSV', 'golden-shark') . '</a></p>';

    if (empty($sites)) {
        echo '<p>' . __('No se encontraron sitios en la red.', 'golden-shark') . '</p>';
    } else {
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>#</th>';
        echo '<th>' . __('Nombre', 'golden-shark') . '</th>';
        echo '<th>' . __('URL', 'golden-shark') . '</th>';
        echo '<th>' . __('ID', 'golden-shark') . '</th>';
        echo '<th>' . __('Estado', 'golden-shark') . '</th>';
        echo '<th>' . __('Acciones', 'golden-shark') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($sites as $index => $site) {
            $info = get_blog_details($site->blog_id);
            $nombre = isset($info->blogname) ? $info->blogname : '(' . __('sin nombre', 'golden-shark') . ')';
            $estado = [];

            if ($info->archived) $estado[] = __('Archivado', 'golden-shark');
            if ($info->deleted) $estado[] = __('Eliminado', 'golden-shark');
            if ($info->spam) $estado[] = __('Spam', 'golden-shark');
            if (empty($estado)) $estado[] = __('Activo', 'golden-shark');

            echo '<tr>';
            echo '<td>' . ($index + 1) . '</td>';
            echo '<td>' . esc_html($nombre) . '</td>';
            echo '<td><a href="' . esc_url($info->siteurl) . '" target="_blank">' . esc_html($info->siteurl) . '</a></td>';
            echo '<td>' . intval($site->blog_id) . '</td>';
            echo '<td>' . esc_html(implode(', ', $estado)) . '</td>';
            echo '<td><a class="button" href="' . esc_url($info->siteurl . '/wp-admin/') . '" target="_blank">' . __('Administrar', 'golden-shark') . '</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    echo '</div>';
}
