<?php
if (!defined('ABSPATH')) exit;

// 🧠 PANEL PRINCIPAL
function golden_shark_render_dashboard()
{
  if (!golden_shark_user_can('edit_posts')) {
    wp_die('No tienes permiso para acceder a esta sección.');
  }

  // 🗂 Exportar historial individual del usuario (con verificación de nonce)
  if (
    isset($_POST['gs_exportar_historial_usuario']) &&
    isset($_POST['_wpnonce']) &&
    wp_verify_nonce($_POST['_wpnonce'], 'gs_exportar_historial_usuario_nonce')
  ) {
    $usuario_historial = get_user_meta(get_current_user_id(), 'gs_historial_usuario', true);
    if (!empty($usuario_historial)) {
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="mi_historial_golden_shark.csv"');
      $out = fopen('php://output', 'w');
      fputcsv($out, ['Fecha', 'Acción']);
      foreach ($usuario_historial as $item) {
        fputcsv($out, [$item['fecha'], $item['accion'] ?? $item['mensaje']]);
      }
      fclose($out);
      exit;
    }
  }

  // ✅ Mostrar notificación temporal si existe
  $notificacion = get_user_meta(get_current_user_id(), 'gs_notificacion_interna', true);
  if (!empty($notificacion)) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notificacion) . '</p></div>';
    delete_user_meta(get_current_user_id(), 'gs_notificacion_interna');
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

  echo '<h3 style="margin-top:40px;">Resumen gráfico:</h3>';
  echo '<canvas id="goldenSharkChart" width="400" height="150" style="max-width:600px;"></canvas>';

  // Historial del usuario
  $historial_usuario = get_user_meta(get_current_user_id(), 'gs_historial_usuario', true);
  if (!empty($historial_usuario)) {
    echo '<h3 style="margin-top:40px;">Tu actividad reciente:</h3>';
    echo '<ul style="list-style: disc; margin-left: 20px;">';
    $ultimos = array_slice(array_reverse($historial_usuario), 0, 5);
    foreach ($ultimos as $entrada) {
      echo '<li><strong>' . esc_html($entrada['fecha']) . '</strong>: ' . esc_html($entrada['accion']) . '</li>';
    }
    echo '</ul>';

    // Botón de exportar historial del usuario
    echo '<form method="post" style="margin-top:20px;">';
    wp_nonce_field('gs_exportar_historial_usuario_nonce');
    echo '<input type="hidden" name="gs_exportar_historial_usuario" value="1">';
    echo '<input type="submit" class="button button-secondary" value="📤 Exportar mi historial en CSV">';
    echo '</form>';
  }

  echo '</div>'; // cierre tarjetas de resumen
  echo '</div>'; // cierre div.wrap
}
