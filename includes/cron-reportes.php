<?php
if (!defined('ABSPATH')) exit;

// Tarea programada para enviar el informe semanal
add_action('gs_cron_reporte_semanal', 'golden_shark_enviar_reporte_semanal');

function golden_shark_enviar_reporte_semanal() {
    $admin_email = get_option('admin_email');
    $csv = golden_shark_generar_csv_reporte();

    $asunto = '📊 Reporte semanal - Golden Shark';
    $mensaje = 'Adjunto encontrarás el reporte semanal con leads, eventos y accesos.';
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Guardar temporalmente el archivo
    $upload_dir = wp_upload_dir();
    $ruta = $upload_dir['basedir'] . '/reporte-golden-shark.csv';
    file_put_contents($ruta, $csv);

    // Enviar correo (corregido: wp_mail en lugar de wp_email)
    wp_mail($admin_email, $asunto, $mensaje, $headers, [$ruta]);

    // Eliminar archivo tras enviar
    unlink($ruta);

    golden_shark_log('📧 Reporte semanal enviado al administrador');
}

// Generar contenido CSV
function golden_shark_generar_csv_reporte() {
    $csv = "Tipo,Fecha,Usuario,IP,Detalle\n";

    // Leads
    $leads = get_option('golden_shark_leads', []);
    foreach ($leads as $lead) {
        $csv .= "Lead," . ($lead['fecha'] ?? '') . "," . ($lead['nombre'] ?? '') . ",," . ($lead['correo'] ?? '') . "\n";
    }

    // Eventos
    $eventos = get_option('golden_shark_eventos', []);
    foreach ($eventos as $evento) {
        $csv .= "Evento," . ($evento['fecha'] ?? '') . "," . ($evento['responsable'] ?? '') . ",," . ($evento['titulo'] ?? '') . "\n";
    }

    // Accesos
    $accesos = get_option('gs_log_accesos', []);
    foreach ($accesos as $log) {
        $csv .= ucfirst($log['tipo']) . "," . ($log['fecha'] ?? '') . "," . ($log['usuario'] ?? '') . "," . ($log['ip'] ?? '') . "," . ($log['navegador'] ?? '') . "\n";
    }

    return $csv;
}
