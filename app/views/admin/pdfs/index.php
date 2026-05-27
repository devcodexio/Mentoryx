<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    
    <!-- Generate PDF Card -->
    <div class="admin-card">
        <h3 class="card-title" style="margin-bottom: 20px;"><i class="fas fa-file-invoice"></i> Generar PDF por Categoría</h3>
        
        <form action="/admin/pdfs/generar" method="POST">
            <!-- CSRF Token -->
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="categoria_id" class="form-label required">Seleccione Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-control" required>
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="display: block; margin-top: 6px; color: var(--text-muted); font-size: 0.8rem; line-height: 1.3;">
                    El PDF generado se guardará de forma permanente en el servidor y contendrá todas las preguntas, imágenes, alternativas y resoluciones de la categoría.
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                <i class="fas fa-cog"></i> Generar y Guardar PDF
            </button>
        </form>
    </div>

    <!-- Generated PDFs Table Card -->
    <div class="admin-card">
        <h3 class="card-title" style="margin-bottom: 20px;"><i class="fas fa-file-pdf"></i> PDFs Históricos Guardados</h3>
        
        <form action="/admin/pdfs/bulk-descargar" method="POST" id="bulk-download-form">
            <?= csrf_field() ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Seleccione los PDFs que desea combinar y descargar.</p>
                <button type="submit" class="btn btn-primary" id="btn-bulk-download" disabled>
                    <i class="fas fa-file-archive"></i> Descargar Seleccionados en 1 PDF
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="select-all-pdfs" style="cursor: pointer;">
                            </th>
                            <th style="width: 80px;">ID</th>
                            <th>Categoría</th>
                            <th>Nombre del Archivo</th>
                            <th>Fecha de Registro</th>
                            <th style="width: 180px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pdfs)): ?>
                            <?php foreach ($pdfs as $pdf): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="pdf_ids[]" value="<?= $pdf['id'] ?>" class="pdf-checkbox" style="cursor: pointer;">
                                    </td>
                                    <td>#<?= $pdf['id'] ?></td>
                                    <td style="font-weight: 600; color: #334155;"><?= htmlspecialchars($pdf['categoria_nombre']) ?></td>
                                    <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">
                                        <?= htmlspecialchars($pdf['archivo_pdf']) ?>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;">
                                        <?= date('d/m/Y H:i', strtotime($pdf['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="justify-content: center;">
                                            <a href="/admin/pdfs/descargar/<?= $pdf['id'] ?>" class="btn btn-sm btn-primary" style="width: auto; padding: 6px 12px;" title="Descargar">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-ajax" 
                                                    data-id="<?= $pdf['id'] ?>" 
                                                    data-type="archivo PDF" 
                                                    data-url="/admin/pdfs/eliminar/<?= $pdf['id'] ?>" 
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
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                                Aún no se han generado PDFs de preguntas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCb = document.getElementById('select-all-pdfs');
    const pdfCheckboxes = document.querySelectorAll('.pdf-checkbox');
    const btnBulk = document.getElementById('btn-bulk-download');

    if(selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            pdfCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkButton();
        });
    }

    pdfCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = Array.from(pdfCheckboxes).every(c => c.checked);
            selectAllCb.checked = allChecked;
            toggleBulkButton();
        });
    });

    function toggleBulkButton() {
        const anyChecked = Array.from(pdfCheckboxes).some(c => c.checked);
        btnBulk.disabled = !anyChecked;
    }
});
</script>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
