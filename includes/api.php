<?php
if (!defined('ABSPATH')) exit;

// Ruta personalizada: /wp-json/golden-shark/v1/*
add_action('rest_api_init', function () {
    register_rest_route('golden-shark/v1', '/leads', [
        'methods'  => 'POST',
        'callback' => 'golden_shark_api_nuevo_lead',
        'permission_callback' => 'golden_shark_api_check_key'
    ]);

    register_rest_route('golden-shark/v1', '/eventos', [
        'methods'  => 'GET',
        'callback' => 'golden_shark_api_listar_eventos',
        'permission_callback' => 'golden_shark_api_check_key'
    ]);
});

// Verificar clave API
function golden_shark_api_check_key(WP_REST_Request $request) {
    $provided = $request->get_header('X-GS-API-Key');
    $saved = get_option('golden_shark_api_key');
    return $provided && $saved && hash_equals($saved, $provided);
}

// Registrar nuevo lead
function golden_shark_api_nuevo_lead(WP_REST_Request $req) {
    $nombre  = sanitize_text_field($req->get_param('nombre'));
    $correo  = sanitize_email($req->get_param('correo'));
    $mensaje = sanitize_textarea_field($req->get_param('mensaje'));

    if (!$nombre || !$correo) {
        return new WP_REST_Response(['error' => 'Nombre y correo son obligatorios'], 400);
    }

    $leads = get_option('golden_shark_leads', []);
    $leads[] = [
        'nombre'   => $nombre,
        'correo'   => $correo,
        'mensaje'  => $mensaje,
        'fecha'    => current_time('Y-m-d H:i:s')
    ];
    update_option('golden_shark_leads', $leads);
    golden_shark_log("📥 Lead agregado vía API: $nombre ($correo)");

    return ['success' => true];
}

// Listar eventos
function golden_shark_api_listar_eventos() {
    $eventos = get_option('golden_shark_eventos', []);
    return rest_ensure_response($eventos);
}