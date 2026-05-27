<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card" style="max-width: 600px; margin: 0 auto;">
    <h3 class="card-title" style="margin-bottom: 24px;"><i class="fas fa-edit"></i> Editar Categoría</h3>

    <form action="/admin/categorias/editar/<?= $category['id'] ?>" method="POST">
        <!-- CSRF Token -->
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nombre" class="form-label required">Nombre de la Categoría</label>
            <input type="text" 
                   id="nombre" 
                   name="nombre" 
                   class="form-control" 
                   placeholder="Ej. Álgebra, Biología Humana, etc." 
                   value="<?= htmlspecialchars(old('nombre', $category['nombre'])) ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea id="descripcion" 
                      name="descripcion" 
                      class="form-control" 
                      rows="4" 
                      placeholder="Breve descripción sobre los temas que abordará esta categoría..."><?= htmlspecialchars(old('descripcion', $category['descripcion'])) ?></textarea>
        </div>

        <div class="form-group">
            <label for="puntaje_maximo" class="form-label required">Puntaje Máximo del Examen</label>
            <input type="number" 
                   id="puntaje_maximo" 
                   name="puntaje_maximo" 
                   class="form-control" 
                   min="1" 
                   step="0.1"
                   value="<?= htmlspecialchars(old('puntaje_maximo', $category['puntaje_maximo'])) ?>" 
                   required>
            <small style="color:var(--text-subtle); display:block; margin-top:6px;">Ejemplo: 20, 100, etc. La nota del examen se calculará en base a este valor.</small>
        </div>

        <div class="form-actions">
            <a href="/admin/categorias" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-submit">Actualizar Categoría</button>
        </div>
    </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
