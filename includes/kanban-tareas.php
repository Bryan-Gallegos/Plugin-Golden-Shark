<?php
if (!defined('ABSPATH')) exit;

/**
 * Renderiza la vista tipo Kanban de las tareas internas.
 */
function golden_shark_render_kanban() {
    if (!golden_shark_user_can('golden_shark_acceso_basico')) {
        wp_die(__('⛔ No tienes permiso para acceder a esta sección.', 'golden-shark'));
    }

    $tareas = get_option('golden_shark_tareas', []);
    $pendientes = [];
    $progreso = [];
    $completadas = [];

    foreach ($tareas as $i => $tarea) {
        $tarea['id'] = $i;
        switch ($tarea['estado']) {
            case 'completado': $completadas[] = $tarea; break;
            case 'progreso': $progreso[] = $tarea; break;
            default: $pendientes[] = $tarea; break;
        }
    }

    $estados = [
        'pendiente'   => __('📋 Pendiente', 'golden-shark'),
        'progreso'    => __('🔧 En progreso', 'golden-shark'),
        'completado'  => __('✅ Completado', 'golden-shark')
    ];
    ?>

    <div class="wrap gs-container">
        <h2><?php echo esc_html__('📌 Vista Kanban - Tareas internas', 'golden-shark'); ?></h2>

        <div id="gs-kanban" style="display: flex; gap: 20px; margin-top: 20px;">
            <?php foreach (['pendiente' => $pendientes, 'progreso' => $progreso, 'completado' => $completadas] as $estado => $lista): ?>
                <div class="gs-kanban-col" style="flex: 1; background: #f9f9f9; padding: 10px; border: 1px solid #ccc;" aria-label="<?php echo esc_attr($estados[$estado]); ?>">
                    <h3 style="text-align: center;"><?php echo esc_html($estados[$estado]); ?></h3>

                    <?php if (empty($lista)) : ?>
                        <p style="text-align: center;"><?php echo esc_html__('Sin tareas en esta columna', 'golden-shark'); ?></p>
                    <?php else : ?>
                        <?php foreach ($lista as $t): ?>
                            <div class="gs-kanban-card" style="background: white; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd;" role="region" aria-label="<?php echo esc_attr($t['titulo']); ?>">
                                <strong><?php echo esc_html($t['titulo']); ?></strong><br>
                                <small><?php echo esc_html__('📅 Fecha:', 'golden-shark') . ' ' . esc_html($t['fecha']); ?></small><br>
                                <small>
                                    <?php
                                    $user = get_userdata($t['responsable'] ?? 0);
                                    $nombre = $user ? $user->display_name : __('No asignado', 'golden-shark');
                                    echo esc_html__('👤 Responsable:', 'golden-shark') . ' ' . esc_html($nombre);
                                    ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
}

/**
 * Agrega la vista Kanban como submenú del panel principal.
 */
function golden_shark_add_kanban_menu() {
    add_submenu_page(
        'golden-shark-panel',
        __('Vista Kanban', 'golden-shark'),
        __('Kanban', 'golden-shark'),
        'golden_shark_acceso_basico',
        'golden-shark-kanban',
        'golden_shark_render_kanban'
    );
}
add_action('admin_menu', 'golden_shark_add_kanban_menu');
