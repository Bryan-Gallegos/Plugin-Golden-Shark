<?php
if (!defined('ABSPATH')) exit;

// 🗂 MÓDULO DE TAREAS INTERNAS
function golden_shark_render_tareas()
{
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $current_user_id = get_current_user_id();
    $vista_predeterminada = get_user_meta($current_user_id, 'gs_vista_tareas', true) ?: 'lista';

    if (isset($_POST['cambiar_vista_tareas']) && isset($_POST['gs_vista_form_nonce']) && wp_verify_nonce($_POST['gs_vista_form_nonce'], 'gs_cambiar_vista')) {
        $nueva_vista = sanitize_text_field($_POST['vista_tareas']);
        update_user_meta($current_user_id, 'gs_vista_tareas', $nueva_vista);
        $vista_predeterminada = $nueva_vista;
        echo '<div class="notice notice-success"><p>' . sprintf(__('✅ Vista actualizada a "%s".', 'golden-shark'), esc_html($nueva_vista)) . '</p></div>';
    }

    if ($vista_predeterminada === 'kanban') {
        include_once plugin_dir_path(__FILE__) . 'kanban-tareas.php';
        return;
    }

    $tareas = get_option('golden_shark_tareas', []);

    // Eliminar tareas antiguas
    if(isset($_POST['eliminar_tareas_antiguas']) && current_user_can('golden_shark_configuracion')){
        $fecha_hoy = date('Y-m-d');
        $tareas = array_filter($tareas, function($tarea) use ($fecha_hoy){
            return $tarea['fecha'] >= $fecha_hoy;
        });

        update_option('golden_shark_tareas', array_values($tareas));
        golden_shark_log('Se eliminaron tareas antiguas');
        golden_shark_log_usuario('Usó el botón "Eliminar tareas antiguas".');
        echo '<div class="notice notice-warning"><p>' . __('🧹 Tareas anteriores a hoy eliminadas.', 'golden-shark') . '</p></div>';
    }

    // Eliminar tarea
    if (isset($_GET['eliminar_tarea']) && isset($_GET['_nonce'])) {
        $id = intval($_GET['eliminar_tarea']);
        if (wp_verify_nonce($_GET['_nonce'], 'eliminar_tarea_' . $id) && isset($tareas[$id])) {
            unset($tareas[$id]);
            $tareas = array_values($tareas);
            update_option('golden_shark_tareas', $tareas);
            golden_shark_log('Se eliminó una tarea interna.');
            golden_shark_log_usuario('Eliminó una tarea interna.');
            echo '<div class="notice notice-success"><p>' . _e('✅ Tarea eliminada correctamente.', 'golden-shark') . '</p></div>';
        }
    }

    // Guardar nueva tarea
    if (isset($_POST['nueva_tarea'])) {
        check_admin_referer('guardar_tarea_nonce', 'tarea_nonce');

        $tareas[] = [
            'titulo' => sanitize_text_field($_POST['tarea_titulo']),
            'estado' => sanitize_text_field($_POST['tarea_estado']),
            'fecha'  => sanitize_text_field($_POST['tarea_fecha']),
            'responsable' => sanitize_text_field($_POST['tarea_responsable']),
            'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['tarea_etiquetas']))),
        ];
        update_option('golden_shark_tareas', $tareas);
        golden_shark_log('Se registró una nueva tarea interna.');
        golden_shark_log_usuario('Registró una nueva tarea.');
        echo '<div class="notice notice-success"><p>' . _e('✅ Tarea guardada correctamente', 'golden-shark') . '</p></div>';
    }

    // Edición rápida
    if (isset($_POST['editar_tarea'])) {
        check_admin_referer('editar_tarea_nonce', 'editar_tarea_nonce_field');

        $id = intval($_POST['tarea_id']);
        if (isset($tareas[$id])) {
            $tarea_anterior = $tareas[$id];
            $nueva_tarea = [
                'titulo' => sanitize_text_field($_POST['tarea_titulo']),
                'estado' => sanitize_text_field($_POST['tarea_estado']),
                'fecha'  => sanitize_text_field($_POST['tarea_fecha']),
                'responsable' => sanitize_text_field($_POST['tarea_responsable']),
                'etiquetas' => array_map('trim', explode(',', sanitize_text_field($_POST['tarea_etiquetas']))),
            ];

            // Mantener historial
            $historial = $tarea_anterior['historial'] ?? [];
            $historial[] = [
                'accion' => 'Editada por ' . wp_get_current_user()->user_login,
                'fecha'  => current_time('Y-m-d H:i:s')
            ];
            $nueva_tarea['historial'] = $historial;

            $tareas[$id] = $nueva_tarea;
            update_option('golden_shark_tareas', $tareas);
            golden_shark_log('Se actualizó una tarea interna.');
            golden_shark_log_usuario('Editó una tarea.');
            echo '<div class="notice notice-success"><p>' . _e('✅ Tarea actualizada correctamente', 'golden-shark') . '</p></div>';
        }
    }

?>

    <div class="wrap gs-container" id="top">
        <h2><?php _e('🗂️ Tareas internas', 'golden-shark'); ?></h2>

        <form method="post" style="margin-bottom: 20px;">
            <?php wp_nonce_field('gs_cambiar_vista', 'gs_vista_form_nonce'); ?>
            <label for="vista_tareas"><?php _e('👁️ Vista preferida:', 'golden-shark'); ?></label>
            <select name="vista_tareas" onchange="this.form.submit();">
                <option value="lista" <?php selected($vista_predeterminada, 'lista'); ?>><?php _e('Lista', 'golden-shark'); ?></option>
                <option value="kanban" <?php selected($vista_predeterminada, 'kanban'); ?>><?php _e('Kanban', 'golden-shark') ?></option>
            </select>
            <input type="hidden" name="cambiar_vista_tareas" value="1">
        </form>

        <h3><?php _e('Nueva tarea', 'golden-shark'); ?></h3>
        <form method="post">
            <?php wp_nonce_field('guardar_tarea_nonce', 'tarea_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="tarea_titulo"><?php _e('Título:', 'golden-shark'); ?></label></th>
                    <td><input type="text" id="tarea_titulo" name="tarea_titulo" required></td>
                </tr>

                <tr>
                    <th><label for="tarea_estado"><?php _e('Estado:', 'golden-shark'); ?></label></th>
                    <td>
                        <select id="tarea_estado" name="tarea_estado">
                            <option value="pendiente"><?php _e('Pendiente', 'golden-shark'); ?></option>
                            <option value="completado"><?php _e('Completado', 'golden-shark'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><label for="tarea_fecha"><?php _e('Fecha:', 'golden-shark'); ?></label></th>
                    <td><input type="date" id="tarea_fecha" name="tarea_fecha" required></td>
                </tr>

                <tr>
                    <th><label for="tarea_responsable"><?php _e('Responsable:', 'golden-shark'); ?></label></th>
                    <td><input type="text" id="tarea_responsable" name="tarea_responsable"></td>
                </tr>
                <tr>
                    <th><label for="tarea_etiquetas"><?php _e('Etiquetas:', 'golden-shark'); ?></label></th>
                    <td><input type="text" id="tarea_etiquetas" name="tarea_etiquetas" placeholder="<?php esc_attr_e('Ej: urgente, marketing, cliente X', 'golden-shark'); ?>"></td>
                </tr>
            </table>
            <p><input type="submit" name="nueva_tarea" value="<?php esc_attr_e('Guardar tarea', 'golden-shark'); ?>"></p>
        </form>

        <hr>
        <h3><?php _e('📋 Filtros combinados', 'golden-shark'); ?></h3>
        <form method="get" style="margin-bottom: 20px;" class="gs-filtros-tareas">
            <input type="hidden" name="page" value="golden-shark-tareas">

            <label for="filtro_estado"><strong><?php _e('Estado:', 'golden-shark') ?></strong></label>
            <select name="estado" id="filtro_estado">
                <option value=""><?php _e('Todos', 'golden-shark') ?></option>
                <option value="pendiente" <?php selected($_GET['estado'] ?? '', 'pendiente'); ?>><?php _e('Pendiente', 'golden-shark') ?></option>
                <option value="completado" <?php selected($_GET['estado'] ?? '', 'completado'); ?>><?php _e('Completado', 'golden-shark') ?></option>
            </select>

            <label for="filtro_responsable"><strong><?php _e('Responsable:', 'golden-shark') ?></strong></label>
            <input type="text" name="responsable" id="filtro_responsable" value="<?php echo esc_attr($_GET['responsable'] ?? ''); ?>" placeholder="<?php esc_attr_e('ej. Juan', 'golden-shark'); ?>">

            <label for="filtro_etiqueta"><strong><?php _e('Etiqueta:', 'golden-shark') ?></strong></label>
            <input type="text" name="etiqueta" id="filtro_etiqueta" value="<?php echo esc_attr($_GET['etiqueta'] ?? ''); ?>" placeholder="<?php esc_attr_e('ej. urgente', 'golden-shark'); ?>">

            <label for="filtro_fecha_inicio"><strong><?php _e('Desde:', 'golden-shark') ?></strong></label>
            <input type="date" name="fecha_inicio" id="filtro_fecha_inicio" value="<?php echo esc_attr($_GET['fecha_inicio'] ?? ''); ?>">

            <label for="filtro_fecha_fin"><strong><?php _e('Hasta:', 'golden-shark') ?></strong></label>
            <input type="date" name="fecha_fin" id="filtro_fecha_fin" value="<?php echo esc_attr($_GET['fecha_fin'] ?? ''); ?>">

            <input type="submit" class="button" value="<?php _e('🔎 Filtrar tareas', 'golden-shark'); ?>">
        </form>

        <hr>
        <h3><?php _e('Tareas registradas', 'golden-shark'); ?></h3>
        <?php if(current_user_can('golden_shark_configuracion')): ?>
            <form method="post" style="margin-botton: 20px;">
                <input type="hidden" name="eliminar_tareas_antiguas" value="1">
                <button type="submit" class="button button-secondary"  onclick="return confirm('<?php echo esc_js(__('¿Seguro que deseas eliminar todas las tareas anteriores a hoy?', 'golden-shark')); ?>')">
                    <?php _e('🧹 Eliminar tareas antiguas', 'golden-shark') ?>
                </button>
            </form>
        <?php endif; ?>
        <?php
        $estado_filtro = $_GET['estado'] ?? '';
        $responsable_filtro = strtolower(trim($_GET['responsable'] ?? ''));
        $etiqueta_filtro = strtolower(trim($_GET['etiqueta'] ?? ''));
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';

        $tareas_filtradas = array_filter($tareas, function ($t) use ($estado_filtro, $responsable_filtro, $etiqueta_filtro, $fecha_inicio, $fecha_fin) {
            if ($estado_filtro && $t['estado'] !== $estado_filtro) return false;

            if ($responsable_filtro && stripos($t['responsable'] ?? '', $responsable_filtro) === false) return false;

            if ($etiqueta_filtro) {
                $etiquetas = array_map('strtolower', $t['etiquetas'] ?? []);
                if (!in_array($etiqueta_filtro, $etiquetas)) return false;
            }

            if ($fecha_inicio && $t['fecha'] < $fecha_inicio) return false;
            if ($fecha_fin && $t['fecha'] > $fecha_fin) return false;

            return true;
        });
        ?>

        <?php if (empty($tareas_filtradas)) : ?>
            <p><?php _e('No hay tareas que coincidan con los filtros aplicados.', 'golden-shark'); ?></p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php _e('Título', 'golden-shark'); ?></th>
                        <th><?php _e('Estado', 'golden-shark'); ?></th>
                        <th><?php _e('Fecha', 'golden-shark'); ?></th>
                        <th><?php _e('Responsable', 'golden-shark'); ?></th>
                        <th><?php _e('Acciones', 'golden-shark'); ?></th>
                        <th><?php _e('Etiquetas', 'golden-shark'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas_filtradas as $i => $t) : ?>
                        <tr>
                            <form method="post">
                                <?php wp_nonce_field('editar_tarea_nonce', 'editar_tarea_nonce_field'); ?>
                                <input type="hidden" name="tarea_id" value="<?php echo $i; ?>">
                                <td><input type="text" name="tarea_titulo" value="<?php echo esc_attr($t['titulo']); ?>"></td>
                                <td>
                                    <select name="tarea_estado">
                                        <option value="pendiente" <?php selected($t['estado'], 'pendiente'); ?>><?php __('Pendiente', 'golden-shark') ?></option>
                                        <option value="completado" <?php selected($t['estado'], 'completado'); ?>><?php __('Completado', 'golden-shark') ?></option>
                                    </select>
                                </td>
                                <td><input type="date" name="tarea_fecha" value="<?php echo esc_attr($t['fecha']); ?>"></td>
                                <td><input type="text" name="tarea_responsable" value="<?php echo esc_attr($t['responsable']); ?>"></td>
                                <td>
                                    <input type="submit" name="editar_tarea" value="<?php esc_attr_e('💾 Guardar', 'golden-shark'); ?>">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=golden-shark-tareas&eliminar_tarea=' . $i), 'eliminar_tarea_' . $i, '_nonce'); ?>" onclick="return confirm('<?php echo esc_js(__('¿Eliminar esta tarea?', 'golden-shark')); ?>')" class="button button-secondary">🗑️</a>
                                </td>
                                <td><input type="text" name="tarea_etiquetas" value="<?php echo esc_attr(implode(', ', $t['etiquetas'] ?? [])); ?>"></td>
                            </form>
                        </tr>
                        <?php if (!empty($t['historial'])): ?>
                            <tr>
                                <td colspan="5" style="background:#f9f9f9;">
                                    <strong><?php _e('🕓 Historial:', 'golden-shark'); ?></strong>
                                    <ul style="margin-left:20px; list-style: disc;">
                                        <?php foreach ($t['historial'] as $h): ?>
                                            <li><em><?php echo esc_html($h['fecha']); ?></em> – <?php echo esc_html($h['accion']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>

            </table>
        <?php endif; ?>
    </div>

    <a href="#top" class="gs-go-top" title="Volver al inicio">⬆️</a>

<?php
}