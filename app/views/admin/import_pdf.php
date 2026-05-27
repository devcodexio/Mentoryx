<?php require_once dirname(__DIR__) . '/layout/admin_header.php'; ?>

<div class="admin-card">
    <h3 class="card-title" style="margin-bottom: 20px;"><i class="fas fa-file-import"></i> Importar Preguntas desde PDF</h3>

    <form action="/admin/importar-pdf" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="categoria_id">Categoría / Año</label>
            <select name="categoria_id" id="categoria_id" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="pdf_file">Archivo PDF</label>
            <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept=".pdf" required>
            <small style="color: #64748b; margin-top: 5px; display: block;">Sube un PDF que contenga las preguntas y alternativas. El sistema intentará extraer 120 preguntas.</small>
        </div>

        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Subir y Extraer</button>
            <a href="/admin/dashboard" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/layout/admin_footer.php'; ?>
