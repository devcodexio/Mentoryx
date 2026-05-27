<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_header.php'; ?>

<div class="admin-card" style="max-width: 700px; margin: 0 auto;">
    <h3 class="card-title" style="margin-bottom: 24px;">
        <i class="fas fa-cogs"></i> Configuración Global del Sistema
    </h3>

    <form action="/admin/configuracion" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label required">Nombre del Sitio</label>
                <input type="text" name="nombre_sitio" class="form-control" 
                       value="<?= htmlspecialchars($settings['nombre_sitio'] ?? 'AutoEvaluación') ?>" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Logo del Sitio <span style="color:var(--text-subtle);font-weight:400;">(Recomendado: PNG fondo transparente)</span></label>
                <?php if (!empty($settings['logo'])): ?>
                    <div style="margin-bottom: 12px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: var(--radius-sm); border: 1px solid var(--border); display:inline-block;">
                        <img src="/uploads/<?= htmlspecialchars($settings['logo']) ?>" style="height: 40px; object-fit: contain;">
                    </div>
                <?php endif; ?>
                <input type="file" name="logo_file" class="form-control" accept="image/*">
                <small style="color:var(--text-subtle); display:block; margin-top:6px;">Si no subes nada, se mantendrá el logo actual (o el ícono por defecto).</small>
            </div>

            <div class="form-group">
                <label class="form-label">Correo de Contacto</label>
                <input type="email" name="correo" class="form-control" 
                       value="<?= htmlspecialchars($settings['correo'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Número de Celular (WhatsApp)</label>
                <input type="text" name="celular" class="form-control" 
                       value="<?= htmlspecialchars($settings['celular'] ?? '') ?>"
                       placeholder="Ej. 51994269463">
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Mensaje por defecto (WhatsApp PDF)</label>
                <input type="text" name="whatsapp_msg" class="form-control" 
                       value="<?= htmlspecialchars($settings['whatsapp_msg'] ?? 'Hola, me interesa el banco de preguntas en PDF del examen:') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Enlace de Facebook</label>
                <input type="url" name="facebook" class="form-control" 
                       value="<?= htmlspecialchars($settings['facebook'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Enlace de TikTok</label>
                <input type="url" name="tiktok" class="form-control" 
                       value="<?= htmlspecialchars($settings['tiktok'] ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Configuración
            </button>
        </div>
    </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/layout/admin_footer.php'; ?>
