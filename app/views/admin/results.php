<?php require_once __DIR__ . '/../layout/admin_header.php'; ?>

<div class="admin-body">
    <div class="admin-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chart-line"></i>
                Todos los Resultados
            </h2>
            <div class="card-actions">
                <a href="/admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card-body">
            <?php if (!empty($results)): ?>
                <div class="results-grid">
                    <?php foreach ($results as $result): ?>
                        <div class="result-card">
                            <div class="result-card-header">
                                <div class="result-user-info">
                                    <div class="result-avatar">
                                        <?= strtoupper(mb_substr($result['usuario_nombre'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="result-username"><?= htmlspecialchars($result['usuario_nombre'] ?? 'Desconocido') ?></h4>
                                        <span class="result-time"><i class="far fa-clock"></i> <?= isset($result['created_at']) ? getTimeAgo($result['created_at']) : 'Hace un momento' ?></span>
                                    </div>
                                </div>
                                <div class="result-badge">
                                    <?= htmlspecialchars($result['categoria_nombre']) ?>
                                </div>
                            </div>
                            
                            <div class="result-card-body">
                                <div class="result-score-section">
                                    <?php 
                                        $score = $result['puntaje'];
                                        $strokeColor = $score >= 70 ? '#10b981' : ($score >= 50 ? '#F97316' : '#ef4444');
                                        $bgColor = $score >= 70 ? 'rgba(16,185,129,0.1)' : ($score >= 50 ? 'rgba(249,115,22,0.1)' : 'rgba(239,68,68,0.1)');
                                    ?>
                                    <div class="score-circle" style="background: <?= $bgColor ?>; border-color: <?= $strokeColor ?>;">
                                        <span style="color: <?= $strokeColor ?>;"><?= $score ?>%</span>
                                    </div>
                                    <div class="score-details">
                                        <div class="score-stat success">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?= $result['correctas'] ?> Correctas</span>
                                        </div>
                                        <div class="score-stat danger">
                                            <i class="fas fa-times-circle"></i>
                                            <span><?= $result['incorrectas'] ?> Incorrectas</span>
                                        </div>
                                        <div class="score-stat info">
                                            <i class="fas fa-question-circle"></i>
                                            <span><?= $result['total_preguntas'] ?> Total</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Aún no hay resultados de exámenes registrados.</p>
                    <a href="/admin/dashboard" class="btn btn-primary">Ir al Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>
