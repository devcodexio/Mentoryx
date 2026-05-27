const Exam = {
    currentQuestionIndex: 0,
    totalQuestions: 0,
    answers: {},
    timerInterval: null,
    timeRemaining: 0,
    timerDurationSeconds: 0,

    init: function(totalQuestions, durationMinutes) {
        this.totalQuestions = totalQuestions;
        this.timeRemaining  = durationMinutes * 60;
        this.timerDurationSeconds = durationMinutes * 60;

        // Render first question
        this.showQuestion(0);
        this.updateProgress();

        // Start timer countdown
        this.startTimer();

        // Bind events
        this.bindEvents();
    },

    bindEvents: function() {
        const self = this;

        // Alternatives selection click
        document.querySelectorAll('.alternative-card').forEach(card => {
            card.addEventListener('click', function() {
                const questionId = this.dataset.questionId;
                const alternativeId = this.dataset.alternativeId;
                
                self.selectAlternative(questionId, alternativeId, this);
            });
        });

        // Previous Question button
        const prevBtn = document.getElementById('btn-prev');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (self.currentQuestionIndex > 0) {
                    self.showQuestion(self.currentQuestionIndex - 1);
                }
            });
        }

        // Next Question button
        const nextBtn = document.getElementById('btn-next');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (self.currentQuestionIndex < self.totalQuestions - 1) {
                    self.showQuestion(self.currentQuestionIndex + 1);
                }
            });
        }

        // Navigator dots click
        document.querySelectorAll('.nav-dot').forEach(dot => {
            dot.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                self.showQuestion(index);
            });
        });

        // Finish Exam Button
        const finishBtn = document.getElementById('btn-finish');
        if (finishBtn) {
            finishBtn.addEventListener('click', () => {
                self.confirmFinish();
            });
        }
    },

    showQuestion: function(index) {
        this.currentQuestionIndex = index;

        // Hide all questions, show target question
        document.querySelectorAll('.question-container').forEach((el, idx) => {
            if (idx === index) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        // Update Question Navigation Dots active state
        document.querySelectorAll('.nav-dot').forEach((el, idx) => {
            if (idx === index) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        // Toggle Prev/Next buttons visibility/state
        const prevBtn = document.getElementById('btn-prev');
        const nextBtn = document.getElementById('btn-next');
        const finishBtn = document.getElementById('btn-finish');

        if (prevBtn) {
            prevBtn.style.visibility = (index === 0) ? 'hidden' : 'visible';
        }

        if (nextBtn) {
            nextBtn.style.display = (index === this.totalQuestions - 1) ? 'none' : 'inline-flex';
        }

        if (finishBtn) {
            finishBtn.style.display = (index === this.totalQuestions - 1) ? 'inline-flex' : 'none';
        }

        // Update active index text in progress bar
        const activeNumEl = document.getElementById('active-question-num');
        if (activeNumEl) {
            activeNumEl.textContent = index + 1;
        }
    },

    selectAlternative: function(questionId, alternativeId, cardElement) {
        // Update local answers object
        this.answers[questionId] = alternativeId;

        // Deselect other alternatives in this question container
        const questionContainer = cardElement.closest('.question-container');
        questionContainer.querySelectorAll('.alternative-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Select clicked alternative
        cardElement.classList.add('selected');

        // Check/check radio input
        const radio = cardElement.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }

        // Mark navigation dot as answered
        const dot = document.querySelector(`.nav-dot[data-index="${this.currentQuestionIndex}"]`);
        if (dot) {
            dot.classList.add('answered');
        }

        // Update progress bar
        this.updateProgress();
    },

    updateProgress: function() {
        const answeredCount = Object.keys(this.answers).length;
        const percent = Math.round((answeredCount / this.totalQuestions) * 100);
        
        const fillEl = document.getElementById('exam-progress-fill');
        if (fillEl) {
            fillEl.style.width = percent + '%';
        }

        const answeredCountEl = document.getElementById('answered-count');
        if (answeredCountEl) {
            answeredCountEl.textContent = answeredCount;
        }
    },

    startTimer: function() {
        const self = this;
        const timerClock = document.getElementById('timer-clock');
        const timerBar   = document.getElementById('timer-bar');

        if (!timerClock) return;

        this.timerInterval = setInterval(() => {
            self.timeRemaining--;

            const minutes = Math.floor(self.timeRemaining / 60);
            const seconds = self.timeRemaining % 60;
            timerClock.textContent =
                (minutes < 10 ? '0' : '') + minutes + ':' +
                (seconds < 10 ? '0' : '') + seconds;

            // Update timer bar
            if (timerBar) {
                const pct = Math.max(0, (self.timeRemaining / self.timerDurationSeconds) * 100);
                timerBar.style.width = pct + '%';
                timerBar.style.background = pct < 20
                    ? 'linear-gradient(90deg,#ef4444,#f87171)'
                    : 'var(--grad-primary)';
            }

            if (self.timeRemaining <= 60) timerClock.classList.add('warning');

            if (self.timeRemaining <= 0) {
                clearInterval(self.timerInterval);
                self.autoSubmit();
            }
        }, 1000);
    },

    autoSubmit: function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Tiempo Agotado!',
                text: 'El límite de tiempo para resolver el examen ha expirado. Tu evaluación será calificada automáticamente.',
                icon: 'warning',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Ver Resultados',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                this.submitForm();
            });
        } else {
            alert('¡Tiempo Agotado! Calificando examen...');
            this.submitForm();
        }
    },

    confirmFinish: function() {
        const answeredCount = Object.keys(this.answers).length;
        const unansweredCount = this.totalQuestions - answeredCount;
        let warningText = '¿Estás seguro de que deseas finalizar y calificar el examen?';
        
        if (unansweredCount > 0) {
            warningText = `Aún tienes ${unansweredCount} pregunta(s) sin responder. ¿Estás seguro de que deseas finalizar y calificar el examen?`;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Finalizar Examen?',
                text: warningText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Seguir resolviendo'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitForm();
                }
            });
        } else {
            if (confirm(warningText)) {
                this.submitForm();
            }
        }
    },

    submitForm: function() {
        // Clear timer interval
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        // Show loading spinner
        if (typeof Loader !== 'undefined') {
            Loader.show('Calificando examen...');
        }

        // Submit form
        const form = document.getElementById('exam-form');
        if (form) {
            form.submit();
        }
    }
};
