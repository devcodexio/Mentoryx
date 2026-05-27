<?php
$loadExamCss = true;
$loadExamJs  = true;
require_once 'layout/header.php';

$nombreEstudiante = htmlspecialchars($_GET['nombre'] ?? 'Estudiante Anónimo');
?>

<div class="container" style="flex-grow:1;">
    <div class="exam-layout">

        <!-- ═══ MAIN EXAM FORM ═══ -->
        <form id="exam-form" action="/examen/finalizar" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="categoria_id"    value="<?= $category['id'] ?>">
            <input type="hidden" name="usuario_nombre"  value="<?= $nombreEstudiante ?>">

            <div class="exam-card">

                <!-- Progress header -->
                <div class="exam-progress-container">
                    <div class="progress-header">
                        <span>Pregunta <span id="active-question-num">1</span> / <?= count($questions) ?></span>
                        <span><span id="answered-count">0</span> respondidas</span>
                    </div>
                    <div class="progress-track">
                        <div id="exam-progress-fill" class="progress-fill"></div>
                    </div>
                </div>

                <!-- Questions -->
                <?php foreach ($questions as $index => $q):
                    $hasText  = !empty(trim($q['pregunta'] ?? ''));
                    $hasImage = !empty($q['imagen']);
                ?>
                    <div class="question-container" data-id="<?= $q['id'] ?>" data-index="<?= $index ?>">

                        <!-- Number badge -->
                        <div class="question-number-badge">
                            <i class="fas fa-circle-question"></i>
                            Pregunta <?= $index + 1 ?>
                        </div>

                        <!-- Text (optional if there's an image) -->
                        <?php if ($hasText): ?>
                            <p class="question-text"><?= htmlspecialchars($q['pregunta']) ?></p>
                        <?php endif; ?>

                        <!-- Image-ONLY question or image + text -->
                        <?php if ($hasImage): ?>
                            <div class="question-media <?= !$hasText ? 'image-only' : '' ?>">
                                <img src="/uploads/<?= htmlspecialchars($q['imagen']) ?>"
                                     class="question-image"
                                     alt="Pregunta <?= $index + 1 ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Alternatives -->
                        <div class="alternatives-container">
                            <?php
                            $letters = ['A','B','C','D','E','F'];
                            foreach ($q['alternativas'] as $ai => $alt):
                                $letter = $letters[$ai] ?? '?';
                            ?>
                                <label class="alternative-card"
                                       data-question-id="<?= $q['id'] ?>"
                                       data-alternative-id="<?= $alt['id'] ?>">
                                    <input type="radio"
                                           class="alternative-input"
                                           name="respuestas[<?= $q['id'] ?>]"
                                           value="<?= $alt['id'] ?>">
                                    <div class="alternative-letter"><?= $letter ?></div>
                                    <span class="alternative-text"><?= htmlspecialchars($alt['alternativa']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    </div>
                <?php endforeach; ?>

                <!-- Navigation -->
                <div class="exam-navigation-buttons">
                    <button type="button" id="btn-prev" class="btn btn-ghost btn-nav" style="visibility:hidden;">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <div style="flex-grow:1;"></div>
                    <button type="button" id="btn-next" class="btn btn-primary btn-nav">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" id="btn-finish" class="btn btn-nav-finish btn-nav" style="display:none;">
                        <i class="fas fa-check-double"></i> Finalizar Examen
                    </button>
                </div>

            </div>
        </form>

        <!-- ═══ SIDEBAR ═══ -->
        <div class="exam-sidebar">

            <!-- Timer -->
            <div class="sidebar-card timer-container">
                <div class="timer-label"><i class="far fa-clock"></i> &nbsp;Tiempo restante</div>
                <div id="timer-clock" class="timer-display">20:00</div>
                <div class="timer-bar-track">
                    <div id="timer-bar" class="timer-bar-fill"></div>
                </div>
            </div>

            <!-- Navigator -->
            <div class="sidebar-card">
                <div class="navigator-header"><i class="fas fa-map-signs"></i> &nbsp;Navegación</div>
                <div class="navigator-grid">
                    <?php foreach ($questions as $index => $q): ?>
                        <button type="button"
                                class="nav-dot"
                                data-index="<?= $index ?>"
                                data-id="<?= $q['id'] ?>">
                            <?= $index + 1 ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Info card -->
            <div class="sidebar-card" style="font-size:.82rem; color:var(--text-muted); line-height:1.6;">
                <p style="font-weight:700; color:var(--text); margin-bottom:10px; font-size:.85rem;">
                    <i class="fas fa-circle-info" style="color:var(--primary-light);"></i> &nbsp;Instrucciones
                </p>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:6px;">
                    <li>• Selecciona una alternativa para responder.</li>
                    <li>• Los números verdes indican preguntas respondidas.</li>
                    <li>• Pulsa <strong style="color:var(--text);">Finalizar</strong> al terminar.</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    Exam.init(<?= count($questions) ?>, 20);
});
</script>

<?php require_once 'layout/footer.php'; ?>
