<?php
if (!defined('ABSPATH')) exit;

function golden_shark_render_dashboard()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    // Exportar historial usuario
    if (isset($_POST['gs_exportar_historial_usuario']) && check_admin_referer('gs_exportar_historial_usuario_nonce')) {
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

    if (isset($_GET['revisar_lead']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['revisar_lead']);
        $leads = get_option('golden_shark_leads', []);
        if (isset($leads[$id]) && wp_verify_nonce($_GET['_nonce'], 'revisar_lead_' . $id)) {
            $leads[$id]['revisado'] = 'si';
            update_option('golden_shark_leads', $leads);
            golden_shark_log('Se marcó un lead como revisado.');
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Lead marcado como revisado.');
            wp_redirect(admin_url('admin.php?page=golden-shark-dashboard'));
            exit;
        }
    }

    $frases = golden_shark_get_frases();
    $eventos = get_option('golden_shark_eventos', []);
    $leads = get_option('golden_shark_leads', []);
    $hoy = date('Y-m-d');
    $eventos_hoy = array_filter($eventos, fn($e) => isset($e['fecha']) && $e['fecha'] === $hoy);
    $leads_sin_revisar = array_filter($leads, fn($l) => empty($l['revisado']) || $l['revisado'] === 'no');
    $limite_eventos = intval(golden_shark_get_config('golden_shark_alerta_eventos_dia', 5));
    $limite_leads = intval(golden_shark_get_config('golden_shark_alerta_leads_pendientes', 5));

    // Notificaciones
    $notificacion = get_user_meta(get_current_user_id(), 'gs_notificacion_interna', true);
    if (!empty($notificacion)) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notificacion) . '</p></div>';
        delete_user_meta(get_current_user_id(), 'gs_notificacion_interna');
    }

    echo '<div class="wrap gs-container">';

    echo '<h1 style="margin-bottom:20px;">Golden Shark Admin Panel 🦈</h1>';

    // Alertas
    if (count($eventos_hoy) > $limite_eventos) {
        echo '<div class="notice notice-error"><p>' . sprintf(__('⚠️ Atención: hay más de %s eventos programados para hoy.', 'golden-shark'), $limite_eventos) . '</p></div>';
    }

    if (count($leads_sin_revisar) > $limite_leads) {
        echo '<div class="notice notice-warning"><p>' . sprintf(__('🔔 Atención: hay más de %s leads sin revisar.', 'golden-shark'), $limite_leads) . '</p></div>';
    }

    // Frase motivacional
    if (!empty($frases)) {
        $frase = $frases[array_rand($frases)];
        echo '<div class="gs-container" style="border-left: 4px solid #0073aa; margin-top: 20px;">';
        echo '<strong>' . __('Frase motivacional del día:', 'golden-shark') . '</strong><br>' . esc_html($frase);
        echo '</div>';
    }

    // Tarjetas resumen visibles según rol
    echo '<div class="gs-dashboard-resumen">';
    echo '<div class="gs-card"><h3>' . __('💬 Frases', 'golden-shark') . '</h3><p class="gs-big-number">' . count($frases) . '</p></div>';

    if (current_user_can('golden_shark_configuracion')) {
        echo '<div class="gs-card"><h3>' . __('📅 Eventos', 'golden-shark') . '</h3><p class="gs-big-number">' . count($eventos) . '</p></div>';
    }

    if (current_user_can('golden_shark_acceso_basico')) {
        echo '<div class="gs-card"><h3>' . __('📨 Leads', 'golden-shark') . '</h3><p class="gs-big-number">' . count($leads) . '</p></div>';
    }

    echo '</div>';


    // Resumen gráfico
    echo '<div class="gs-container" style="margin-top:30px;">';
    echo '<h3>' . __('Resumen gráfico', 'golden-shark') . '</h3>';
    echo '<canvas id="goldenSharkChart" width="400" height="150" style="max-width:600px;"></canvas>';
    echo '</div>';

    // Leads sin revisar
    if (!empty($leads_sin_revisar)) {
        echo '<div class="gs-container">';
        echo '<h3>' . __('Leads sin revisar', 'golden-shark') . '</h3>';
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . __('Nombre', 'golden-shark') . '</th>';
        echo '<th>' . __('Email', 'golden-shark') . '</th>';
        echo '<th>' . __('Fecha', 'golden-shark') . '</th>';
        echo '<th>' . __('Acción', 'golden-shark') . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($leads_sin_revisar as $index => $lead) {
            echo '<tr>';
            echo '<td>' . esc_html($lead['nombre'] ?? '-') . '</td>';
            echo '<td>' . esc_html($lead['email'] ?? '-') . '</td>';
            echo '<td>' . esc_html($lead['fecha'] ?? '-') . '</td>';
            echo '<td><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=golden-shark-dashboard&revisar_lead=' . $index), 'revisar_lead_' . $index)) . '">' . __('Marcar como revisado', 'golden-shark') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    // Historial usuario
    $historial_usuario = get_user_meta(get_current_user_id(), 'gs_historial_usuario', true);
    if (!empty($historial_usuario)) {
        echo '<div class="gs-container">';
        echo '<h3>' . __('Actividad reciente', 'golden-shark') . '</h3>';
        echo '<ul>';
        foreach (array_slice(array_reverse($historial_usuario), 0, 5) as $entrada) {
            echo '<li><strong>' . esc_html($entrada['fecha']) . '</strong>: ' . esc_html($entrada['accion'] ?? $entrada['mensaje']) . '</li>';
        }
        echo '</ul>';
        echo '<form method="post" style="margin-top:10px;">';
        wp_nonce_field('gs_exportar_historial_usuario_nonce');
        echo '<input type="hidden" name="gs_exportar_historial_usuario" value="1">';
        echo '<input type="submit" class="button button-secondary" value="' . esc_attr__('📤 Exportar historial en CSV', 'golden-shark') . '">';
        echo '</form>';
        echo '</div>';
    }

    echo '</div>'; // cierre wrap
}
