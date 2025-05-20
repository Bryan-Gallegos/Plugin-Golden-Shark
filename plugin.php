<?php
/*
Plugin Name: Golden Shark Admin Panel
Description: Plugin de administración interno para gestionar eventos, leads y configuración desde el panel de WordPress.
Version: 2.5
Author: Carlos Gallegos
*/

if (!defined('ABSPATH')) exit;

$archivos = [
    'functions.php',
    'menu.php',
    'dashboard.php',
    'eventos.php',
    'frases.php',
    'leads.php',
    'config.php',
    'notas.php',
    'historial.php',
    'shortcodes.php',
    'tareas.php',
    'calendar.php',
    'multisite.php',
    'cron.php',
    'logs.php',
    'historial_sitios.php',
    'editar_sitio.php',
    'config_global.php',
    'frases_globales.php',
    'sites_list.php',
    'roles.php',
    'api.php',
    'webhooks.php',
    'functions-tareas.php',
    'kanban-tareas.php',
    'perfil.php'
];

foreach ($archivos as $archivo) {
    require_once plugin_dir_path(__FILE__) . 'includes/' . $archivo;
}

// Al activar el plugin
register_activation_hook(__FILE__, function() {
    // Programar tarea cron semanal
    if(!wp_next_scheduled('gs_cron_borrar_frases_antiguas')){
        wp_schedule_event(time(), 'weekly', 'gs_cron_borrar_frases_antiguas');
    }

    if (!wp_next_scheduled('gs_cron_enviar_resumen_diario')) {
        wp_schedule_event(time(), 'daily', 'gs_cron_enviar_resumen_diario');
    }
    if (!wp_next_scheduled('golden_shark_enviar_recordatorios_diarios')) {
        wp_schedule_event(time(), 'daily', 'golden_shark_enviar_recordatorios_diarios');
    }
    if (!wp_next_scheduled('gs_cron_informe_mensual')) {
        wp_schedule_event(time(), 'monthly', 'gs_cron_informe_mensual');
    }

    // Crear roles personalizados
    add_role('gs_editor', 'GS Editor', [
        'read' => true,
        'edit_posts' => true,
        'golden_shark_acceso_basico' => true
    ]);

    add_role('gs_supervisor', 'GS Supervisor', [
        'read' => true,
        'edit_posts' => true,
        'golden_shark_acceso_basico' => true,
        'golden_shark_configuracion' => true,
        'golden_shark_ver_logs' => true
    ]);
});

// Al desactivar el plugin
register_desactivation_hook(__FILE__, function() {
    // Eliminar tarea programada
    wp_clear_scheduled_hook('gs_cron_borrar_frases_antiguas');

    wp_clear_scheduled_hook('gs_cron_enviar_resumen_diario');

    wp_clear_scheduled_hook('golden_shark_enviar_recordatorios_diarios');

    wp_clear_scheduled_hook('gs_cron_informe_mensual');

    // Eliminar roles personalizados
    remove_role('gs_editor');
    remove_role('gs_supervisor');
});