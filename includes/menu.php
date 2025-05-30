<?php
if (!defined('ABSPATH')) exit;

// Crear menú principal y submenús
function golden_shark_admin_menu()
{
    add_menu_page(
        __('Golden Shark Panel', 'golden-shark'),
        __('Golden Shark 🦈', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-dashboard',
        'golden_shark_render_dashboard',
        'dashicons-star-filled',
        26
    );

    add_menu_page(
        __('Resumen', 'golden-shark'),
        __('Resumen', 'golden-shark'),
        'read',
        'golden-shark-resumen',
        'golden_shark_render_resumen',
        'dashicons-chart-area',
        3
    );

    // Submenú: Accesos y Roles (solo administrador clásico)
    add_submenu_page(
        'golden-shark-dashboard',
        __('👥 Accesos y Roles', 'golden-shark'),
        __('👥 Accesos y Roles', 'golden-shark'),
        'manage_options',
        'golden-shark-roles',
        'golden_shark_render_roles'
    );

    // Submenús accesibles para quienes tengan acceso básico
    add_submenu_page(
        'golden-shark-dashboard',
        __('Eventos', 'golden-shark'),
        __('Eventos', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-eventos',
        'golden_shark_render_eventos'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Frases & Mensajes', 'golden-shark'),
        __('Frases & Mensajes', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-frases',
        'golden_shark_render_frases'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Leads', 'golden-shark'),
        __('Leads', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-leads',
        'golden_shark_render_leads'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Tareas Internas', 'golden-shark'),
        __('Tareas', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-tareas',
        'golden_shark_render_tareas'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Notas Internas', 'golden-shark'),
        __('Notas Internas', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-notas',
        'golden_shark_render_notas'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Calendario de Eventos', 'golden-shark'),
        __('Calendario', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-calendar',
        'golden_shark_render_calendar'
    );

    add_submenu_page(
        'golden-shark-dashboard',
        __('Historial de Actividad', 'golden-shark'),
        __('Historial', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-historial',
        'golden_shark_render_historial'
    );

    // Submenú: Configuración (requiere capacidad personalizada)
    add_submenu_page(
        'golden-shark-dashboard',
        __('Configuración', 'golden-shark'),
        __('Configuración', 'golden-shark'),
        'golden_shark_configuracion',
        'golden-shark-config',
        'golden_shark_render_config'
    );

    // Submenú: Logs (requiere capacidad para ver logs)
    add_submenu_page(
        'golden-shark-dashboard',
        __('📜 Logs del sistema', 'golden-shark'),
        __('📜 Logs del sistema', 'golden-shark'),
        'golden_shark_ver_logs',
        'golden-shark-logs',
        'golden_shark_render_logs'
    );

    // Panel Multisitio solo para superadmin en el sitio principal
    if (is_multisite() && is_main_site() && is_super_admin()) {
        add_submenu_page(
            'golden-shark-dashboard',
            __('🌐 Panel Multisitio', 'golden-shark'),
            __('🌐 Panel Multisitio', 'golden-shark'),
            'manage_network_options',
            'golden-shark-multisite-panel',
            'golden_shark_render_multisite_panel'
        );

        add_submenu_page(
            'golden-shark-dashboard',
            __('🌐 Lista de Sitios', 'golden-shark'),
            __('Lista de Sitios', 'golden-shark'),
            'manage_network_options',
            'golden-shark-sites',
            'golden_shark_render_sites_list'
        );

        add_submenu_page(
            'golden-shark',
            __('Historial de sitios', 'golden-shark'),
            __('📜 Historial Sitios', 'golden-shark'),
            'manage_network',
            'gs-historial-sitios',
            'golden_shark_render_historial_sitios'
        );
    }

    add_submenu_page(
        'golden-shark',
        __('Mi perfil', 'golden-shark'),
        __('👤 Mi perfil', 'golden-shark'),
        'read',
        'golden-shark-perfil',
        'golden_shark_render_perfil_usuario'
    );
}
add_action('admin_menu', 'golden_shark_admin_menu');

// 🌐 Renderizar el panel multisitio con pestañas
function golden_shark_render_multisite_panel()
{
    if (!is_super_admin()) {
        wp_die(__('Acceso denegado. Solo el superadministrador puede acceder.', 'golden-shark'));
    }

    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'frases';

    echo '<div class="wrap" role="main">';
    echo '<h1>' . esc_html__('🌐 Panel Multisitio', 'golden-shark') . '</h1>';
    echo '<nav class="nav-tab-wrapper" role="navigation" aria-label="' . esc_attr__('Navegación del panel multisitio', 'golden-shark') . '">';
    echo '<a href="?page=golden-shark-multisite-panel&tab=frases" class="nav-tab ' . ($tab === 'frases' ? 'nav-tab-active' : '') . '">' . esc_html__('Frases Globales', 'golden-shark') . '</a>';
    echo '<a href="?page=golden-shark-multisite-panel&tab=config" class="nav-tab ' . ($tab === 'config' ? 'nav-tab-active' : '') . '">' . esc_html__('Configuración Global', 'golden-shark') . '</a>';
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

// ✅ Cargar los archivos necesarios solo si aplica
if (is_multisite() && is_main_site() && is_super_admin()) {
    require_once plugin_dir_path(__FILE__) . 'frases_globales.php';
    require_once plugin_dir_path(__FILE__) . 'config_global.php';
    require_once plugin_dir_path(__FILE__) . 'sites_list.php';
}