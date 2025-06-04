<?php
if(!defined('ABSPATH')) exit;

// Registrar capacidades personalizadas
function golden_shark_register_capacidades_personalizadas(){
    $roles = ['administrator', 'editor']; // Se puede añadir más si usas otros roles

    $capabilities = [
        'golden_shark_ver_eventos',
        'golden_shark_editar_eventos',
        'golden_shark_ver_leads',
        'golden_shark_editar_leads',
        'golden_shark_ver_notas',
        'golden_shark_editar_notas',
        'golden_shark_ver_frases',
        'golden_shark_editar_frases',
        'golden_shark_ver_tareas',
        'golden_shark_editar_tareas',
        'golden_shark_ver_configuracion',
        'golden_shark_editar_configuracion',
        'golden_shark_ver_logs',
    ];

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            foreach ($capabilities as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}