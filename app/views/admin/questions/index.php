<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card">
    <div class="card-header-flex">
        <h3 class="card-title"><i class="fas fa-question-circle"></i> Gestión de Preguntas</h3>
        <a href="/admin/preguntas/crear" class="btn btn-primary" style="width: auto;">
            <i class="fas fa-plus-circle"></i> Nueva Pregunta
        </a>
    </div>

    <!-- Filter Form -->
    <form action="/admin/preguntas" method="GET" class="filter-bar">
        <div class="search-input-wrapper">
            <input type="text" 
                   name="q" 
                   class="form-control" 
                   placeholder="Buscar texto en preguntas..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        
        <div style="flex-grow: 1; max-width: 250px;">
            <select name="categoria_id" class="form-control">
                <option value="">-- Todas las Categorías --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $selectedCategoryId == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width: auto; height: 42px;"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="/admin/preguntas" class="btn btn-cancel" style="width: auto; height: 42px; display: inline-flex; align-items: center;"><i class="fas fa-sync-alt"></i> Limpiar</a>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 150px;">Categoría</th>
                    <th>Pregunta</th>
                    <th style="width: 90px; text-align: center;">Imagen</th>
                    <th style="width: 180px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td>#<?= $q['id'] ?></td>
                            <td>
                                <span class="result-badge badge-warning" style="margin:0; font-size: 0.8rem; padding: 4px 10px; background: rgba(79, 70, 229, 0.08); color: var(--primary-color);">
                                    <?= htmlspecialchars($q['categoria_nombre']) ?>
                                </span>
                            </td>
                            <td style="font-weight: 500; color: #334155; line-height: 1.4;">
                                <?= htmlspecialchars(mb_strimwidth($q['pregunta'], 0, 100, "...")) ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($q['imagen'])): ?>
                                    <span style="color: var(--success-color); font-size: 1.2rem;" title="Tiene imagen cargada">
                                        <i class="fas fa-image"></i>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 1.2rem;" title="Sin imagen">
                                        <i class="far fa-image" style="opacity: 0.3;"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="/admin/preguntas/editar/<?= $q['id'] ?>" class="btn btn-sm btn-warning-edit" title="Editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete-ajax" 
                                            data-id="<?= $q['id'] ?>" 
                                            data-type="pregunta" 
                                            data-url="/admin/preguntas/eliminar/<?= $q['id'] ?>" 
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
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                            No se encontraron preguntas que coincidan con los filtros.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php 
                $queryParams = $_GET; 
                for ($i = 1; $i <= $pages; $i++): 
                    $queryParams['page'] = $i;
                    $pageUrl = '/admin/preguntas?' . http_build_query($queryParams);
                    $activeClass = ($i == $currentPage) ? 'active' : '';
            ?>
                <div class="page-item <?= $activeClass ?>">
                    <a href="<?= $pageUrl ?>" class="page-link"><?= $i ?></a>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
