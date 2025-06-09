<?php
if (!defined('ABSPATH')) exit;

// 📅 EVENTOS
function golden_shark_render_eventos()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $eventos = get_option('golden_shark_eventos', []);

    // Limpieza de eventos antiguos
    if (isset($_POST['eliminar_eventos_antiguos']) && current_user_can('golden_shark_configuracion')) {
        $hoy = date('Y-m-d');
        $eventos = array_filter($eventos, function ($evento) use ($hoy) {
            return substr($evento['fecha'], 0, 10) >= $hoy;
        });

        update_option('golden_shark_eventos', array_values($eventos));
        golden_shark_log('Se eliminaron eventos antiguos');
        golden_shark_log_usuario('Usó el botón "Eliminar eventos antiguos". ');
        echo '<div class="notice notice-warning"><p>' . __('🧹 Eventos anteriores a hoy eliminados correctamente.', 'golden-shark') . '</p></div>';
    }

    if (isset($_GET['toggle_favorito']) && isset($_GET['_nonce'])) {
        $evento_id = intval($_GET['toggle_favorito']);
        if (wp_verify_nonce($_GET['_nonce'], 'toogle_favorito_' . $evento_id)) {
            golden_shark_toggle_evento_favorito($evento_id);
            wp_redirect(remove_query_arg(['toggle_favorito', '_nonce']));
            exit;
        }
    }

    // Exportar CSV
    if (isset($_POST['exportar_csv'])) {
        $tipo_filtro = sanitize_text_field($_POST['filtro_tipo'] ?? '');
        $etiqueta_filtro = strtolower(trim(sanitize_text_field($_POST['filtro_etiqueta'] ?? '')));
        $fecha_inicio = sanitize_text_field($_POST['filtro_fecha_inicio'] ?? '');
        $fecha_fin = sanitize_text_field($_POST['filtro_fecha_fin'] ?? '');

        $eventos_filtrados = array_filter($eventos, function ($evento) use ($tipo_filtro, $etiqueta_filtro, $fecha_inicio, $fecha_fin) {
            if ($tipo_filtro && $evento['tipo'] !== $tipo_filtro) return false;

            if ($etiqueta_filtro) {
                $etiquetas = array_map('strtolower', $evento['etiquetas'] ?? []);
                if (!in_array($etiqueta_filtro, $etiquetas)) return false;
            }

            $fecha_evento = substr($evento['fecha'], 0, 10);
            if ($fecha_inicio && $fecha_evento < $fecha_inicio) return false;
            if ($fecha_fin && $fecha_evento > $fecha_fin) return false;

            return true;
        });

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="eventos_golden_shark.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Título', 'Fecha', 'Ubicación', 'Tipo', 'Etiquetas']);
        foreach ($eventos_filtrados as $evento) {
            fputcsv($output, [
                $evento['titulo'],
                $evento['fecha'],
                $evento['lugar'],
                $evento['tipo'],
                implode(', ', $evento['etiquetas'] ?? [])
            ]);
        }
        fclose($output);
        golden_shark_log('Se exportaron los eventos filtrados a CSV.');
        exit;
    }

    // Guardar nuevo evento
    if (isset($_POST['nuevo_evento'])) {
        if (!isset($_POST['evento_nonce']) || !wp_verify_nonce($_POST['evento_nonce'], 'guardar_evento_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }

        $imagen_url = '';
        $docuemnto_url = '';

        if (!empty($_FILES['evento_imagen']['tmp_name'])) {
            $upload = wp_handle_upload($_FILES['evento_imagen'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $imagen_url = esc_url_raw($upload['url']);
            }
        }

        if (!empty($_FILES['evento_documento']['tmp_name'])) {
            $upload = wp_handle_upload($_FILES['evento_documento'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $docuemnto_url = esc_url_raw($upload['url']);
            }
        }

        $eventos[] = [
            'titulo'    => sanitize_text_field($_POST['evento_titulo']),
            'fecha'     => sanitize_text_field($_POST['evento_fecha']),
            'lugar'     => sanitize_text_field($_POST['evento_lugar']),
            'tipo'      => sanitize_text_field($_POST['evento_tipo']),
            'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['evento_etiquetas']))),
            'imagen'    => $imagen_url,
            'documento' => $docuemnto_url,
        ];
        update_option('golden_shark_eventos', $eventos);
        $evento_id = array_key_last($eventos);
        golden_shark_guardar_historial_objeto('eventos', $evento_id, __('Creado', 'golden-shark'));
        golden_shark_log('Se registró un nuevo evento: ' . $_POST['evento_titulo']);
        update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('✅ Evento guardado correctamente.', 'golden-shark'));
        echo '<div class="updated"><p>' . __('✅ Evento guardado correctamente.', 'golden-shark') . '</p></div>';
        if (function_exists('golden_shark_disparar_webhook_evento')) {
            golden_shark_disparar_webhook_evento(end($eventos));
        }
        if (function_exists('golden_shark_crear_tareas_automaticas')) {
            golden_shark_crear_tareas_automaticas(end($eventos));
        }
    }


    // Editar evento
    if (isset($_POST['editar_evento_guardado'])) {
        if (!isset($_POST['editar_evento_nonce']) || !wp_verify_nonce($_POST['editar_evento_nonce'], 'guardar_edicion_evento_nonce')) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }

        $id = intval($_POST['evento_id']);
        if (isset($eventos[$id])) {

            $imagen_url = $eventos[$id]['imagen'] ?? '';
            $documento_url = $eventos[$id]['documento'] ?? '';

            if (!empty($_POST['eliminar_imagen'])) {
                $imagen_url = '';
            }

            if (!empty($_POST['eliminar_documento'])) {
                $docuemnto_url = '';
            }

            if (!empty($_FILES['evento_imagen']['tmp_name'])) {
                $upload = wp_handle_upload($_FILES['evento_imagen'], ['test_form' => false]);
                if (!isset($upload['error'])) {
                    $imagen_url = esc_url_raw($upload['url']);
                }
            }

            if (!empty($_FILES['evento_documento']['tmp_name'])) {
                $upload = wp_handle_upload($_FILES['evento_documento'], ['test_form' => false]);
                if (!isset($upload['error'])) {
                    $docuemnto_url = esc_url_raw($upload['url']);
                }
            }

            $eventos[$id] = [
                'titulo'    => sanitize_text_field($_POST['evento_titulo']),
                'fecha'     => sanitize_text_field($_POST['evento_fecha']),
                'lugar'     => sanitize_text_field($_POST['evento_lugar']),
                'tipo'      => sanitize_text_field($_POST['evento_tipo']),
                'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['evento_etiquetas']))),
                'imagen'    => $imagen_url,
                'documento' => $docuemnto_url,
            ];
            update_option('golden_shark_eventos', $eventos);
            golden_shark_guardar_historial_objeto('eventos', $id, __('Editado', 'golden-shark'));
            golden_shark_log('Se editó el evento: ' . $_POST['evento_titulo']);
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('✅ Evento actualizado correctamente.', 'golden-shark'));
            echo '<div class="updated"><p>' . __('✏️ Evento actualizado correctamente.', 'golden-shark') . '</p></div>';
            if (function_exists('golden_shark_disparar_webhook_evento')) {
                golden_shark_disparar_webhook_evento($eventos[$id]);
            }
            if (function_exists('golden_shark_crear_tareas_automaticas')) {
                golden_shark_crear_tareas_automaticas($eventos[$id]);
            }
        }
    }


    // Eliminar evento
    if (isset($_GET['eliminar_evento']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_evento']);
        $nonce = $_GET['_nonce'];

        if (!wp_verify_nonce($nonce, 'eliminar_evento_' . $id)) {
            wp_die(__('⚠️ Seguridad fallida. Token inválido.', 'golden-shark'));
        }

        if (isset($eventos[$id])) {
            unset($eventos[$id]);
            $eventos = array_values($eventos);
            update_option('golden_shark_eventos', $eventos);
            golden_shark_guardar_historial_objeto('eventos', $id, __('Eliminado', 'golden-shark'));
            golden_shark_log('Se eliminó un evento con ID: ' . $id);
            update_user_meta(get_current_user_id(), 'gs_notificacion_interna', __('🗑️ Evento eliminado correctamente.', 'golden-shark'));
            echo '<div class="updated"><p>' . __('🗑️ Evento eliminado.', 'golden-shark') . '</p></div>';
        }
    }


?>
    <div class="wrap" id="top">
        <h2><?php _e('Gestión de Eventos Internos', 'golden-shark'); ?></h2>

        <?php if (isset($_GET['editar_evento'])):
            $id = intval($_GET['editar_evento']);
            if (isset($eventos[$id])): $evento = $eventos[$id]; ?>
                <div class="gs-container">
                    <h3><?php _e('Editar Evento', 'golden-shark'); ?></h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="editar_evento_guardado" value="1">
                        <input type="hidden" name="evento_id" value="<?php echo $id; ?>">
                        <?php wp_nonce_field('guardar_edicion_evento_nonce', 'editar_evento_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><?php __('Título', 'golden-shark') ?>:</th>
                                <td><input type="text" name="evento_titulo" value="<?php echo esc_attr($evento['titulo']); ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><?php __('Fecha', 'golden-shark') ?>:</th>
                                <td><input type="date" name="evento_fecha" value="<?php echo esc_attr($evento['fecha']); ?>" required></td>
                            </tr>
                            <tr>
                                <th><?php __('Ubicación', 'golden-shark') ?>:</th>
                                <td><input type="text" name="evento_lugar" value="<?php echo esc_attr($evento['lugar']); ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><?php __('Tipo', 'golden-shark') ?>:</th>
                                <td>
                                    <select name="evento_tipo">
                                        <option value="interno" <?php selected($evento['tipo'], 'interno'); ?>><?php __('Interno', 'golden-shark') ?></option>
                                        <option value="reunion"> <?php selected($evento['tipo'], 'reunion'); ?><?php __('Reunión', 'golden-shark') ?></option>
                                        <option value="lanzamiento" <?php selected($evento['tipo'], 'lanzamiento'); ?>><?php __('Lanzamiento', 'golden-shark') ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php __('Etiquetas', 'golden-shark') ?>:</th>
                                <td><input type="text" name="evento_etiquetas" value="<?php echo esc_attr(implode(', ', $evento['etiquetas'] ?? [])); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php __('Imagen adjunta', 'golden-shark'); ?>:</th>
                                <td>
                                    <?php if (!empty($evento['imagen'])): ?>
                                        <img src="<?php echo esc_url($evento['imagen']); ?>" style="max-width:100px;height:auto;"><br>
                                        <label><input type="checkbox" name="eliminar_imagen"> <?php _e('Eliminar imagen actual', 'golden-shark'); ?></label><br>
                                    <?php endif; ?>
                                    <input type="file" name="evento_imagen" accept="image/*">
                                </td>
                            </tr>
                            <tr>
                                <th><?php __('Documento adjunto', 'golden-shark') ?>:</th>
                                <td>
                                    <?php if (!empty($evento['documento'])): ?>
                                        <a href="<?php echo esc_url($evento['documento']); ?>" target="_blank"><?php _e('📄 Ver documento actual', 'golden-shark'); ?></a><br>
                                        <label><input type="checkbox" name="eliminar_documento"><?php _e('Eliminar documento actual', 'golden-shark'); ?></label><br>
                                    <?php endif; ?>
                                    <input type="file" name="evento_documento" accept=".pdf,.doc,.docx">
                                </td>
                            </tr>
                        </table>
                        <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar cambios', 'golden-shark'); ?>"></p>
                    </form>
                    <hr>
                </div>
        <?php endif;
        endif; ?>


        <div class="gs-container">
            <h3><?php _e('Nuevo Evento', 'golden-shark'); ?></h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="nuevo_evento" value="1">
                <?php wp_nonce_field('guardar_evento_nonce', 'evento_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Título', 'golden-shark'); ?>:</th>
                        <td><input type="text" name="evento_titulo" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><?php __('Fecha', 'golden-shark') ?>:</th>
                        <td><input type="date" name="evento_fecha" required></td>
                    </tr>
                    <tr>
                        <th><?php __('Ubicación', 'golden-shark') ?>:</th>
                        <td><input type="text" name="evento_lugar" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><?php __('Tipo', 'golden-shark') ?>:</th>
                        <td>
                            <select name="evento_tipo">
                                <option value="interno"><?php _e('Interno', 'golden-shark'); ?></option>
                                <option value="reunion"><?php _e('Reunión', 'golden-shark'); ?></option>
                                <option value="lanzamiento"><?php _e('Lanzamiento', 'golden-shark'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php __('Etiquetas', 'golden-shark') ?>:</th>
                        <td><input type="text" name="evento_etiquetas" placeholder="<?php esc_attr_e('Ej: urgente, cliente, zona sur', 'golden-shark'); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php __('Imagen adjunta', 'golden-shark') ?>:</th>
                        <td><input type="file" name="evento_imagen" accept="image/*"></td>
                    </tr>
                    <tr>
                        <th><?php __('Documento adjunto', 'golden-shark'); ?>:</th>
                        <td><input type="file" name="evento_documento" accept=".pdf,.doc,.docx"></td>
                    </tr>
                </table>
                <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Guardar evento', 'golden-shark'); ?>"></p>
            </form>
        </div>

        <div class="gs-container">
            <form method="post" style="margin-top:20px;">
                <input type="hidden" name="exportar_csv" value="1">

                <select name="filtro_tipo">
                    <option value=""><?php __('Todos', 'golden-shark') ?></option>
                    <option value="interno" <?php selected($_GET['tipo'] ?? '', 'interno'); ?>><?php __('Interno', 'golden-shark') ?></option>
                    <option value="reunion" <?php selected($_GET['tipo'] ?? '', 'reunion'); ?>><?php __('Reunión', 'golden-shark') ?></option>
                    <option value="lanzamiento" <?php selected($_GET['tipo'] ?? '', 'lanzamiento'); ?>><?php __('Lanzamiento', 'golden-shark') ?></option>
                </select>

                <input type="text" name="filtro_etiqueta" placeholder="Etiqueta..." value="<?php echo esc_attr($_GET['etiqueta'] ?? ''); ?>">

                <input type="date" name="filtro_fecha_inicio" value="<?php echo esc_attr($_GET['fecha_inicio'] ?? ''); ?>">
                <input type="date" name="filtro_fecha_fin" value="<?php echo esc_attr($_GET['fecha_fin'] ?? ''); ?>">

                <input type="submit" class="button button-secondary" value="📤 Exportar eventos filtrados">
            </form>
        </div>

        <hr>
        <div class="gs-container">
            <form method="get" style="margin-bottom: 20px;" class="gs-filtros-eventos">
                <input type="hidden" name="page" value="golden-shark-eventos">

                <label for="filtro_tipo"><strong><?php __('Tipo', 'golden-shark') ?>:</strong></label>
                <select name="tipo" id="filtro_tipo">
                    <option value=""><?php __('Todos', 'golden-shark') ?></option>
                    <option value="interno" <?php selected($_GET['tipo'] ?? '', 'interno'); ?>><?php __('Interno', 'golden-shark') ?></option>
                    <option value="reunion" <?php selected($_GET['tipo'] ?? '', 'reunion'); ?>><?php __('Reunión', 'golden-shark') ?></option>
                    <option value="lanzamiento" <?php selected($_GET['tipo'] ?? '', 'lanzamiento'); ?>><?php __('Lanzamiento', 'golden-shark') ?></option>
                </select>

                <label for="filtro_etiqueta"><strong><?php __('Etiqueta', 'golden-shark') ?>:</strong></label>
                <input type="text" name="etiqueta" id="filtro_etiqueta" value="<?php echo esc_attr($_GET['etiqueta'] ?? ''); ?>" placeholder="Ej: urgente, marketing">

                <label for="filtro_fecha_inicio"><strong><?php __('Desde', 'golden-shark') ?>:</strong></label>
                <input type="date" name="fecha_inicio" id="filtro_fecha_inicio" value="<?php echo esc_attr($_GET['fecha_inicio'] ?? ''); ?>">

                <label for="filtro_fecha_fin"><strong><?php __('Hasta', 'golden-shark') ?>:</strong></label>
                <input type="date" name="fecha_fin" id="filtro_fecha_fin" value="<?php echo esc_attr($_GET['fecha_fin'] ?? ''); ?>">

                <input type="submit" class="button" value="Filtrar eventos">
            </form>

            <h3><?php _e('Eventos Registrados:', 'golden-shark'); ?></h3>
            <?php if (current_user_can('golden_shark_configuracion')): ?>
                <form method="post" style="margin-bottom: 20px;">
                    <input type="hidden" name="eliminar_eventos_antiguos" value="1">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('¿Eliminar todos los eventos con fecha pasada?', 'golden-shark')); ?>')">
                        <?php __('🧹 Eliminar eventos antiguos', 'golden-shark') ?>
                    </button>
                </form>
            <?php endif; ?>
            <?php if (empty($eventos)) : ?>
                <p><?php _e('No hay eventos registrados.', 'golden-shark'); ?></p>
            <?php else : ?>
                <table class="widefat fixed">
                    <thead>
                        <tr>
                            <th><?php _e('Título', 'golden-shark'); ?></th>
                            <th><?php _e('Fecha', 'golden-shark'); ?></th>
                            <th><?php _e('Ubicación', 'golden-shark'); ?></th>
                            <th><?php _e('Acciones', 'golden-shark'); ?></th>
                            <th><?php _e('Etiquetas', 'golden-shark'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tipo_filtrado = $_GET['tipo'] ?? '';
                        $etiqueta_filtrada = strtolower(trim($_GET['etiqueta'] ?? ''));
                        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
                        $fecha_fin = $_GET['fecha_fin'] ?? '';
                        foreach ($eventos as $i => $evento) :
                            if ($tipo_filtrado && $evento['tipo'] !== $tipo_filtrado) continue;

                            if ($etiqueta_filtrada) {
                                $etiquetas = array_map('strtolower', $evento['etiquetas'] ?? []);
                                if (!in_array($etiqueta_filtrada, $etiquetas)) continue;
                            }

                            if ($fecha_inicio && substr($evento['fecha'], 0, 10) < $fecha_inicio) continue;
                            if ($fecha_fin && substr($evento['fecha'], 0, 10) > $fecha_fin) continue;
                        ?>
                            <tr>
                                <td><?php echo esc_html($evento['titulo']); ?></td>
                                <td><?php echo esc_html($evento['fecha']); ?></td>
                                <td><?php echo esc_html($evento['lugar']); ?></td>
                                <td>
                                    <?php
                                    $is_fav = golden_shark_es_evento_favorito($i);
                                    $fav_url = wp_nonce_url(admin_url('admin.php?page=golden-shark-eventos&toggle_favorito=' . $i), 'toggle_favorito_' . $i, '_nonce');
                                    ?>
                                    <a href="<?php echo $fav_url; ?>" title="Marcar como favorito">
                                        <?php echo $is_fav ? '⭐' : '☆'; ?>
                                    </a> |
                                    <a href="<?php echo admin_url('admin.php?page=golden-shark-eventos&editar_evento=' . $i); ?>"><?php _e('Editar', 'golden-shark'); ?></a> |
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-eventos&eliminar_evento=' . $i), 'eliminar_evento_' . $i, '_nonce'); ?>" onclick="return confirm('<?php echo esc_js(__('¿Eliminar este evento?', 'golden-shark')); ?>');"><?php __('Eliminar', 'golden-shark') ?></a>
                                </td>
                                <td>
                                    <?php
                                    echo isset($evento['etiquetas']) && is_array($evento['etiquetas'])
                                        ? implode(', ', array_map('esc_html', $evento['etiquetas']))
                                        : '-';
                                    ?>
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
