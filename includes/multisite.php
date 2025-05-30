<?php
if (!defined('ABSPATH')) exit;

// 🌐 MULTISITE: migrar frases a site_option
function golden_shark_migrar_frases_a_site_option() {
    if (!is_multisite()) return;

    $opcion_local = get_option('golden_shark_frases');
    $opcion_global = get_site_option('golden_shark_frases');

    if ($opcion_local && !$opcion_global) {
        update_site_option('golden_shark_frases', $opcion_local);
        delete_option('golden_shark_frases');
        golden_shark_log('✅ Frases migradas a nivel multisite');
    }
}
add_action('admin_init', 'golden_shark_migrar_frases_a_site_option');

// 📡 Obtener frases (multisitio o local)
function golden_shark_get_frases() {
    return is_multisite() ? get_site_option('golden_shark_frases', []) : get_option('golden_shark_frases', []);
}

// 🌐 Guardar frases
function golden_shark_set_frases($frases) {
    return is_multisite() ? update_site_option('golden_shark_frases', $frases) : update_option('golden_shark_frases', $frases);
}

// 🌐 Migrar configuraciones a nivel multisite
function golden_shark_migrar_configuraciones_a_site_option() {
    if (!is_multisite()) return;

    $claves = [
        'golden_shark_color_dashboard',
        'golden_shark_mensaje_motivacional',
        'golden_shark_mensaje_correo',
        'golden_shark_habilitar_notificaciones'
    ];

    foreach ($claves as $clave) {
        $valor_local = get_option($clave, null);
        $valor_global = get_site_option($clave, null);

        if (!is_null($valor_local) && is_null($valor_global)) {
            update_site_option($clave, $valor_local); 
            delete_option($clave);
            golden_shark_log('⚙️ Migrada la configuración "' . $clave . '" al modo multisitio');
        }
    }
}
add_action('admin_init', 'golden_shark_migrar_configuraciones_a_site_option'); 

// 📡 Get / Set configuración desde site_option
function golden_shark_get_config($clave, $default = ''){
    return is_multisite() ? get_site_option($clave, $default) : get_option($clave, $default);
}

function golden_shark_set_config($clave, $valor){
    return is_multisite() ? update_site_option($clave, $valor) : update_option($clave, $valor);
}

// 🔍 Obtener todos los sitios públicos
function golden_shark_get_all_sites(){
    return is_multisite() ? get_sites(['public' => 1]) : [];
}

// 🧭 Vista: listado de sitios con edición remota
function golden_shark_render_multisite_panel() {
    if (!is_super_admin()) {
        wp_die(__('⛔ Acceso restringido solo a superadministradores.', 'golden-shark'));
    }

    echo '<div class="wrap">';
    echo '<h2>' . esc_html__('🌐 Panel Multisitio', 'golden-shark') . '</h2>';
    echo '<p>' . esc_html__('Desde aquí puedes gestionar frases y configuración global o editar remotamente los sitios de la red.', 'golden-shark') . '</p>';

    echo '<h3>' . esc_html__('🎛️ Edición remota por sitio', 'golden-shark') . '</h3>';
    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th>' . esc_html__('Sitio', 'golden-shark') . '</th>';
    echo '<th>' . esc_html__('URL', 'golden-shark') . '</th>';
    echo '<th>' . esc_html__('Acciones', 'golden-shark') . '</th>';
    echo '</tr></thead><tbody>';

    foreach (golden_shark_get_all_sites() as $sitio) {
        $blog_id = $sitio->blog_id;
        $info = get_blog_details($blog_id);
        if (!$info) continue;

        $url = esc_url($info->siteurl);
        $nombre = esc_html($info->blogname);

        echo '<tr>';
        echo "<td>$nombre</td>";
        echo "<td><a href=\"$url\" target=\"_blank\">$url</a></td>";
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=gs-editar-sitio&sitio=' . $blog_id)) . '" class="button">' . esc_html__('Editar frases / config', 'golden-shark') . '</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}