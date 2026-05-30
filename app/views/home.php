<?php require_once 'layout/header.php'; ?>

<div style="flex-grow: 1; padding-bottom: 60px;">

    <!-- HERO SECTION -->
    <div class="hero-section">
        <!-- Hero Background Image -->
        <div class="hero-bg-image"></div>
        
        <!-- Decorative Elements -->
        <div class="hero-decoration hero-decoration-1"></div>
        <div class="hero-decoration hero-decoration-2"></div>
        <div class="hero-decoration hero-decoration-3"></div>
        <div class="hero-decoration hero-decoration-4"></div>
        
        <!-- Floating Icons -->
        <div class="floating-icon floating-icon-1"><i class="fas fa-book"></i></div>
        <div class="floating-icon floating-icon-2"><i class="fas fa-pencil-alt"></i></div>
        <div class="floating-icon floating-icon-3"><i class="fas fa-lightbulb"></i></div>
        <div class="floating-icon floating-icon-4"><i class="fas fa-trophy"></i></div>
        
        <div class="container" style="position: relative; z-index: 1;">

            <div class="hero-badge">
                <i class="fas fa-bolt"></i> Plataforma de Evaluación
            </div>

            <h1 class="hero-title">
                Prepárate para el<br>
                <span class="gradient-text">Examen de Admisión UNSCH</span>
            </h1>

            <p class="hero-subtitle">
                <strong>Mentoryx</strong> es el mejor simulador de exámenes. Practica con los exámenes de admisión reales de la <strong>UNSCH</strong> de años anteriores. Responde, califica y aprueba.
            </p>

            <div class="hero-cta">
                <a href="#exams" class="btn btn-hero-cta">
                    <i class="fas fa-rocket"></i> Comenzar Ahora
                </a>
            </div>

            <?php if (!empty($categories)): ?>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value"><?= count($categories) ?></span>
                    <span class="hero-stat-label">Exámenes</span>
                </div>
                <div class="hero-stat" style="border-left: 1px solid rgba(255,255,255,0.07); border-right: 1px solid rgba(255,255,255,0.07); padding: 0 40px;">
                    <span class="hero-stat-value">100</span>
                    <span class="hero-stat-label">Preguntas por año</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">∞</span>
                    <span class="hero-stat-label">Intentos libres</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CATEGORIES SECTION -->
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt" style="color: var(--primary-light); font-size: 1rem; margin-right: 8px;"></i>
                Exámenes de Admisión UNSCH
            </h2>
            <span class="section-count"><?= count($categories ?? []) ?> disponibles</span>
        </div>

        <!-- Search Bar -->
        <div class="search-wrapper">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="category-search" class="search-input" placeholder="Buscar exámenes por año...">
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>
        </div>

        <div class="categories-grid">
            <?php if (!empty($categories)): ?>
                <?php 
                $iconClasses = ['icon-indigo', 'icon-violet', 'icon-cyan', 'icon-amber', 'icon-emerald'];
                $icons       = ['fa-scroll', 'fa-graduation-cap', 'fa-book-open', 'fa-award', 'fa-star'];
                foreach ($categories as $i => $cat): 
                    $iconClass = $iconClasses[$i % count($iconClasses)];
                    $icon      = $icons[$i % count($icons)];
                ?>
                    <div class="category-card" id="cat-<?= $cat['id'] ?>">
                        <div class="shine-effect"></div>
                        <div class="card-image">
                            <div class="card-image-bg <?= $iconClass ?>"></div>
                            <div class="card-image-overlay"></div>
                            <div class="card-icon-wrap <?= $iconClass ?>">
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                        </div>

                        <div class="card-body">
                            <h3 class="category-name"><?= htmlspecialchars($cat['nombre']) ?></h3>
                            <p class="category-desc"><?= htmlspecialchars($cat['descripcion'] ?? 'Examen de admisión oficial. Responde las 100 preguntas y revisa tu puntaje al instante.') ?></p>
                        </div>

                        <div class="card-footer">
                            <!-- Start Exam -->
                            <button class="btn btn-primary btn-start-exam"
                                    data-id="<?= $cat['id'] ?>"
                                    data-name="<?= htmlspecialchars($cat['nombre']) ?>">
                                <i class="fas fa-play"></i> Iniciar Examen
                            </button>

                            <!-- WhatsApp only — no download -->
                            <button class="btn btn-wa btn-whatsapp"
                                    data-name="<?= htmlspecialchars($cat['nombre']) ?>">
                                <i class="fab fa-whatsapp"></i> Descargar Preguntas PDF
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Aún no hay exámenes disponibles. El administrador debe agregar las categorías.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ─── SEARCH/FILTER FUNCTIONALITY ─── */
    const searchInput = document.getElementById('category-search');
    const searchSuggestions = document.getElementById('search-suggestions');
    const categoryCards = document.querySelectorAll('.category-card');
    const categories = [];

    // Store category data for filtering
    categoryCards.forEach(card => {
        const name = card.querySelector('.category-name').textContent.toLowerCase();
        const desc = card.querySelector('.category-desc').textContent.toLowerCase();
        const id = card.id;
        categories.push({ id, name, desc, element: card });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // Filter cards
            categories.forEach(cat => {
                const matches = cat.name.includes(searchTerm) || cat.desc.includes(searchTerm);
                
                if (matches || searchTerm === '') {
                    cat.element.style.display = 'block';
                    cat.element.style.animation = 'fadeIn 0.3s ease';
                } else {
                    cat.element.style.display = 'none';
                }
            });

            // Update suggestions
            if (searchTerm.length > 0) {
                const matches = categories.filter(cat => 
                    cat.name.includes(searchTerm) || cat.desc.includes(searchTerm)
                );
                
                if (matches.length > 0) {
                    searchSuggestions.innerHTML = matches.map(cat => `
                        <div class="search-suggestion-item" data-id="${cat.id}">
                            <div class="suggestion-title">${cat.element.querySelector('.category-name').textContent}</div>
                            <div class="suggestion-desc">${cat.element.querySelector('.category-desc').textContent}</div>
                        </div>
                    `).join('');
                    searchSuggestions.classList.add('active');
                } else {
                    searchSuggestions.innerHTML = '<div class="search-suggestion-item" style="cursor:default;"><div class="suggestion-title">No se encontraron resultados</div></div>';
                    searchSuggestions.classList.add('active');
                }
            } else {
                searchSuggestions.classList.remove('active');
            }
        });

        // Handle suggestion clicks
        searchSuggestions.addEventListener('click', function(e) {
            const item = e.target.closest('.search-suggestion-item');
            if (item && item.dataset.id) {
                const card = document.getElementById(item.dataset.id);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.style.animation = 'pulse 0.5s ease';
                    setTimeout(() => {
                        card.style.animation = '';
                    }, 500);
                }
                searchSuggestions.classList.remove('active');
                searchInput.value = '';
            }
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.classList.remove('active');
            }
        });
    }

    /* ─── NAVBAR SCROLL EFFECT ─── */
    const navbar = document.querySelector('.navbar');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });

    /* ─── MOUSE TRACKING FOR CARDS ─── */
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            card.style.setProperty('--mouse-x', `${x}%`);
            card.style.setProperty('--mouse-y', `${y}%`);
        });
    });

    /* ─── START EXAM ─── */
    document.querySelectorAll('.btn-start-exam').forEach(btn => {
        btn.addEventListener('click', function () {
            const categoryId   = this.dataset.id;
            const categoryName = this.dataset.name;

            if (typeof Swal === 'undefined') {
                const nombre = prompt('Ingresa tu nombre:') || 'Estudiante';
                window.location.href = `/examen/${categoryId}?nombre=${encodeURIComponent(nombre)}`;
                return;
            }

            Swal.fire({
                title: '¡Listo para el examen!',
                html: `<p style="color:#94a3b8;margin-bottom:16px;">Examen: <strong style="color:#e2e8f0">${categoryName}</strong></p>
                       <p style="color:#94a3b8;font-size:.9rem;margin-bottom:0;">Ingresa tu nombre para registrar tu resultado:</p>`,
                input: 'text',
                inputPlaceholder: 'Tu nombre completo...',
                background: '#1a1a2e',
                color: '#363636',
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#475569',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-play"></i> Comenzar',
                cancelButtonText: 'Cancelar',
                inputValidator: v => (!v || !v.trim()) ? '¡Debes ingresar tu nombre!' : null
            }).then(result => {
                if (result.isConfirmed) {
                    if (typeof Loader !== 'undefined') Loader.show('Cargando examen...');
                    window.location.href = `/examen/${categoryId}?nombre=${encodeURIComponent(result.value.trim())}`;
                }
            });
        });
    });

    /* ─── WHATSAPP ONLY (no download) ─── */
    const waPhone = <?= json_encode(get_setting('celular', '51994269463')) ?>;
    const waMsgPrefix = <?= json_encode(get_setting('whatsapp_msg', 'Hola, me interesa el banco de preguntas en PDF del examen:')) ?>;

    document.querySelectorAll('.btn-whatsapp').forEach(btn => {
        btn.addEventListener('click', function () {
            const categoryName = this.dataset.name;
            const msg    = encodeURIComponent(`${waMsgPrefix} "${categoryName}". ¿Pueden enviármelo?`);
            window.open(`https://wa.me/${waPhone}?text=${msg}`, '_blank');
        });
    });

    /* ─── HAMBURGER MENU TOGGLE ─── */
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navLinks = document.getElementById('nav-links');
    
    if (hamburgerBtn && navLinks) {
        hamburgerBtn.addEventListener('click', () => {
            hamburgerBtn.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Close menu when clicking on a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                hamburgerBtn.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburgerBtn.contains(e.target) && !navLinks.contains(e.target)) {
                hamburgerBtn.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });
    }

});
</script>

<?php require_once 'layout/footer.php'; ?>
