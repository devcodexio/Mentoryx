<?php require_once dirname(__DIR__) . '/layout/admin_header.php'; ?>

<!-- Statistics grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Categorías</h3>
            <p><?= $totalCategories ?></p>
        </div>
        <div class="stat-icon stat-purple">
            <i class="fas fa-folder"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Preguntas</h3>
            <p><?= $totalQuestions ?></p>
        </div>
        <div class="stat-icon stat-indigo">
            <i class="fas fa-question-circle"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>PDFs Registrados</h3>
            <p><?= $totalPdfs ?></p>
        </div>
        <div class="stat-icon stat-cyan">
            <i class="fas fa-file-pdf"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Evaluaciones Realizadas</h3>
            <p><?= $stats['total_intentos'] ?? 0 ?></p>
        </div>
        <div class="stat-icon stat-green">
            <i class="fas fa-user-check"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
    
    <!-- Recent Attempts Card -->
    <div class="admin-card">
        <h3 class="card-title" style="margin-bottom: 20px;"><i class="fas fa-history"></i> Evaluaciones Recientes</h3>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Examen</th>
                        <th>Puntaje</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentResults)): ?>
                        <?php foreach ($recentResults as $res): ?>
                            <tr>
                                <td style="font-weight: 600; color: #334155;">
                                    <?= htmlspecialchars($res['usuario_nombre']) ?>
                                </td>
                                <td><?= htmlspecialchars($res['categoria_nombre']) ?></td>
                                <td>
                                    <?php 
                                        $sc = (float)$res['puntaje'];
                                        $bdClass = 'badge-danger';
                                        if ($sc >= 14) $bdClass = 'badge-success';
                                        elseif ($sc >= 10.5) $bdClass = 'badge-warning';
                                    ?>
                                    <span class="result-badge <?= $bdClass ?>" style="padding: 2px 8px; font-size: 0.75rem; margin-bottom: 0;">
                                        <?= number_format($sc, 1) ?> / 20.0
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    <?= date('d/m/Y H:i', strtotime($res['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                                Aún no se han registrado intentos de exámenes en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Metrics Breakdown Card -->
    <div class="admin-card">
        <h3 class="card-title" style="margin-bottom: 20px;"><i class="fas fa-percentage"></i> Desempeño Promedio</h3>
        
        <?php 
            $promedio = (float)($stats['promedio_puntaje'] ?? 0);
            $totalResp = (int)($stats['total_correctas'] ?? 0) + (int)($stats['total_incorrectas'] ?? 0);
            $correctPercent = $totalResp > 0 ? round(( (int)$stats['total_correctas'] / $totalResp ) * 100) : 0;
        ?>

        <div style="text-align: center; padding: 20px 0;">
            <div style="font-size: 3rem; font-weight: 800; color: var(--primary-color);">
                <?= number_format($promedio, 1) ?><span style="font-size: 1.2rem; color: var(--text-muted);">/20.0</span>
            </div>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 30px;">Calificación promedio global</p>

            <div style="margin-bottom: 12px; display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #475569;">
                <span>Respuestas Correctas</span>
                <span><?= $correctPercent ?>%</span>
            </div>
            <div class="progress-track" style="height: 12px; background: #e2e8f0; border-radius: 6px;">
                <div class="progress-fill" style="width: <?= $correctPercent ?>%; background: var(--success-color); border-radius: 6px;"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); margin-top: 20px;">
                <span><i class="fas fa-check-circle" style="color: var(--success-color);"></i> Correctas: <?= $stats['total_correctas'] ?? 0 ?></span>
                <span><i class="fas fa-times-circle" style="color: var(--danger-color);"></i> Incorrectas: <?= $stats['total_incorrectas'] ?? 0 ?></span>
            </div>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__) . '/layout/admin_footer.php'; ?>
