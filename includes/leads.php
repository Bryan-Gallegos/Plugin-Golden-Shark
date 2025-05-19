<?php
if (!defined('ABSPATH')) exit;

// 📥 LEADS
function golden_shark_render_leads()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die('No tienes permiso para acceder a esta sección.');
    }

    $leads = get_option('golden_shark_leads', []);

    // Guardar nuevo lead
    if (isset($_POST['nuevo_lead'])) {
        if (!isset($_POST['lead_nonce']) || !wp_verify_nonce($_POST['lead_nonce'], 'guardar_lead_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $nombre = sanitize_text_field($_POST['lead_nombre']);
        $correo = sanitize_email($_POST['lead_correo']);
        $mensaje = sanitize_textarea_field($_POST['lead_mensaje']);
        $fecha = current_time('Y-m-d H:i:s');

        if ($nombre && $correo) {
            $leads[] = [
                'nombre' => $nombre,
                'correo' => $correo,
                'mensaje' => $mensaje,
                'fecha' => $fecha
            ];
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se registró un nuevo lead: $nombre ($correo)");
            //Enviar a webhook si esta configurado
            $webhook_url = get_option('golden_shark_webhook_leads_url', '');
            if (!empty($webhook_url) && filter_var($webhook_url, FILTER_VALIDATE_URL)) {
                wo_remote_post($webhook_url, [
                    'method'    => 'POST',
                    'timeout'   => 10,
                    'headers'   => ['Content-Type' => 'application/json'],
                    'body'      => json_encode([
                        'nombre'    => $nombre,
                        'correo'    => $correo,
                        'mensaje'   => $mensaje,
                        'fecha'     => $fecha,
                        'sitio'     => get_bloginfo('name'),
                        'url'       => get_site_url(),
                    ]),
                ]);

                golden_shark_log("📡 Webhook enviado tras nuevo lead ($correo)");
            }
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Lead guardado correctamente.');
            echo '<div class="notice notice-success"><p>✅ Lead guardado correctamente.</p></div>';
        }
    }

    // Eliminar lead
    if (isset($_GET['eliminar_lead']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_lead']);
        $nonce = $_GET['_nonce'];

        if (!wp_verify_nonce($nonce, 'eliminar_lead_' . $id)) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        if (isset($leads[$id])) {
            $lead_eliminado = $leads[$id];
            unset($leads[$id]);
            $leads = array_values($leads);
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se eliminó el lead: {$lead_eliminado['nombre']} ({$lead_eliminado['correo']})");
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '🗑️ Lead eliminado correctamente.');
            echo '<div class="notice notice-error"><p>🗑️ Lead eliminado.</p></div>';
        }
    }

    // Editar lead
    if (isset($_POST['editar_lead_guardado'])) {
        if (!isset($_POST['editar_lead_nonce']) || !wp_verify_nonce($_POST['editar_lead_nonce'], 'guardar_edicion_lead_nonce')) {
            wp_die('⚠️ Seguridad fallida. Token inválido.');
        }

        $id = intval($_POST['lead_id']);
        if (isset($leads[$id])) {
            $leads[$id] = [
                'nombre' => sanitize_text_field($_POST['lead_nombre']),
                'correo' => sanitize_email($_POST['lead_correo']),
                'mensaje' => sanitize_textarea_field($_POST['lead_mensaje']),
                'fecha' => $leads[$id]['fecha']
            ];
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se editó el lead: {$_POST['lead_nombre']} ({$_POST['lead_correo']})");
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', '✅ Lead actualizado correctamente.');
            echo '<div class="notice notice-info"><p>✏️ Lead actualizado correctamente.</p></div>';
        }
    }

    // Exportar leads a CSV
    if (isset($_POST['exportar_leads'])) {
        $correo_filtro = sanitize_text_field($_POST['filtro_correo'] ?? '');

        $leads_filtrados = array_filter($leads, function ($lead) use ($correo_filtro) {
            if (empty($correo_filtro)) return true;
            return stripos($lead['correo'], $correo_filtro) !== false;
        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nombre', 'Correo', 'Mensaje', 'Fecha']);
        foreach ($leads_filtrados as $lead) {
            fputcsv($output, [$lead['nombre'], $lead['correo'], $lead['mensaje'], $lead['fecha']]);
        }
        fclose($output);
        golden_shark_log('Se exportaron los leads filtrados a CSV.');
        exit;
    }

?>
    <div class="wrap" id="top">
        <h2>📨 Leads Capturados</h2>

        <div class="gs-container">
            <?php if (isset($_GET['editar_lead'])):
                $id = intval($_GET['editar_lead']);
                if (isset($leads[$id])): $lead = $leads[$id]; ?>
                    <h3>✏️ Editar Lead</h3>
                    <form method="post">
                        <input type="hidden" name="editar_lead_guardado" value="1">
                        <input type="hidden" name="lead_id" value="<?php echo $id; ?>">
                        <?php wp_nonce_field('guardar_edicion_lead_nonce', 'editar_lead_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="lead_nombre">Nombre:</label></th>
                                <td><input type="text" name="lead_nombre" value="<?php echo esc_attr($lead['nombre']); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lead_correo">Correo:</label></th>
                                <td><input type="email" name="lead_correo" value="<?php echo esc_attr($lead['correo']); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lead_mensaje">Mensaje:</label></th>
                                <td><textarea name="lead_mensaje" rows="3"><?php echo esc_textarea($lead['mensaje']); ?></textarea></td>
                            </tr>
                        </table>
                        <p><input type="submit" class="button button-primary" value="Guardar cambios"></p>
                    </form>
                    <hr>
            <?php endif;
            endif; ?>
        </div>

        <div class="gs-container">
            <h3>➕ Nuevo Lead</h3>
            <form method="post">
                <input type="hidden" name="nuevo_lead" value="1">
                <?php wp_nonce_field('guardar_lead_nonce', 'lead_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="lead_nombre">Nombre:</label></th>
                        <td><input type="text" name="lead_nombre" required></td>
                    </tr>
                    <tr>
                        <th><label for="lead_correo">Correo:</label></th>
                        <td><input type="email" name="lead_correo" required></td>
                    </tr>
                    <tr>
                        <th><label for="lead_mensaje">Mensaje:</label></th>
                        <td><textarea name="lead_mensaje" rows="3"></textarea></td>
                    </tr>
                </table>
                <p><input type="submit" class="button button-primary" value="Guardar lead"></p>
            </form>
            <form method="post" style="margin-top: 15px;">
                <input type="hidden" name="exportar_leads" value="1">
                <input type="text" name="filtro_correo" placeholder="Filtrar por correo..." value="<?php echo esc_attr($_GET['correo'] ?? ''); ?>">
                <input type="submit" class="button button-secondary" value="📤 Exportar leads filtrados">
            </form>
        </div>

        <div class="gs-container">
            <form method="get" style="margin-bottom: 15px;">
                <input type="hidden" name="page" value="golden-shark-leads">
                <label for="filtro_correo"><strong>Filtrar por correo:</strong></label>
                <input type="text" name="correo" id="filtro_correo" placeholder="ej. gmail.com" value="<?php echo esc_attr($_GET['correo'] ?? ''); ?>">
                <input type="submit" class="button" value="Filtrar">
            </form>

            <h3>📋 Leads Registrados:</h3>
            <?php
            $correo_filtrado = $_GET['correo'] ?? '';

            $leads_filtrados = array_filter($leads, function ($lead) use ($correo_filtrado) {
                if ($correo_filtrado === '') return true;
                return stripos($lead['correo'], $correo_filtrado) !== false;
            });
            ?>

            <?php if (empty($leads_filtrados)) : ?>
                <p>No hay leads registrados con ese filtro.</p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads_filtrados as $i => $lead): ?>
                            <tr>
                                <td><?php echo esc_html($lead['nombre']); ?></td>
                                <td><?php echo esc_html($lead['correo']); ?></td>
                                <td><?php echo esc_html($lead['mensaje']); ?></td>
                                <td><?php echo esc_html($lead['fecha']); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=golden-shark-leads&editar_lead=' . $i); ?>">Editar</a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-leads&eliminar_lead=' . $i), 'eliminar_lead_' . $i, '_nonce'); ?>" onclick="return confirm('¿Eliminar este lead?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <a href="#top" class="gs-go-top" title="Volver al inicio">⬆️</a>
    </div>
<?php
}
