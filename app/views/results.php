<?php 
$loadExamCss = true;
require_once 'layout/header.php'; 

// Assess performance
$score = (float)$result['puntaje'];
$total = (int)$result['total_preguntas'];
$correct = (int)$result['correctas'];
$incorrect = (int)$result['incorrectas'];

$performanceClass = 'badge-danger';
$performanceText = 'Necesita Práctica';
$messageText = 'Sigue repasando los temas. ¡La práctica hace al maestro!';

if ($score >= 14) {
    $performanceClass = 'badge-success';
    $performanceText = 'Excelente Desempeño';
    $messageText = '¡Felicitaciones! Has demostrado un dominio sobresaliente en este tema.';
} elseif ($score >= 10.5) {
    $performanceClass = 'badge-warning';
    $performanceText = 'Aprobado';
    $messageText = '¡Buen intento! Tienes bases sólidas, pero aún puedes perfeccionar algunos puntos.';
}
?>

<div class="container" style="flex-grow: 1; padding-top: 30px; padding-bottom: 50px;">
    
    <!-- Hero Score Summary Card -->
    <div class="results-hero">
        <span class="result-badge <?= $performanceClass ?>"><?= $performanceText ?></span>
        <h2 style="font-size: 2rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px;">
            ¡Resultados de <?= htmlspecialchars($result['usuario_nombre']) ?>!
        </h2>
        <p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 24px;">
            Evaluación: <strong><?= htmlspecialchars($result['categoria_nombre']) ?></strong>
        </p>

        <!-- Score Gauge Representation -->
        <div style="margin: 20px 0;">
            <div class="score-display">
                <span><?= number_format($score, 1) ?></span><span class="score-scale">/20.0</span>
            </div>
            <p style="font-size: 1rem; font-weight: 500; color: var(--text-muted);"><?= $messageText ?></p>
        </div>

        <!-- Statistics counters -->
        <div class="results-stats-row">
            <div class="stat-item">
                <span class="stat-value correct"><i class="fas fa-check-circle"></i> <?= $correct ?></span>
                <span class="stat-label">Correctas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value incorrect"><i class="fas fa-times-circle"></i> <?= $incorrect ?></span>
                <span class="stat-label">Incorrectas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: var(--primary-color);"><i class="fas fa-list-ol"></i> <?= $total ?></span>
                <span class="stat-label">Total Preguntas</span>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: center; max-width: 480px; margin: 0 auto;">
            <a href="/examen/<?= $result['categoria_id'] ?>?nombre=<?= urlencode($result['usuario_nombre']) ?>" class="btn btn-primary" style="width: auto;">
                <i class="fas fa-redo"></i> Volver a Intentar
            </a>
            <a href="/" class="btn btn-outline" style="width: auto;">
                <i class="fas fa-home"></i> Ir al Inicio
            </a>
        </div>
    </div>

    <!-- Review Section -->
    <h3 class="review-title">Revisión Detallada</h3>

    <div class="review-questions-list">
        <?php 
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($questions as $index => $q): 
            $questionId = $q['id'];
            $selectedAltId = $choices[$questionId] ?? null;

            // Analyze if user answered correct
            $isCorrect = false;
            $correctAltId = null;
            foreach ($q['alternativas'] as $alt) {
                if ($alt['es_correcta'] == 1) {
                    $correctAltId = $alt['id'];
                }
            }
            if ($selectedAltId !== null && $selectedAltId == $correctAltId) {
                $isCorrect = true;
            }

            $cardClass = $isCorrect ? 'correct' : 'incorrect';
            $statusLabelClass = $isCorrect ? 'correct' : 'incorrect';
            $statusIcon = $isCorrect ? 'fa-check' : 'fa-times';
            $statusText = $isCorrect ? 'Correcta' : ($selectedAltId === null ? 'Sin responder' : 'Incorrecta');
        ?>
            <div class="review-card <?= $cardClass ?>">
                
                <!-- Status Badge -->
                <div class="review-status-label <?= $statusLabelClass ?>">
                    <i class="fas <?= $statusIcon ?>"></i> <?= $statusText ?>
                </div>

                <!-- Question Title -->
                <h4 class="question-text" style="font-size: 1.2rem; max-width: 80%; line-height: 1.4; margin-bottom: 20px;">
                    Pregunta <?= $index + 1 ?>: <?= htmlspecialchars($q['pregunta']) ?>
                </h4>

                <!-- Optional Image -->
                <?php if (!empty($q['imagen'])): ?>
                    <div class="question-media" style="text-align: left; background: none; border: none; padding: 0;">
                        <img src="/uploads/<?= htmlspecialchars($q['imagen']) ?>" class="question-image" style="max-height: 200px;" alt="Imagen de Pregunta">
                    </div>
                <?php endif; ?>

                <!-- Alternatives List -->
                <div class="alternatives-container" style="margin-top: 15px;">
                    <?php 
                    foreach ($q['alternativas'] as $altIndex => $alt): 
                        $letter = $letters[$altIndex] ?? '?';
                        
                        $altClass = '';
                        // If it is the correct answer
                        if ($alt['id'] == $correctAltId) {
                            $altClass = 'review-correct';
                        }
                        // If it was selected by the user, but is incorrect
                        elseif ($alt['id'] == $selectedAltId) {
                            $altClass = 'review-incorrect';
                        }
                    ?>
                        <div class="alternative-card <?= $altClass ?>" style="cursor: default;">
                            <div class="alternative-letter"><?= $letter ?></div>
                            <span class="alternative-text">
                                <?= htmlspecialchars($alt['alternativa']) ?>
                                <?php if ($alt['id'] == $correctAltId): ?>
                                    <span style="color: var(--success-color); font-weight: bold; font-size: 0.85rem; margin-left: 8px;">(Respuesta Correcta)</span>
                                <?php elseif ($alt['id'] == $selectedAltId): ?>
                                    <span style="color: var(--danger-color); font-weight: bold; font-size: 0.85rem; margin-left: 8px;">(Tu Selección)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resolution Detail Box -->
                <div class="resolution-box">
                    <div class="resolution-title">
                        <i class="fas fa-lightbulb"></i> Resolución y Explicación
                    </div>
                    <div class="resolution-body">
                        <?= !empty($q['resolucion']) ? nl2br(htmlspecialchars($q['resolucion'])) : 'No se ha registrado una resolución específica para esta pregunta.' ?>
                        
                        <?php if (!empty($q['imagen_resolucion'])): ?>
                            <div style="margin-top: 15px; text-align: left;">
                                <img src="/uploads/<?= htmlspecialchars($q['imagen_resolucion']) ?>" style="max-width: 100%; max-height: 300px; border-radius: 8px;" alt="Imagen de Resolución">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once 'layout/footer.php'; ?>
