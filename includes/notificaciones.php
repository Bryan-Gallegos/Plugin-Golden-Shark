<?php

if (!defined('ABSPATH')) exit;

// 🔔 Enviar notificación interna a un usuario
function golden_shark_notificar_usuario($user_id, $mensaje) {
    if (!$user_id || !$mensaje) return;

    $notificaciones = get_user_meta($user_id, 'golden_shark_notificaciones', true);
    if (!is_array($notificaciones)) $notificaciones = [];

    $notificaciones[] = [
        'mensaje' => sanitize_text_field($mensaje),
        'fecha'   => current_time('mysql'),
        'leido'   => false,
    ];

    update_user_meta($user_id, 'golden_shark_notificaciones', $notificaciones);
}

// 🔎 Obtener notificaciones no leídas del usuario actual
function golden_shark_get_notificaciones_actual() {
    $user_id = get_current_user_id();
    $notificaciones = get_user_meta($user_id, 'golden_shark_notificaciones', true);
    if (!is_array($notificaciones)) return [];

    return array_filter($notificaciones, 'golden_shark_filtrar_no_leidas');
}

// 🔎 Filtro auxiliar para notificaciones no leídas
function golden_shark_filtrar_no_leidas($n) {
    return isset($n['leido']) && !$n['leido'];
}