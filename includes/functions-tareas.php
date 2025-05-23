<?php
if (!defined('ABSPATH')) exit;

function golden_shark_crear_tareas_automaticas($evento)
{
    $tipo = $evento['tipo'];
    $fecha_evento = strtotime($evento['fecha']);

    if (!$tipo || !$fecha_evento) return;

    $tareas_predefinidas = [
        'interno' => [
            ['titulo' => 'Revisar logística del evento', 'dias_antes' => 3],
            ['titulo' => 'Enviar recordatorio al equipo', 'dias_antes' => 1],
        ],
        'reunion' => [
            ['titulo' => 'Preparar agenda de la reunión', 'dias_antes' => 2],
            ['titulo' => 'Confirmar participantes', 'dias_antes' => 1],
        ],
        'lanzamiento' => [
            ['titulo' => 'Revisar presentación final', 'dias_antes' => 4],
            ['titulo' => 'Programar publicación en redes', 'dias_antes' => 2],
        ]
    ];

    $tareas_actuales = get_option('golden_shark_tareas', []);

    foreach ($tareas_predefinidas[$tipo] ?? [] as $tarea) {
        $fecha_tarea = date('Y-m-d', strtotime("-{$tarea['dias_antes']} days", $fecha_evento));
        $tareas_actuales[] = [
            'titulo' => $tarea['titulo'],
            'fecha' => $fecha_tarea,
            'estado' => 'pendiente',
            'responsable' => get_current_user_id(),
            'historial' => [['accion' => 'Creada automáticamente por evento', 'fecha' => current_time('Y-m-d H:i:s')]]
        ];
    }

    update_option('golden_shark_tareas', $tareas_actuales);
}

function golden_shark_enviar_recordatorios_tareas()
{
    $tareas = get_option('golden_shark_tareas', []);
    if (empty($tareas)) return;

    $hoy = date('Y-m-d');
    $maniana = date('Y-m-d', strtotime('+1 day'));
    $usuarios_con_tareas = [];

    foreach ($tareas as $tarea) {
        if ($tarea['estado'] !== 'pendiente') continue;

        if ($tarea['fecha'] === $hoy || $tarea['fecha'] === $maniana) {
            $uid = $tarea['responsable'] ?? 0;
            if (!$uid) continue;

            $usuarios_con_tareas[$uid][] = $tarea;
        }
    }

    foreach ($usuarios_con_tareas as $user_id => $tareas_usuario) {
        $user_info = get_userdata($user_id);
        if (!$user_info || !is_email($user_info->user_email)) continue;

        $mensaje = "Hola " . $user_info->display_name . ",\n\n";
        $mensaje .= "Estas son tus tareas pendientes para hoy o mañana:\n\n";

        foreach ($tareas_usuario as $tarea) {
            $mensaje .= "- " . $tarea['titulo'] . " (Fecha: " . $tarea['fecha'] . ")\n";
        }

        $mensaje .= "\nPor favor ingresa al panel para actualizarlas. 🦈\n\n";
        $mensaje .= get_bloginfo('name');

        wp_mail($user_info->user_email, '🔔 Recordatorio de tareas pendientes - Golden Shark', $mensaje);
    }
}
