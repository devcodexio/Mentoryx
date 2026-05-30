<?php require_once dirname(__DIR__) . '/layout/admin_header.php'; ?>

<div class="card-header-flex">
    <h2 class="card-title"><i class="fas fa-user-circle"></i> Mi Perfil</h2>
</div>

<div class="admin-card">
    <form action="/admin/perfil" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group" style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 300px;">
                <div class="form-group">
                    <label for="username" class="form-label">Usuario (Login)</label>
                    <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="nombre" class="form-label required">Nombre a Mostrar</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($admin['nombre'] ?? 'Administrador') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Nueva Contraseña <small style="color:rgba(255,255,255,0.4); text-transform:none;">(Dejar en blanco para no cambiar)</small></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                </div>
            </div>

            <div style="width: 250px; flex-shrink: 0; background: rgba(15,23,42,0.4); border: 1px solid rgba(124,58,237,0.2); padding: 20px; border-radius: var(--radius-lg); text-align: center;">
                <label class="form-label">Foto de Perfil</label>
                <div style="margin-bottom: 16px;" id="profile-preview-container">
                    <?php if (!empty($admin['foto'])): ?>
                        <img id="profile-preview" src="/uploads/<?= htmlspecialchars($admin['foto']) ?>" alt="Foto" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #7c3aed; box-shadow: 0 4px 12px rgba(124,58,237,0.3); margin: 0 auto; display: block;">
                    <?php else: ?>
                        <div id="profile-placeholder" style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #f97316); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: 0 4px 12px rgba(124,58,237,0.3); margin: 0 auto;">
                            <?= strtoupper(mb_substr($admin['nombre'] ?? $admin['username'], 0, 1)) ?>
                        </div>
                        <img id="profile-preview" src="#" alt="Foto" style="display: none; width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #7c3aed; box-shadow: 0 4px 12px rgba(124,58,237,0.3); margin: 0 auto;">
                    <?php endif; ?>
                </div>
                <input type="file" name="foto" id="foto" accept="image/*" class="form-control" style="font-size: 0.8rem; padding: 8px;">
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
        </div>
    </form>
</div>

<script>
document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('profile-preview');
            const placeholder = document.getElementById('profile-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            if (preview) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once dirname(__DIR__) . '/layout/admin_footer.php'; ?>
