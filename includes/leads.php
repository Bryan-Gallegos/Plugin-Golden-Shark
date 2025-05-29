<?php
if (!defined('ABSPATH')) exit;

// 📥 LEADS
function golden_shark_render_leads()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $leads = get_option('golden_shark_leads', []);

    // Eliminar leads antiguos
    if (isset($_POST['eliminar_leads_antiguos']) && current_user_can('golden_shark_configuracion')) {
        $hoy = date('Y-m-d');
        $leads = array_filter($leads, function ($lead) use ($hoy) {
            return substr($lead['fecha'], 0, 10) >= $hoy;
        });

        update_option('golden_shark_leads', array_values($leads));
        golden_shark_log('Se eliminaron leads antiguos');
        golden_shark_log_usuario('Uso el botón "Eliminar leads antiguos".');
        echo '<div class="notice notice-warning"><p>' . __('🧹 Leads anteriores a hoy eliminados correctamente.', 'golden-shark') . '</p></div>';
    }

    // Guardar nuevo lead
    if (isset($_POST['nuevo_lead'])) {
        if (!isset($_POST['lead_nonce']) || !wp_verify_nonce($_POST['lead_nonce'], 'guardar_lead_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
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
                'fecha' => $fecha,
                'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['lead_etiquetas']))),
            ];
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se registró un nuevo lead: $nombre ($correo)");
            // Enviar a webhook si está configurado
            $webhook_url = get_option('golden_shark_webhook_leads_url', '');
            $campos = get_option('golden_shark_webhook_campos_leads', ['nombre', 'correo', 'mensaje']);

            if (!empty($webhook_url) && filter_var($webhook_url, FILTER_VALIDATE_URL)) {
                $payload = [];

                foreach ($campos as $campo) {
                    switch ($campo) {
                        case 'nombre':
                            $payload['nombre'] = $nombre;
                            break;
                        case 'correo':
                            $payload['correo'] = $correo;
                            break;
                        case 'mensaje':
                            $payload['mensaje'] = $mensaje;
                            break;
                        case 'fecha':
                            $payload['fecha'] = $fecha;
                            break;
                        case 'etiquetas':
                            $payload['etiquetas'] = array_map('trim', explode(',', sanitize_text_field($_POST['lead_etiquetas'])));
                            break;
                    }
                }

                // Campos adicionales fijos
                $payload['sitio'] = get_bloginfo('name');
                $payload['url'] = get_site_url();

                wo_remote_post($webhook_url, [
                    'method'  => 'POST',
                    'timeout' => 10,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => json_encode($payload),
                ]);

                golden_shark_log("📡 Webhook personalizado enviado para lead ($correo)");
            }

            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('✅ Lead guardado correctamente.', 'golden-shark'));
            echo '<div class="notice notice-success"><p>' . __('✅ Lead guardado correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Eliminar lead
    if (isset($_GET['eliminar_lead']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_lead']);
        $nonce = $_GET['_nonce'];

        if (!wp_verify_nonce($nonce, 'eliminar_lead_' . $id)) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.'));
        }

        if (isset($leads[$id])) {
            $lead_eliminado = $leads[$id];
            unset($leads[$id]);
            $leads = array_values($leads);
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se eliminó el lead: {$lead_eliminado['nombre']} ({$lead_eliminado['correo']})");
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('🗑️ Lead eliminado correctamente.', 'golden-shark'));
            echo '<div class="notice notice-error"><p>' . __('🗑️ Lead eliminado.', 'golden-shark') . '</p></div>';
        }
    }

    // Editar lead
    if (isset($_POST['editar_lead_guardado'])) {
        if (!isset($_POST['editar_lead_nonce']) || !wp_verify_nonce($_POST['editar_lead_nonce'], 'guardar_edicion_lead_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.'));
        }

        $id = intval($_POST['lead_id']);
        if (isset($leads[$id])) {
            $leads[$id] = [
                'nombre' => sanitize_text_field($_POST['lead_nombre']),
                'correo' => sanitize_email($_POST['lead_correo']),
                'mensaje' => sanitize_textarea_field($_POST['lead_mensaje']),
                'fecha' => $leads[$id]['fecha'],
                'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['lead_etiquetas']))),
            ];
            update_option('golden_shark_leads', $leads);
            golden_shark_log("Se editó el lead: {$_POST['lead_nombre']} ({$_POST['lead_correo']})");
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('✅ Lead actualizado correctamente.', 'golden-shark'));
            echo '<div class="notice notice-info"><p>' . __('✏️ Lead actualizado correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Exportar leads a CSV
    if (isset($_POST['exportar_leads'])) {
        $correo_filtro = sanitize_text_field($_POST['filtro_correo'] ?? '');
        $etiqueta_filtro = strtolower(trim(sanitize_text_field($_POST['filtro_etiqueta'] ?? '')));
        $fecha_inicio = sanitize_text_field($_POST['filtro_fecha_inicio'] ?? '');
        $fecha_fin = sanitize_text_field($_POST['filtro_fecha_fin'] ?? '');

        $leads_filtrados = array_filter($leads, function ($lead) use ($correo_filtro, $etiqueta_filtro, $fecha_inicio, $fecha_fin) {
            if ($correo_filtro && stripos($lead['correo'], $correo_filtro) === false) return false;

            if ($etiqueta_filtro) {
                $etiquetas = array_map('strtolower', $lead['etiquetas'] ?? []);
                if (!in_array($etiqueta_filtro, $etiquetas)) return false;
            }

            if ($fecha_inicio && $lead['fecha'] < $fecha_inicio) return false;
            if ($fecha_fin && $lead['fecha'] > $fecha_fin) return false;

            return true;
        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nombre', 'Correo', 'Mensaje', 'Fecha', 'Etiquetas']);
        foreach ($leads_filtrados as $lead) {
            fputcsv($output, [
                $lead['nombre'],
                $lead['correo'],
                $lead['mensaje'],
                $lead['fecha'],
                implode(', ', $lead['etiquetas'] ?? [])
            ]);
        }
        fclose($output);
        golden_shark_log('Se exportaron los leads filtrados a CSV.');
        exit;
    }

    // Exportación avanzada de leads
    if (isset($_POST['exportar_leads_avanzado']) && isset($_POST['columnas'])) {
        $columnas = array_map('sanitize_text_field', $_POST['columnas']);
        $fecha_inicio = sanitize_text_field($_POST['fecha_inicio'] ?? '');
        $fecha_fin = sanitize_text_field($_POST['fecha_fin'] ?? '');

        $leads_filtrados = array_filter($leads, function ($lead) use ($fecha_inicio, $fecha_fin) {
            if ($fecha_inicio && $lead['fecha'] < $fecha_inicio) return false;
            if ($fecha_fin && $lead['fecha'] > $fecha_fin) return false;
            return true;
        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment, filename="leads_exportacion_avanzada.csv"');
        $output = fopen('php://output', 'w');

        // Escribir encabezados
        fputcsv($output, $columnas);

        foreach ($leads_filtrados as $lead) {
            $fila = [];
            foreach ($columnas as $col) {
                $valor = $lead[$col] ?? '';
                if (is_array($valor)) {
                    $valor = implode(', ', $valor);
                }
                $fila[] = $valor;
            }
            fputcsv($output, $fila);
        }

        fclose($output);
        golden_shark_log('📤 Exportación avanzada de leads realizada.');
        exit;
    }

?>
    <div class="wrap" id="top">
        <h2><?php _e('📨 Leads Capturados', 'golden-shark'); ?></h2>

        <div class="gs-container">
            <?php if (isset($_GET['editar_lead'])):
                $id = intval($_GET['editar_lead']);
                if (isset($leads[$id])): $lead = $leads[$id]; ?>
                    <h3><?php _e('Editar Lead', 'golden-shark'); ?></h3>
                    <form method="post">
                        <input type="hidden" name="editar_lead_guardado" value="1">
                        <input type="hidden" name="lead_id" value="<?php echo $id; ?>">
                        <?php wp_nonce_field('guardar_edicion_lead_nonce', 'editar_lead_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="lead_nombre"><?php _e('Nombre:', 'golden-shark'); ?></label></th>
                                <td><input type="text" name="lead_nombre" value="<?php echo esc_attr($lead['nombre']); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lead_correo"><?php _e('Correo:', 'golden-shark'); ?></label></th>
                                <td><input type="email" name="lead_correo" value="<?php echo esc_attr($lead['correo']); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="lead_mensaje"><?php _e('Mensaje:', 'golden-shark'); ?></label></th>
                                <td><textarea name="lead_mensaje" rows="3"><?php echo esc_textarea($lead['mensaje']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="lead_etiquetas"><?php _e('Etiquetas:', 'golden-shark'); ?></label></th>
                                <td><input type="text" name="lead_etiquetas" value="<?php echo esc_attr(implode(', ', $lead['etiquetas'] ?? [])); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                        <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar cambios', 'golden-shark'); ?>"></p>
                    </form>
                    <hr>
            <?php endif;
            endif; ?>
        </div>

        <div class="gs-container">
            <h3><?php _e('➕ Nuevo Lead', 'golden-shark'); ?></h3>
            <form method="post">
                <input type="hidden" name="nuevo_lead" value="1">
                <?php wp_nonce_field('guardar_lead_nonce', 'lead_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="lead_nombre"><?php _e('Nombre:', 'golden-shark'); ?></label></th>
                        <td><input type="text" name="lead_nombre" required></td>
                    </tr>
                    <tr>
                        <th><label for="lead_correo"><?php _e('Correo:', 'golden-shark'); ?></label></th>
                        <td><input type="email" name="lead_correo" required></td>
                    </tr>
                    <tr>
                        <th><label for="lead_mensaje"><?php _e('Mensaje:', 'golden-shark'); ?></label></th>
                        <td><textarea name="lead_mensaje" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="lead_etiquetas"><?php _e('Etiquetas:', 'golden-shark'); ?></label></th>
                        <td><input type="text" name="lead_etiquetas" placeholder="Ej: urgente, cliente, evento" class="regular-text"></td>
                    </tr>
                </table>
                <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar lead', 'golden-shark'); ?>"></p>
            </form>
            <form method="post" style="margin-top: 15px;">
                <input type="hidden" name="exportar_leads" value="1">
                <input type="text" name="filtro_correo" placeholder="Correo..." value="<?php echo esc_attr($_GET['correo'] ?? ''); ?>">
                <input type="text" name="filtro_etiqueta" placeholder="Etiqueta..." value="<?php echo esc_attr($_GET['etiqueta'] ?? ''); ?>">
                <input type="date" name="filtro_fecha_inicio" value="<?php echo esc_attr($_GET['fecha_inicio'] ?? ''); ?>">
                <input type="date" name="filtro_fecha_fin" value="<?php echo esc_attr($_GET['fecha_fin'] ?? ''); ?>">
                <input type="submit" class="button button-secondary" value="<?php esc_attr_e('📤 Exportar leads filtrados', 'golden-shark'); ?>">
            </form>
            <form method="post" style="margin-top: 10px; background: #f9f9f9; padding: 15px; border: 1px solid #ccc;">
                <input type="hidden" name="exportar_leads_avanzado" value="1">
                <strong><?php _e('Exportación avanzada:', 'golden-shark'); ?></strong><br><br>

                <label><strong><?php _e('Columnas a incluir:', 'golden-shark'); ?></strong></label><br>
                <label><input type="checkbox" name="columnas[]" value="nombre" checked> Nombre</label>
                <label><input type="checkbox" name="columnas[]" value="correo" checked> Correo</label>
                <label><input type="checkbox" name="columnas[]" value="mensaje"> Mensaje</label>
                <label><input type="checkbox" name="columnas[]" value="fecha" checked> Fecha</label>
                <label><input type="checkbox" name="columnas[]" value="etiquetas"> Etiquetas</label>

                <br><br>
                <label><strong><?php _e('Rango de fechas:', 'golden-shark'); ?></strong></label><br>
                <input type="date" name="fecha_inicio">
                <input type="date" name="fecha_fin">

                <br><br>
                <input type="submit" value="">
            </form>
        </div>

        <div class="gs-container">
            <form method="get" style="margin-bottom: 20px;" class="gs-filtros-leads">
                <input type="hidden" name="page" value="golden-shark-leads">

                <label for="filtro_correo"><strong>Correo:</strong></label>
                <input type="text" name="correo" id="filtro_correo" placeholder="ej. gmail.com" value="<?php echo esc_attr($_GET['correo'] ?? ''); ?>">

                <label for="filtro_etiqueta"><strong>Etiqueta:</strong></label>
                <input type="text" name="etiqueta" id="filtro_etiqueta" placeholder="cliente, evento, vip..." value="<?php echo esc_attr($_GET['etiqueta'] ?? ''); ?>">

                <label for="fecha_inicio"><strong>Desde:</strong></label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo esc_attr($_GET['fecha_inicio'] ?? ''); ?>">

                <label for="fecha_fin"><strong>Hasta:</strong></label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo esc_attr($_GET['fecha_fin'] ?? ''); ?>">

                <input type="submit" class="button" value="Filtrar Leads">
            </form>

            <h3><?php _e('📋 Leads Registrados:', 'golden-shark'); ?></h3>
            <?php if (current_user_can('golden_shark_configuracion')): ?>
                <form method="post" style="margin-bottom: 20px;">
                    <input type="hidden" name="eliminar_leads_antiguos" value="1">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('¿Eliminar todos los leads con fecha pasada?', 'golden-shark')); ?>')">
                        <?php _e('🧹 Eliminar leads antiguos', 'golden-shark'); ?>
                    </button>
                </form>
            <?php endif; ?>
            <?php
            $correo_filtrado = $_GET['correo'] ?? '';
            $etiqueta_filtrada = strtolower(trim($_GET['etiqueta'] ?? ''));
            $fecha_inicio = $_GET['fecha_inicio'] ?? '';
            $fecha_fin = $_GET['fecha_fin'] ?? '';

            $leads_filtrados = array_filter($leads, function ($lead) use ($correo_filtrado, $etiqueta_filtrada, $fecha_inicio, $fecha_fin) {
                if ($correo_filtrado && stripos($lead['correo'], $correo_filtrado) === false) return false;

                if ($etiqueta_filtrada) {
                    $etiquetas = array_map('strtolower', $lead['etiquetas'] ?? []);
                    if (!in_array($etiqueta_filtrada, $etiquetas)) return false;
                }

                if ($fecha_inicio && $lead['fecha'] < $fecha_inicio) return false;
                if ($fecha_fin && $lead['fecha'] > $fecha_fin) return false;

                return true;
            });
            ?>

            <?php if (empty($leads_filtrados)) : ?>
                <p><?php _e('No hay leads registrados con ese filtro.', 'golden-shark'); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Nombre', 'golden-shark'); ?></th>
                            <th><?php _e('Correo', 'golden-shark'); ?></th>
                            <th><?php _e('Mensaje', 'golden-shark'); ?></th>
                            <th><?php _e('Fecha', 'golden-shark'); ?></th>
                            <th><?php _e('Acciones', 'golden-shark'); ?></th>
                            <th><?php _e('Etiquetas', 'golden-shark'); ?></th>
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
                                    <a href="<?php echo admin_url('admin.php?page=golden-shark-leads&editar_lead=' . $i); ?>"><?php _e('Editar', 'golden-shark'); ?></a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-leads&eliminar_lead=' . $i), 'eliminar_lead_' . $i, '_nonce'); ?>" onclick="return confirm('<?php echo esc_js(__('¿Eliminar este lead?', 'golden-shark')); ?>')">Eliminar</a>
                                </td>
                                <td>
                                    <?php
                                    echo isset($lead['etiquetas']) && is_array($lead['etiquetas'])
                                        ? implode(', ', array_map('esc_html', $lead['etiquetas']))
                                        : '-';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <a href="#top" class="gs-go-top" title="<?php esc_attr_e('Volver al inicio', 'golden-shark'); ?>">⬆️</a>
    </div>
<?php
}