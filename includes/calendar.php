<?php
if (!defined('ABSPATH')) exit;

// 📅 CALENDARIO DE EVENTOS
function golden_shark_render_calendar()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $eventos = get_option('golden_shark_eventos', []);
    $eventos_json = [];

    foreach ($eventos as $evento) {
        $color = '#0073aa';
        if (isset($evento['tipo'])){
            switch($evento['tipo']){
                case 'reunion':
                    $color = '#e67e22';
                    break;
                case 'lanzamiento':
                    $color = '#e74c3c';
                    break;
                case 'interno':
                default:
                    $color = '#2980b9';
                    break;
            }
        }

        $eventos_json[] = [
            'title' => '[' . ucfirst($evento['tipo']) . '] ' . $evento['titulo'],
            'start' => $evento['fecha'],
            'description' => $evento['lugar'],
            'color' => $color
        ];
    }

    //Pasar eventos a JS
    wp_add_inline_script('golden-shark-admin-script', 'var gsEventos = ' . json_encode($eventos_json) . ';', 'before');

    echo '<div class="wrap">';
    echo '<h2>' . __('Calendario de Eventos', 'golden-shark') .'</h2>';
    echo '<div id="gs-calendar"></div>';
    echo '</div>';
}
