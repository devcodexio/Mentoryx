<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card" style="max-width: 800px;">
    <h3 class="card-title" style="margin-bottom: 24px;">
        <i class="fas fa-plus-circle"></i> Agregar Nueva Pregunta
    </h3>

    <form action="/admin/preguntas/crear" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Row: Category + Image -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

            <div class="form-group">
                <label class="form-label required">Categoría / Año de Examen</label>
                <select name="categoria_id" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= old('categoria_id') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Imagen <span style="color:var(--text-subtle);font-weight:400;">(JPG/PNG/GIF &bull; máx 2MB)</span>
                </label>
                <small style="display:block;color:var(--text-subtle);font-size:.78rem;margin-bottom:8px;">
                    <i class="fas fa-info-circle"></i> Si subes una imagen, el texto de la pregunta es opcional.
                    La imagen puede SER la pregunta.
                </small>
                <input type="file" id="imagen-file-input" name="imagen"
                       class="form-control" accept="image/*">
                <div id="image-preview-container" class="image-preview-container">
                    <span style="color:var(--text-subtle);font-size:.85rem;">Sin imagen seleccionada</span>
                </div>
            </div>
        </div>

        <!-- Question text — OPTIONAL when image is provided -->
        <div class="form-group">
            <label class="form-label" id="pregunta-label">
                Enunciado de la Pregunta
                <span id="pregunta-opt-badge" style="font-weight:400; color:var(--text-subtle); font-size:0.78rem; margin-left:6px;">(opcional si hay imagen)</span>
            </label>
            <textarea name="pregunta" id="pregunta-input"
                      class="form-control" rows="3"
                      placeholder="Escribe el texto de la pregunta... (no es necesario si adjuntas una imagen)"><?= htmlspecialchars(old('pregunta')) ?></textarea>
        </div>

        <!-- Alternatives -->
        <div class="form-group" style="background:rgba(15,23,42,0.4); padding:24px; border:1px solid rgba(124,58,237,0.2); border-radius:var(--radius-lg);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <label class="form-label required" style="margin:0;">
                    Alternativas <span style="font-weight:400;color:var(--text-subtle);">(marca la correcta)</span>
                </label>
                <button type="button" id="btn-add-alternative"
                        class="btn btn-ghost btn-sm" style="width:auto;">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </div>

            <div id="alternatives-builder-list" class="alternatives-builder-list">
                <?php foreach (['A','B','C','D'] as $i => $letter): ?>
                    <div class="builder-item">
                        <div class="correct-radio-label">
                            <input type="radio" name="correcta" value="<?= $i ?>"
                                   <?= old('correcta') == $i ? 'checked' : '' ?> required>
                            <span>Correcta</span>
                        </div>
                        <span style="font-weight:800; color:var(--text-subtle); width:22px; font-family:var(--font-display);"><?= $letter ?>.</span>
                        <input type="text" name="alternativas[]" class="form-control"
                               placeholder="Texto alternativa <?= $letter ?>"
                               value="<?= htmlspecialchars(old("alternativas.{$i}")) ?>" required>
                        <button type="button" class="btn-icon btn-icon-delete btn-remove-alt" title="Eliminar">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Resolution -->
        <div class="form-group" style="margin-top:20px;">
            <label class="form-label">Resolución Detallada (Opcional)</label>
            <textarea name="resolucion" class="form-control" rows="4"
                      placeholder="Explica paso a paso la solución correcta..."><?= htmlspecialchars(old('resolucion')) ?></textarea>
                      
            <div style="margin-top: 15px;">
                <label for="imagen_resolucion" class="form-label">Subir Imagen de Resolución (Opcional)</label>
                <input type="file" id="imagen_resolucion" name="imagen_resolucion" class="form-control" accept="image/*">
            </div>
        </div>

        <!-- Score -->
        <div class="form-group">
            <label class="form-label required">Puntaje de esta Pregunta</label>
            <input type="number" name="puntaje" class="form-control"
                   min="0.1" step="0.1"
                   value="<?= htmlspecialchars(old('puntaje', '1')) ?>"
                   placeholder="Ej: 0.5, 1, 2, 4"
                   required>
            <small style="color:var(--text-subtle);display:block;margin-top:6px;">
                <i class="fas fa-info-circle"></i> La nota final del estudiante = suma de puntos de respuestas correctas.
            </small>
        </div>

        <div class="form-actions">
            <a href="/admin/preguntas" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Pregunta
            </button>
        </div>
    </form>
</div>

<script>
// Make pregunta text optional when image is chosen
document.getElementById('imagen-file-input').addEventListener('change', function () {
    const hasFile = this.files && this.files.length > 0;
    const input   = document.getElementById('pregunta-input');
    const badge   = document.getElementById('pregunta-opt-badge');
    if (hasFile) {
        input.required = false;
        badge.style.color = 'var(--success)';
        badge.textContent = '(opcional — imagen cargada ✓)';
    } else {
        input.required = false; // keep optional always when there's no category constraint
        badge.style.color = 'var(--text-subtle)';
        badge.textContent = '(opcional si hay imagen)';
    }
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
