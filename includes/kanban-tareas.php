<?php

if(!defined('ABSPATH')) exit;

// Vista Kanban de Tareas
function golden_shark_render_kanban(){
    if(!golden_shark_user_can('golden_shark_acceso_basico')){
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $tareas = get_option('golden_shark_tareas', []);
    $pendientes = [];
    $progreso = [];
    $completadas = [];

    foreach($tareas as $i => $tarea){
        $tarea['id'] = $i;
        switch ($tarea['estado']){
            case 'completado': $completadas[] = $tarea; break;
            case 'progreso': $progreso[] = $tarea; break;
            default: $pendientes[] = $tarea; break;
        }
    }

    ?>
    <div class="wrap">
        <h2>Vista Kanban - Tareas internas</h2>
        <div id="gs-kanban" style="display: flex; gap: 20px;">
            <?php foreach (['pendiente' => $pendientes, 'progreso' => $progreso, 'completado' => $completadas] as $estado => $lista): ?>
                <div style="flex: 1; background: #f9f9f9; padding: 10px; border: 1px solid #ccc;">
                    <h3 style="text-align: center;"><?php echo ucfirst($estado); ?></h3>
                    <?php if (empty($lista)): ?>
                        <p style="text-align: center;">Sin tareas</p>
                    <?php else: ?>
                        <?php foreach ($lista as $t): ?>
                            <div style="background: white; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd;">
                                <strong><?php echo esc_html($t['titulo']); ?></strong><br>
                                <small>Fecha: <?php echo esc_html($t['fecha']); ?></small><br>
                                <small>Responsable: <?php echo get_userdata($t['responsable'])->display_name ?? 'N/A'; ?></small>
                            </div>
                        <?php endforeach ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

//Registrar en el menú (opcional si no se añade desde otro lado)
function golden_shark_add_kanban_menu(){
    add_submenu_page(
        'golden-shark-panel',
        'Vista Kanban',
        'Kanban',
        'golden_shark_acceso_basico',
        'golden-shark-kanban',
        'golden_shark_render_kanban'
    );
}
add_action('admin_menu', 'golden_shark_add_kanban_menu');