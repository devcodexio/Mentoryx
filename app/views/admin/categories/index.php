<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card">
    <div class="card-header-flex">
        <h3 class="card-title"><i class="fas fa-folder"></i> Listado de Categorías</h3>
        <a href="/admin/categorias/crear" class="btn btn-primary" style="width: auto;">
            <i class="fas fa-plus-circle"></i> Nueva Categoría
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nombre de Categoría</th>
                    <th>Descripción</th>
                    <th style="width: 180px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?= $cat['id'] ?></td>
                            <td style="font-weight: 600; color: #334155;"><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($cat['descripcion'] ?? 'Sin descripción.') ?></td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="/admin/categorias/editar/<?= $cat['id'] ?>" class="btn btn-sm btn-warning-edit" title="Editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete-ajax" 
                                            data-id="<?= $cat['id'] ?>" 
                                            data-type="categoría" 
                                            data-url="/admin/categorias/eliminar/<?= $cat['id'] ?>" 
                                            data-csrf="<?= \App\Core\Session::csrfToken() ?>"
                                            title="Eliminar">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                            Aún no se han registrado categorías.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
