<?php
if (!defined('ABSPATH')) exit;

function golden_shark_render_calendar()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $eventos = get_option('golden_shark_eventos', []);
    $eventos_json = [];

    foreach ($eventos as $evento) {
        if (empty($evento['fecha']) || empty($evento['titulo'])) continue;

        $tipo = isset($evento['tipo']) ? $evento['tipo'] : 'interno';
        $titulo = isset($evento['titulo']) ? $evento['titulo'] : __('Sin título', 'golden-shark');
        $lugar = isset($evento['lugar']) ? $evento['lugar'] : __('Lugar no especificado', 'golden-shark');

        $color = match ($tipo) {
            'reunion' => '#e67e22',
            'lanzamiento' => '#e74c3c',
            default => '#2980b9',
        };

        $eventos_json[] = [
            'title' => '[' . ucfirst($tipo) . '] ' . $titulo,
            'start' => $evento['fecha'],
            'description' => $lugar,
            'color' => $color
        ];
    }

    wp_add_inline_script('golden-shark-admin-script', 'var gsEventos = ' . json_encode($eventos_json) . ';', 'before');

    echo '<div class="wrap">';
    echo '<h2 aria-label="' . esc_attr__('Calendario de Eventos', 'golden-shark') . '">' . __('Calendario de Eventos', 'golden-shark') . '</h2>';
    echo '<div id="gs-calendar" role="application" aria-label="' . esc_attr__('Vista del calendario de eventos', 'golden-shark') . '"></div>';
    echo '<p class="screen-reader-text">' . __('El calendario muestra los eventos del sistema organizados por fecha y tipo.', 'golden-shark') . '</p>';
    echo '</div>';
}
