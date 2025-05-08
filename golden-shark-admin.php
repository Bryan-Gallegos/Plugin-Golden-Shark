<?php
/*
Plugin Name: Golden Shark Admin Panel
Description: Plugin de administración interno para gestionar eventos, leads y configuración desde el panel de WordPress.
Version: 1.6
Author: Carlos Gallegos
*/

if (!defined('ABSPATH')) exit;

$archivos = [
    'funciones.php',
    'menu.php',
    'dashboard.php',
    'eventos.php',
    'frases.php',
    'leads.php',
    'config.php',
    'notas.php',
    'historial.php',
    'shortcodes.php'
];

foreach ($archivos as $archivo) {
    require_once plugin_dir_path(__FILE__) . 'includes/' . $archivo;
}