<?php
if (!defined('ABSPATH')) exit;

// Shortcode para mostrar una frase motivacional aleatoria
function golden_shark_shortcode_frase()
{
    $frases = golden_shark_get_frases();
    if (!empty($frases)) {
        $frase = $frases[array_rand($frases)];
        return '<div class="frase-motivacional" style="font-style: italic; padding: 10px; border-left: 4px solid #f39c12; background: #fdf8ec;">' . esc_html($frase) . '</div>';
    } else {
        return '<div class="frase-motivacional">No hay frases registradas aún.</div>';
    }
}
add_shortcode('frase_motivacional', 'golden_shark_shortcode_frase');

// Shortcode para formulario público de leads
function golden_shark_formulario_lead_shortcode()
{
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gs_lead_submit'])) {
        if (!isset($_POST['gs_form_nonce_field']) || !wp_verify_nonce($_POST['gs_form_nonce_field'], 'gs_formulario_lead_nonce')) {
            $mensaje = '<div style="background:#f2dede;padding:10px;border-left:4px solid #a94442;margin-bottom:15px;">⚠️ Error de seguridad. Recarga la página.</div>';
        } else {
            $nombre = sanitize_text_field($_POST['gs_lead_nombre']);
            $correo = sanitize_email($_POST['gs_lead_correo']);
            $mensaje_input = sanitize_textarea_field($_POST['gs_lead_mensaje']);
            $fecha = current_time('Y-m-d H:i:s');

            if ($nombre && $correo) {
                $leads = get_option('golden_shark_leads', []);
                $leads[] = [
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'mensaje' => $mensaje_input,
                    'fecha' => $fecha
                ];
                update_option('golden_shark_leads', $leads);
            
                // 🔁 Crear tarea automáticamente
                if (function_exists('golden_shark_crear_tareas_automaticas')) {
                    golden_shark_crear_tareas_automaticas([
                        'tipo' => 'lead',
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'fecha' => $fecha
                    ]);
                }
            
                $mensaje = '<div style="background:#dff0d8;padding:10px;border-left:4px solid #3c763d;margin-bottom:15px;">✅ ¡Gracias por registrarte!</div>';
            }
             else {
                $mensaje = '<div style="background:#f2dede;padding:10px;border-left:4px solid #a94442;margin-bottom:15px;">❌ Por favor completa los campos obligatorios.</div>';
            }
        }
    }

    ob_start();
    echo $mensaje;
?>
    <form method="post">
        <p><label>Nombre*:<br><input type="text" name="gs_lead_nombre" required style="width:100%;"></label></p>
        <p><label>Correo electrónico*:<br><input type="email" name="gs_lead_correo" required style="width:100%;"></label></p>
        <p><label>Mensaje (opcional):<br><textarea name="gs_lead_mensaje" rows="4" style="width:100%;"></textarea></label></p>
        <p><input type="submit" name="gs_lead_submit" value="Enviar" style="background:#0073aa; color:#fff; padding:8px 20px; border:none;"></p>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('formulario_lead', 'golden_shark_formulario_lead_shortcode');

//Shortcode para el listado de los eventos 
function golden_shark_shortcode_lista_eventos()
{
    $eventos = get_option('golden_shark_eventos', []);
    if (empty($eventos)) {
        return '<p>No hay eventos disponibles en este momento.</p>';
    }

    ob_start();
    echo '<ul class="gs-eventos-lista">';
    foreach ($eventos as $evento) {
        echo '<li><strong>' . esc_html($evento['titulo']) . '</strong> – ' . esc_html($evento['fecha']) . ' en ' . esc_html($evento['lugar']) . '</li>';
    }
    echo '</ul>';

    return ob_get_clean();
}
add_shortcode('lista_eventos', 'golden_shark_shortcode_lista_eventos');

//Shortcode para el listado de una nota aleatoria 
function golden_shark_shortcode_nota_aleatoria()
{
    $notas = get_option('golden_shark_notas', []);
    $mostrar = get_option('golden_shark_habilitar_notificaciones', '1');

    if ($mostrar !== '1' || !is_array($notas) || empty($notas)) return '';

    $nota = $notas[array_rand($notas)]; // ✅ Esto es lo que faltaba
    return '<div class="gs-nota-aleatoria" style="background:#fefefe;border-left:4px solid #666;padding:10px;margin-top:10px;"><strong>Nota interna:</strong><br>' . nl2br(esc_html($nota['contenido'])) . '</div>';
}


//Shortcode para el total de los leads capturados como métrica
function golden_shark_shortcode_total_leads()
{
    $leads = get_option('golden_shark_leads', []);
    return '<p>Total de leads registrados: <strong>' . count($leads) . '</strong></p>';
}
add_shortcode('total_leads', 'golden_shark_shortcode_total_leads');

//Shortcode para mostrar el historial personal del usuario conectado
function golden_shark_shortcode_mi_historial()
{
    if(!is_user_logged_in()){
        return '<p>🔒 Debes iniciar sesión para ver tu historial</p>';
    }

    $user_id = get_current_user_id();
    $historial = get_user_meta($user_id, 'gs_historial_usuario', true);

    if(!is_array($historial) || empty($historial)){
        return '<p>No tienes historial registrado aún.</p>';
    }

    //Ordernar del más reciente al más antiguo
    $historial = array_reverse($historial);

    ob_start();
    echo '<ul class="gs-mi-historial" style="margin-left: 20px;">';
    foreach($historial as $item){
        echo '<li><strong>' . esc_html($item['fecha']) . '</strong>: ' . esc_html($item['mensaje']) . '</li>';
    }
    echo '</ul>';
    return ob_get_clean();
}

add_shortcode('mi_historial', 'golden_shark_shortcode_mi_historial');