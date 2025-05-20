<?php
if (!defined('ABSPATH')) exit;

// Crear menú principal y submenús
function golden_shark_admin_menu()
{
    add_menu_page(
        'Golden Shark Panel',
        'Golden Shark 🦈',
        'golden_shark_acceso_basico',
        'golden-shark-dashboard',
        'golden_shark_render_dashboard',
        'dashicons-star-filled',
        26
    );

    // Submenú: Accesos y Roles (solo administrador clásico)
    if (current_user_can('manage_options')) {
        add_submenu_page(
            'golden-shark-dashboard',
            '👥 Accesos y Roles',
            '👥 Accesos y Roles',
            'manage_options',
            'golden-shark-roles',
            'golden_shark_render_roles'
        );
    }

    // Submenús accesibles para quienes tengan acceso básico
    if (current_user_can('golden_shark_acceso_basico')) {
        add_submenu_page(
            'golden-shark-dashboard',
            'Eventos',
            'Eventos',
            'golden_shark_acceso_basico',
            'golden-shark-eventos',
            'golden_shark_render_eventos'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Frases & Mensajes',
            'Frases & Mensajes',
            'golden_shark_acceso_basico',
            'golden-shark-frases',
            'golden_shark_render_frases'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Leads',
            'Leads',
            'golden_shark_acceso_basico',
            'golden-shark-leads',
            'golden_shark_render_leads'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Tareas Internas',
            'Tareas',
            'golden_shark_acceso_basico',
            'golden-shark-tareas',
            'golden_shark_render_tareas'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Notas Internas',
            'Notas Internas',
            'golden_shark_acceso_basico',
            'golden-shark-notas',
            'golden_shark_render_notas'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Calendario de Eventos',
            'Calendario',
            'golden_shark_acceso_basico',
            'golden-shark-calendar',
            'golden_shark_render_calendar'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            'Historial de Actividad',
            'Historial',
            'golden_shark_acceso_basico',
            'golden-shark-historial',
            'golden_shark_render_historial'
        );
    }

    // Submenú: Configuración (requiere capacidad personalizada)
    if (current_user_can('golden_shark_configuracion')) {
        add_submenu_page(
            'golden-shark-dashboard',
            'Configuración',
            'Configuración',
            'golden_shark_configuracion',
            'golden-shark-config',
            'golden_shark_render_config'
        );
    }

    // Submenú: Logs (requiere capacidad para ver logs)
    if (current_user_can('golden_shark_ver_logs')) {
        add_submenu_page(
            'golden-shark-dashboard',
            '📜 Logs del sistema',
            '📜 Logs del sistema',
            'golden_shark_ver_logs',
            'golden-shark-logs',
            'golden_shark_render_logs'
        );
    }

    // Panel Multisitio solo para superadmin en el sitio principal
    if (is_multisite() && is_main_site() && is_super_admin()) {
        add_submenu_page(
            'golden-shark-dashboard',
            '🌐 Panel Multisitio',
            '🌐 Panel Multisitio',
            'manage_network_options',
            'golden-shark-multisite-panel',
            'golden_shark_render_multisite_panel'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            '🌐 Lista de Sitios',
            'Lista de Sitios',
            'manage_network_options',
            'golden-shark-sites',
            'golden_shark_render_sites_list'
        );

        add_submenu_page(
            'golden-shark',
            'Historial de sitios',
            '📜 Historial Sitios',
            'manage_network',
            'gs-historial-sitios',
            'golden_shark_render_historial_sitios'
        );
    }

    add_submenu_page(
        'golden-shark', // slug del menú padre
        'Mi perfil',     // Título de la página
        '👤 Mi perfil',  // Título del menú
        'read',          // Capacidad mínima
        'golden-shark-perfil', // Slug
        'golden_shark_render_perfil_usuario' // Callback
    );
}
add_action('admin_menu', 'golden_shark_admin_menu');

// 🌐 Renderizar el panel multisitio con pestañas
function golden_shark_render_multisite_panel()
{
    if (!is_super_admin()) {
        wp_die('Acceso denegado. Solo el superadministrador puede acceder.');
    }

    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'frases';

    echo '<div class="wrap">';
    echo '<h1>🌐 Panel Multisitio</h1>';
    echo '<nav class="nav-tab-wrapper">';
    echo '<a href="?page=golden-shark-multisite-panel&tab=frases" class="nav-tab ' . ($tab === 'frases' ? 'nav-tab-active' : '') . '">Frases Globales</a>';
    echo '<a href="?page=golden-shark-multisite-panel&tab=config" class="nav-tab ' . ($tab === 'config' ? 'nav-tab-active' : '') . '">Configuración Global</a>';
    echo '</nav>';

    echo '<div style="margin-top: 20px;">';
    if ($tab === 'config') {
        golden_shark_render_config_global();
    } else {
        golden_shark_render_frases_globales();
    }
    echo '</div>';
    echo '</div>';
}

// ✅ Cargar los archivos necesarios para el panel multisitio
require_once plugin_dir_path(__FILE__) . 'frases_globales.php';
require_once plugin_dir_path(__FILE__) . 'config_global.php';
require_once plugin_dir_path(__FILE__) . 'sites_list.php';
