<?php
if (!defined('ABSPATH')) exit;

// 📤 Enviar webhook al crear o editar un evento
function golden_shark_disparar_webhook_evento($evento)
{
    $url = get_option('golden_shark_webhook_eventos_url', '');
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        golden_shark_log(__('⚠️ URL de webhook no válida o no configurada.', 'golden-shark'));
        return;
    }

    if (!is_array($evento)) {
        golden_shark_log(__('⚠️ El evento proporcionado no es un array.', 'golden-shark'));
        return;
    }

    $respuesta = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($evento),
        'timeout' => 10,
    ]);

    if (is_wp_error($respuesta)) {
        golden_shark_log(__('❌ Error al enviar webhook de evento:', 'golden-shark') . ' ' . $respuesta->get_error_message());
    } else {
        $code = wp_remote_retrieve_response_code($respuesta);
        golden_shark_log(sprintf(__('📤 Webhook enviado a %s - Código de respuesta: %s', 'golden-shark'), $url, $code));
    }
}