<?php

if(!defined('ABSPATH')) exit;

// CRON: Borrar frases antiguas 
add_action('gs_cron_borrar_frases_antiguas', 'golden_shark_borrar_frases_antiguas');

function golden_shark_borrar_frases_antiguas(){
    $frases = golden_shark_get_frases();

    $limite = strtotime('-1 year');
    $filtradas = array_filter($frases, function($frase) use ($limite){ 
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\|/', $frase, $matches)){
            return strtotime($matches[1]) >= $limite;
        }

        return true;
    });

    if(count($filtradas) < count($frases)){
        golden_shark_log('Se eliminaron frases antiguas por cron semanal');
        golden_shark_set_frases(array_values($filtradas));
    }
}

add_action('gs_cron_enviar_resumen_diario', 'golden_shark_enviar_resumen_diario');

function golden_shark_enviar_resumen_diario(){
    $leads = get_option('golden_shark_leads', []);
    $eventos = get_option('golden_shark_eventos', []);
    $frases = golden_shark_get_frases();

    $ayer = strtotime('-1 day');
    $leads_recientes = array_filter($leads, fn($l) => strtotime($l['fecha']) >= $ayer);
    $eventos_recientes = array_filter($eventos, fn($e) => strtotime($e['fecha']) >= $ayer);

    $total_leads = count($leads_recientes);
    $total_eventos = count($eventos_recientes);

    $asunto = '📊 Resumen diario – Golden Shark';
    $mensaje = "Resumen del día anterior:\n\n";
    $mensaje .= "🟢 Leads capturados: $total_leads\n";
    $mensaje .= "📅 Eventos registrados: $total_eventos\n";
    $mensaje .= "💬 Frases en total: " . count($frases) . "\n\n";
    $mensaje .= "📍 Sitio: " . get_bloginfo('name') . "\n";
    $mensaje .= "🔗 URL: " . site_url() . "\n";
    $mensaje .= "\nEste correo fue generado automáticamente por el plugin.";

    foreach(get_super_admins() as $admin_user){
        $admin = get_user_by('login', $admin_user);
        if($admin && is_email($admin->user_email)){
            wp_mail($admin->user_email, $asunto, $mensaje);
        }
    }

    golden_shark_log("📧 Resumen diario enviado a los superadmins", 'info');
}

function golden_shark_enviar_informe_mensual() {
    $eventos = get_option('golden_shark_eventos', []);
    $leads = get_option('golden_shark_leads', []);
    $tareas = get_option('golden_shark_tareas', []);

    $mes_actual = date('Y-m');

    $eventos_mes = array_filter($eventos, fn($e) => str_starts_with($e['fecha'], $mes_actual));
    $leads_mes = array_filter($leads, fn($l) => str_starts_with($l['fecha'], $mes_actual));
    $tareas_mes = array_filter($tareas, fn($t) => str_starts_with($t['fecha'], $mes_actual));

    $csv = fopen('php://temp', 'w+');
    fputcsv($csv, ['Categoría', 'Cantidad']);
    fputcsv($csv, ['Eventos', count($eventos_mes)]);
    fputcsv($csv, ['Leads', count($leads_mes)]);
    fputcsv($csv, ['Tareas', count($tareas_mes)]);

    rewind($csv);
    $csv_data = stream_get_contents($csv);
    fclose($csv);

    $upload_dir = wp_upload_dir();
    $path = $upload_dir['basedir'] . '/informe_mensual.csv';
    file_put_contents($path, $csv_data);

    $to = get_option('admin_email');
    $subject = '📈 Informe Mensual - Golden Shark';
    $message = 'Adjunto encontrarás el resumen mensual del panel Golden Shark.';
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $attachments = [$path];

    wp_mail($to, $subject, $message, $headers, $attachments);
    golden_shark_log('Se envió el informe mensual automáticamente.');
}

add_action('gs_cron_informe_mensual', 'golden_shark_enviar_informe_mensual');