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
        golden_shark_log('Frases migradas a nivel multisite');
    }
}
add_action('admin_init', 'golden_shark_migrar_frases_a_site_option');

// 📡 Obtener frases (multisitio o local)
function golden_shark_get_frases() {
    if (is_multisite()) {
        return get_site_option('golden_shark_frases', []);
    }
    return get_option('golden_shark_frases', []);
}

// 🌐 Guardar frases
function golden_shark_set_frases($frases) {
    if (is_multisite()) {
        return update_site_option('golden_shark_frases', $frases);
    }
    return update_option('golden_shark_frases', $frases);
}

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
            golden_shark_log('Migrada la configuración "' . $clave . '" al modo multisitio');
        }
    }
}
add_action('admin_init', 'golden_shark_migrar_configuraciones_a_site_option'); 

function golden_shark_get_config($clave, $default = ''){
    if(is_multisite()){
        return get_site_option($clave, $default);
    }
    return get_option($clave, $default);
}

function golden_shark_set_config($clave, $valor){
    if(is_multisite()){
        return update_site_option($clave, $valor);
    }
    return update_option($clave, $valor);
}