<?php
if (!defined('ABSPATH')) exit;

/**
 * Crear tareas automáticas asociadas a eventos según tipo y fecha.
 */
function golden_shark_crear_tareas_automaticas($evento)
{
    if (!isset($evento['tipo'], $evento['fecha'])) return;

    $tipo = sanitize_text_field($evento['tipo']);
    $fecha_evento = strtotime(sanitize_text_field($evento['fecha']));

    if (!$tipo || !$fecha_evento) return;

    $tareas_predefinidas = apply_filters('golden_shark_tareas_predefinidas', [
        'interno' => [
            ['titulo' => __('Revisar logística del evento', 'golden-shark'), 'dias_antes' => 3],
            ['titulo' => __('Enviar recordatorio al equipo', 'golden-shark'), 'dias_antes' => 1],
        ],
        'reunion' => [
            ['titulo' => __('Preparar agenda de la reunión', 'golden-shark'), 'dias_antes' => 2],
            ['titulo' => __('Confirmar participantes', 'golden-shark'), 'dias_antes' => 1],
        ],
        'lanzamiento' => [
            ['titulo' => __('Revisar presentación final', 'golden-shark'), 'dias_antes' => 4],
            ['titulo' => __('Programar publicación en redes', 'golden-shark'), 'dias_antes' => 2],
        ]
    ]);

    $tareas_actuales = get_option('golden_shark_tareas', []);

    foreach ($tareas_predefinidas[$tipo] ?? [] as $tarea) {
        $fecha_tarea = date('Y-m-d', strtotime("-{$tarea['dias_antes']} days", $fecha_evento));

        $tareas_actuales[] = [
            'titulo' => sanitize_text_field($tarea['titulo']),
            'fecha' => $fecha_tarea,
            'estado' => 'pendiente',
            'responsable' => get_current_user_id(),
            'historial' => [[
                'accion' => __('Creada automáticamente por evento', 'golden-shark'),
                'fecha' => current_time('Y-m-d H:i:s')
            ]]
        ];
    }

    update_option('golden_shark_tareas', $tareas_actuales);
}

/**
 * Enviar correos recordatorios de tareas para hoy o mañana.
 */
function golden_shark_enviar_recordatorios_tareas()
{
    $tareas = get_option('golden_shark_tareas', []);
    if (empty($tareas)) return;

    $hoy = date('Y-m-d');
    $maniana = date('Y-m-d', strtotime('+1 day'));
    $usuarios_con_tareas = [];

    foreach ($tareas as $tarea) {
        if (($tarea['estado'] ?? '') !== 'pendiente') continue;

        if (in_array($tarea['fecha'], [$hoy, $maniana])) {
            $uid = $tarea['responsable'] ?? 0;
            if ($uid) {
                $usuarios_con_tareas[$uid][] = $tarea;
            }
        }
    }

    foreach ($usuarios_con_tareas as $user_id => $tareas_usuario) {
        $user_info = get_userdata($user_id);
        if (!$user_info || !is_email($user_info->user_email)) continue;

        $mensaje = sprintf(
            __("Hola %s,\n\nEstas son tus tareas pendientes para hoy o mañana:\n\n", 'golden-shark'),
            $user_info->display_name
        );

        foreach ($tareas_usuario as $tarea) {
            $mensaje .= "- " . esc_html($tarea['titulo']) . " (" . __('Fecha:', 'golden-shark') . " " . $tarea['fecha'] . ")\n";
        }

        $mensaje .= "\n" . __('Por favor ingresa al panel para actualizarlas.', 'golden-shark') . " 🦈\n\n";
        $mensaje .= get_bloginfo('name');

        wp_mail(
            $user_info->user_email,
            __('🔔 Recordatorio de tareas pendientes - Golden Shark', 'golden-shark'),
            $mensaje
        );
    }
}