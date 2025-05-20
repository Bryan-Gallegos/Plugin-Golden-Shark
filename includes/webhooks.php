<?php
if (!defined('ABSPATH')) exit;

// 📤 Enviar webhook al crear o editar un evento
function golden_shark_disparar_webhook_evento($evento)
{
    $url = get_option('golden_shark_webhook_eventos_url', '');
    if (!$url) return;

    $respuesta = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($evento),
        'timeout' => 10,
    ]);

    if (is_wp_error($respuesta)) {
        golden_shark_log('❌ Error al enviar webhook de evento: ' . $respuesta->get_error_message());
    } else {
        golden_shark_log('📤 Webhook de evento enviado correctamente a ' . $url);
    }
}