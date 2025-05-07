<?php
if (!defined('ABSPATH')) exit;

// 🧠 PANEL PRINCIPAL
function golden_shark_render_dashboard()
{
    if (!golden_shark_user_can('edit_posts')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }  

    $frases = get_option('golden_shark_frases', []);
    $eventos = get_option('golden_shark_eventos', []);
    $leads = get_option('golden_shark_leads', []);

    $color_dashboard = get_option('golden_shark_color_dashboard', '#0073aa');
    $total_frases = count($frases);
    $total_eventos = count($eventos);
    $total_leads = count($leads);
    $logo_url = plugin_dir_url(__FILE__) . '../assets/logo.png';

    echo '<div class="wrap">';
    echo '<img src="' . esc_url($logo_url) . '" alt="Golden Shark Logo" class="golden-shark-logo">';
    echo '<h1 class="golden-shark-title" style="color:' . esc_attr($color_dashboard) . ';">Golden Shark Admin Panel 🦈</h1>';

    // Frase motivacional
    if (!empty($frases)) {
        $frase = $frases[array_rand($frases)];
        echo '<div class="golden-shark-frase-box" style="border-color:' . esc_attr($color_dashboard) . ';">
                <strong>Frase motivacional del día:</strong><br>' . esc_html($frase) . '
              </div>';
    }

    // Tarjetas de resumen
    echo '<div class="golden-shark-resumen">';

    echo '<div class="golden-shark-resumen-box" style="border-color:#f39c12;">
            <h2>' . $total_frases . '</h2>
            <p>Frases motivacionales</p>
          </div>';
        
    echo '<div class="golden-shark-resumen-box" style="border-color:#2ecc71;">
            <h2>' . $total_eventos . '</h2>
            <p>Eventos internos</p>
          </div>';
    
    echo '<div class="golden-shark-resumen-box" style="border-color:#3498db;">
            <h2>' . $total_leads . '</h2>
            <p>Leads capturados</p>
          </div>';

    echo '</div>'; //cierre tarjetas de resumen
    echo '</div>'; //Cierre div.wrap
}