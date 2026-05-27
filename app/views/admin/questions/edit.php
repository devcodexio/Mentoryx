<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    <h3 class="card-title" style="margin-bottom: 24px;"><i class="fas fa-edit"></i> Editar Pregunta</h3>

    <form action="/admin/preguntas/editar/<?= $question['id'] ?>" method="POST" enctype="multipart/form-data">
        <!-- CSRF Token -->
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Category select -->
            <div class="form-group">
                <label for="categoria_id" class="form-label required">Categoría de Examen</label>
                <select name="categoria_id" id="categoria_id" class="form-control" required>
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= old('categoria_id', $question['categoria_id']) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Image File Upload -->
            <div class="form-group">
                <label for="imagen-file-input" class="form-label">Subir Nueva Imagen (JPG, PNG, GIF - Máx. 2MB)</label>
                <input type="file" id="imagen-file-input" name="imagen" class="form-control" accept="image/*">
                
                <div id="image-preview-container" class="image-preview-container">
                    <?php if (!empty($question['imagen'])): ?>
                        <img src="/uploads/<?= htmlspecialchars($question['imagen']) ?>" class="img-preview" alt="Imagen actual">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Imagen actual cargada</span>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--danger-color); cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="eliminar_imagen" value="1"> <i class="fas fa-trash-alt"></i> Eliminar imagen actual
                            </label>
                        </div>
                    <?php else: ?>
                        <span style="font-size: 0.9rem; color: var(--text-muted);">Sin imagen cargada</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Question text -->
        <div class="form-group">
            <label for="pregunta" class="form-label required">Enunciado de la Pregunta</label>
            <textarea id="pregunta" 
                      name="pregunta" 
                      class="form-control" 
                      rows="3" 
                      placeholder="Escribe la pregunta completa aquí..." 
                      required><?= htmlspecialchars(old('pregunta', $question['pregunta'])) ?></textarea>
        </div>

        <!-- Alternatives list builder -->
        <div class="form-group" style="background:rgba(15,23,42,0.4); padding:24px; border:1px solid rgba(124,58,237,0.2); border-radius:var(--radius-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <label class="form-label required" style="margin-bottom: 0;">Alternativas de Respuesta (Marque la correcta)</label>
                <button type="button" id="btn-add-alternative" class="btn btn-outline" style="width: auto; padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-plus"></i> Agregar Alternativa
                </button>
            </div>

            <div id="alternatives-builder-list" class="alternatives-builder-list">
                <?php 
                $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                foreach ($alternatives as $index => $alt): 
                    $letter = $letters[$index] ?? '?';
                ?>
                    <div class="builder-item form-group">
                        <div class="correct-radio-label">
                            <input type="radio" 
                                   name="correcta" 
                                   value="<?= $index ?>" 
                                   <?= old('correcta', $alt['es_correcta'] == 1) ? 'checked' : '' ?> 
                                   required>
                            <span>Correcta</span>
                        </div>
                        <div style="flex-grow: 1; display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 800; font-size: 1.1rem; color: var(--text-muted); width: 20px;"><?= $letter ?>.</span>
                            <input type="text" 
                                   name="alternativas[]" 
                                   class="form-control" 
                                   placeholder="Texto de alternativa <?= $letter ?>" 
                                   value="<?= htmlspecialchars(old("alternativas.{$index}", $alt['alternativa'])) ?>" 
                                   required>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-alt" style="width: auto; height: 42px; display: flex; align-items: center;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Explanation / Resolution -->
        <div class="form-group" style="margin-top: 24px;">
            <label for="resolucion" class="form-label">Resolución y Explicación Detallada (Opcional)</label>
            <textarea id="resolucion" 
                      name="resolucion" 
                      class="form-control" 
                      rows="4" 
                      placeholder="Explica detalladamente los pasos para resolver esta pregunta..." 
                      ><?= htmlspecialchars(old('resolucion', $question['resolucion'])) ?></textarea>
                      
            <div style="margin-top: 15px;">
                <label for="imagen_resolucion" class="form-label">Subir Imagen de Resolución (Opcional)</label>
                <input type="file" id="imagen_resolucion" name="imagen_resolucion" class="form-control" accept="image/*">
                
                <div id="image-res-preview-container" class="image-preview-container" style="margin-top: 10px;">
                    <?php if (!empty($question['imagen_resolucion'])): ?>
                        <img src="/uploads/<?= htmlspecialchars($question['imagen_resolucion']) ?>" class="img-preview" alt="Imagen resolución actual">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Imagen actual cargada</span>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--danger-color); cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="eliminar_imagen_resolucion" value="1"> <i class="fas fa-trash-alt"></i> Eliminar imagen actual
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Score -->
        <div class="form-group">
            <label class="form-label required">Puntaje de esta Pregunta</label>
            <input type="number" name="puntaje" class="form-control"
                   min="0.1" step="0.1"
                   value="<?= htmlspecialchars(old('puntaje', $question['puntaje'] ?? 1)) ?>"
                   placeholder="Ej: 0.5, 1, 2, 4"
                   required>
            <small style="color:var(--text-subtle);display:block;margin-top:6px;">
                <i class="fas fa-info-circle"></i> La nota final = suma de puntos de preguntas respondidas correctamente.
            </small>
        </div>

        <div class="form-actions">
            <a href="/admin/preguntas" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-submit">Actualizar Pregunta</button>
        </div>
    </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
