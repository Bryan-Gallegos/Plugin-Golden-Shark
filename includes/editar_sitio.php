<?php

if (!defined('ABSPATH')) exit;

// Edicion remota por sitio individual
function golden_shark_render_editar_sitio()
{
    if (!is_super_admin()) {
        wp_die('Acceso restringido solo a superadministradores');
    }

    $sitio_id = intval($_GET['sitio'] ?? 0);
    $info = get_blog_details($sitio_id);
    if (!$info) {
        printf('<div class="notice notice-error"><p>' . __('⛔ El sitio con ID %s no existe.', 'golden-shark') . '</p></div>', $sitio_id);
        return;
    }


    switch_to_blog($sitio_id);

    $frases = get_option('golden_shark_frases', []);
    $color = get_option('golden_shark_color_dashboard', '#0073aa');
    $mensaje = get_option('golden_shark_mensaje_motivacional', '');
    $notificaciones = get_option('golden_shark_habilitar_notificaciones', '1');
    $mensaje_correo = get_option('golden_shark_mensaje_correo', '');

    // Guardar cambios remotos
    if (isset($_POST['guardar_cambios_remotos']) && check_admin_referer('guardar_cambios_remotos')) {
        update_option('golden_shark_frases', array_map('sanitize_text_field', $_POST['frases']));
        update_option('golden_shark_color_dashboard', sanitize_hex_color($_POST['color']));
        update_option('golden_shark_mensaje_motivacional', sanitize_text_field($_POST['mensaje']));
        update_option('golden_shark_mensaje_correo', sanitize_text_field($_POST['mensaje_correo']));
        update_option('golden_shark_habilitar_notificaciones', isset($_POST['notificaciones']) ? '1' : '0');

        golden_shark_log("🔧 Cambios remotos guardados para el sitio #$sitio_id", 'warning');
        golden_shark_guardar_historial_sitio($sitio_id, 'Actualizó frases y configuración desde el panel multisitio');

        // Logs individuales para auditoría
        golden_shark_log("Color del dashboard actualizado a: " . sanitize_hex_color($_POST['color']), 'info');
        golden_shark_log("Mensaje motivacional: \"" . sanitize_text_field($_POST['mensaje']) . "\"", 'info');
        golden_shark_log("Mensaje en correos: \"" . sanitize_text_field($_POST['mensaje_correo']) . "\"", 'info');
        golden_shark_log("Notificaciones: " . (isset($_POST['notificaciones']) ? 'activadas' : 'desactivadas'), 'info');

        if (!empty($_POST['frases'])) {
            golden_shark_log("Se guardaron " . count($_POST['frases']) . " frases motivacionales en el sitio #$sitio_id", 'info');
        }

        echo '<div class="notice notice-success"><p>' . __('✅ Cambios guardados en el sitio remoto.', 'golden-shark') . '</p></div>';

        // Recargar valores actualizados
        $frases = get_option('golden_shark_frases', []);
        $color = get_option('golden_shark_color_dashboard', '#0073aa');
        $mensaje = get_option('golden_shark_mensaje_motivacional', '');
        $notificaciones = get_option('golden_shark_habilitar_notificaciones', '1');
        $mensaje_correo = get_option('golden_shark_mensaje_correo', '');
    }

    $info = get_blog_details($sitio_id);
    $nombre = esc_html($info->blogname);
    $url = esc_url($info->siteurl);

    echo '<div class="wrap">';
    printf('<h2>' . __('🛠️ Editar sitio remoto: %s', 'golden-shark') . '</h2>', $nombre);
    echo "<p><a href=\"$url\" target=\"_blank\">Visitar sitio</a></p>";

    echo '<form method="post">';
    wp_nonce_field('guardar_cambios_remotos');

    echo '<div class="gs-container">';
    echo '<h3>🎨 Configuración general</h3>';
    echo '<table class="form-table">';
    echo '<tr><th><label for="color">' . __('Color del dashboard', 'golden-shark') . '</label></th><td><input type="color" id="color" name="color" value="' . esc_attr($color) . '"></td></tr>';
    echo '<tr><th><label for="mensaje">' . __('Mensaje motivacional:', 'golden-shark') . '</label></th><td><input type="text" id="mensaje" name="mensaje" class="regular-text" value="' . esc_attr($mensaje) . '"></td></tr>';
    echo '<tr><th><label for="mensaje_correo">' . __('Mensaje en correos:', 'golden-shark') . '</label></th><td><textarea id="mensaje_correo" name="mensaje_correo" rows="3" class="large-text">' . esc_textarea($mensaje_correo) . '</textarea></td></tr>';
    echo '<tr><th>' . __('Notificaciones:', 'golden-shark') . '</th><td><label><input type="checkbox" name="notificaciones" value="1" ' . checked($notificaciones, '1', false) . '> ' . __('Habilitadas', 'golden-shark') . '</label></td></tr>';
    echo '</table>';
    echo '</div>';

    echo '<div class="gs-container">';
    echo '<h3>' . __('💬 Frases motivacionales', 'golden-shark') . '</h3>';
    if (empty($frases)) $frases[] = '';
    echo '<div class="frases-container">';
    foreach ($frases as $i => $frase) {
        echo '<p><input type="text" name="frases[]" class="widefat" value="' . esc_attr($frase) . '"></p>';
    }
    echo '</div>';
    echo '<p><button type="button" class="button" onclick="agregarCampoFrase()">' . __('➕ Agregar otra frase', 'golden-shark') . '</button></p>';
    echo '</div>';

    echo '<p><input type="submit" name="guardar_cambios_remotos" class="button button-primary" value="' . __('💾 Guardar todos los cambios', 'golden-shark') . '"></p>';
    echo '</form>';

    $historial = get_site_option("gs_historial_sitio_$sitio_id", []);
    if (!empty($historial)) {
        echo '<div class="gs-container">';
        echo '<h3>' . __('📜 Historial de cambios remotos', 'golden-shark') . '</h3>';
        echo '<ul style="margin-left: 20px;">';
        foreach (array_reverse($historial) as $registro) {
            echo '<li><strong>' . esc_html($registro['fecha']) . '</strong> - ';
            echo esc_html($registro['usuario']) . ': ';
            echo esc_html($registro['cambios']) . '</li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    echo '</div>';

    restore_current_blog();
}

// En cola para usar en multisite.php -> admin menu
function golden_shark_menu_editar_sitio()
{
    if (!is_super_admin()) {
        add_submenu_page(null, 'Editar sitio remoto', 'Editar sitio', 'manage_network', 'gs-editar-sitio', 'golden_shark_menu_editar_sitio');
    }
}

add_action('admin_menu', 'golden_shark_menu_editar_sitio');

//JS para añadir frases dinámicamente
add_action('admin_footer', function () {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_golden_shark') return;
?>
    <script>
        function agregarCampoFrase() {
            const container = document.querySelector('.frases-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'frases[]';
            input.className = 'widefat';
            input.placeholder = 'Nueva frase...';
            container.appendChild(input);
        }
    </script>
<?php
});
