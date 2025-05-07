<?php
if (!defined('ABSPATH')) exit;

// Shortcode para mostrar una frase motivacional aleatoria
function golden_shark_shortcode_frase()
{
    $frases = get_option('golden_shark_frases', []);
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
            $mensaje = '<div style="background:#dff0d8;padding:10px;border-left:4px solid #3c763d;margin-bottom:15px;">✅ ¡Gracias por registrarte!</div>';
        } else {
            $mensaje = '<div style="background:#f2dede;padding:10px;border-left:4px solid #a94442;margin-bottom:15px;">❌ Por favor completa los campos obligatorios.</div>';
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
