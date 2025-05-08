<?php
if (!defined('ABSPATH')) exit;

// Crear menú principal y submenús
function golden_shark_admin_menu()
{
    add_menu_page(
        'Golden Shark Panel',
        'Golden Shark 🦈',
        'administrator',
        'golden-shark-dashboard',
        'golden_shark_render_dashboard',
        'dashicons-star-filled',
        26
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Eventos',
        'Eventos',
        'edit_posts',
        'golden-shark-eventos',
        'golden_shark_render_eventos'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Frases & Mensajes',
        'Frases & Mensajes',
        'edit_posts',
        'golden-shark-frases',
        'golden_shark_render_frases'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Leads',
        'Leads',
        'edit_posts',
        'golden-shark-leads',
        'golden_shark_render_leads'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Configuración',
        'Configuración',
        'manage_options',
        'golden-shark-config',
        'golden_shark_render_config'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Notas Internas',
        'Notas Internas',
        'edit_posts',
        'golden-shark-notas',
        'golden_shark_render_notas'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Historial de Actividad',
        'Historial',
        'manage_options',
        'golden-shark-historial',
        'golden_shark_render_historial'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        'Tareas Internas',
        'Tareas',
        'edit_posts',
        'golden-shark-tareas',
        'golden_shark_render_tareas'
    );
}

add_action('admin_menu', 'golden_shark_admin_menu');
