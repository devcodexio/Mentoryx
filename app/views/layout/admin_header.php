<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Panel Admin') ?> — AutoEvaluación</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">

    <style>
        /* Image preview override */
        #image-preview-container img { max-width: 160px; max-height: 100px; border-radius: 8px; object-fit: contain; }
    </style>
</head>
<body>

    <!-- Loader -->
    <div id="global-loader" class="loader-overlay">
        <div class="loader-spinner"></div>
        <div class="loader-text">Procesando...</div>
    </div>

    <!-- Flash data -->
    <div id="flash-data"
         data-success="<?= htmlspecialchars(\App\Core\Session::getFlash('success') ?? '') ?>"
         data-error="<?= htmlspecialchars(\App\Core\Session::getFlash('error') ?? '') ?>">
    </div>

    <div class="admin-wrapper">

        <!-- ═══ SIDEBAR ═══ -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <?php 
                        $logo = get_setting('logo'); 
                        $nombreSitio = get_setting('nombre_sitio', 'AutoEvaluación');
                    ?>
                    <?php if ($logo): ?>
                        <img src="/uploads/<?= htmlspecialchars($logo) ?>" alt="Logo" class="sidebar-logo-img">
                    <?php else: ?>
                        <div class="sidebar-logo-icon"><i class="fas fa-graduation-cap"></i></div>
                    <?php endif; ?>
                    <span class="sidebar-logo-text"><?= htmlspecialchars($nombreSitio) ?></span>
                </div>
                <button id="sidebar-toggle" class="sidebar-toggle-btn" title="Contraer/Expandir Sidebar"><i class="fas fa-bars"></i></button>
            </div>

            <?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>

            <div class="sidebar-section-label">Principal</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item <?= str_contains($uri, 'dashboard') || rtrim($uri,'/')==='/admin' ? 'active' : '' ?>">
                    <a href="/admin/dashboard" title="Dashboard"><i class="fas fa-chart-line"></i> <span class="sidebar-text">Dashboard</span></a>
                </li>
            </ul>

            <div class="sidebar-section-label">Contenido</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item <?= str_contains($uri, 'categorias') ? 'active' : '' ?>">
                    <a href="/admin/categorias" title="Categorías"><i class="fas fa-calendar-alt"></i> <span class="sidebar-text">Años / Categorías</span></a>
                </li>
                <li class="sidebar-item <?= str_contains($uri, 'preguntas') ? 'active' : '' ?>">
                    <a href="/admin/preguntas" title="Preguntas"><i class="fas fa-circle-question"></i> <span class="sidebar-text">Preguntas</span></a>
                </li>
                <li class="sidebar-item <?= str_contains($uri, 'importar-pdf') ? 'active' : '' ?>">
                    <a href="/admin/importar-pdf" title="Importar de PDF"><i class="fas fa-file-import"></i> <span class="sidebar-text">Importar de PDF</span></a>
                </li>
                <li class="sidebar-item <?= str_contains($uri, 'resultados') ? 'active' : '' ?>">
                    <a href="/admin/resultados" title="Resultados"><i class="fas fa-poll"></i> <span class="sidebar-text">Resultados</span></a>
                </li>
            </ul>

            <div class="sidebar-section-label">Otros</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item <?= str_contains($uri, 'pdfs') ? 'active' : '' ?>">
                    <a href="/admin/pdfs" title="PDFs"><i class="fas fa-file-pdf"></i> <span class="sidebar-text">PDFs</span></a>
                </li>
                <li class="sidebar-item <?= str_contains($uri, 'configuracion') ? 'active' : '' ?>">
                    <a href="/admin/configuracion" title="Configuración"><i class="fas fa-cogs"></i> <span class="sidebar-text">Configuración</span></a>
                </li>
                <li class="sidebar-item <?= str_contains($uri, 'perfil') ? 'active' : '' ?>">
                    <a href="/admin/perfil" title="Mi Perfil"><i class="fas fa-user-edit"></i> <span class="sidebar-text">Mi Perfil</span></a>
                </li>
                <li class="sidebar-item">
                    <a href="/" target="_blank" title="Ver Web"><i class="fas fa-arrow-up-right-from-square"></i> <span class="sidebar-text">Ver Web</span></a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="/admin/logout" class="sidebar-logout" title="Cerrar Sesión">
                    <i class="fas fa-right-from-bracket"></i> <span class="sidebar-text">Cerrar Sesión</span>
                </a>
            </div>
        </aside>

        <!-- ═══ MAIN CONTENT ═══ -->
        <div class="admin-content">
            <!-- Mobile overlay -->
            <div class="mobile-overlay" id="mobile-overlay"></div>

            <header class="admin-navbar">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" title="Menú">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="admin-nav-title">
                        <i class="fas fa-chevron-right" style="color:rgba(255,255,255,0.4);font-size:.8rem;margin-right:8px;"></i>
                        <?= htmlspecialchars($title ?? 'Dashboard') ?>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div class="admin-search">
                        <input type="text" class="admin-search-input" placeholder="Buscar...">
                        <i class="fas fa-search admin-search-icon"></i>
                    </div>
                    <div class="admin-notifications">
                        <button class="notification-btn" id="notification-toggle" title="Notificaciones">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notification-count"><?= count($recentResults ?? []) ?></span>
                        </button>
                        <div class="notification-dropdown" id="notification-dropdown">
                            <div class="notification-header">
                                <h4>Usuarios que realizaron exámenes</h4>
                                <button class="mark-all-read" id="mark-all-read">Marcar todas como leídas</button>
                            </div>
                            <div class="notification-list" id="notification-list">
                                <?php
                                $resultModel = new \App\Models\Result();
                                $recentResults = $resultModel->getLatest(5);
                                
                                if (!empty($recentResults)):
                                    foreach ($recentResults as $index => $result):
                                        $timeAgo = getTimeAgo($result['created_at'] ?? 'now');
                                        $scoreColor = $result['puntaje'] >= 70 ? '#10b981' : ($result['puntaje'] >= 50 ? '#F97316' : '#ef4444');
                                ?>
                                    <div class="notification-item unread" data-notification-id="<?= $result['id'] ?>">
                                        <div class="notification-avatar">
                                            <?= strtoupper(mb_substr($result['usuario_nombre'] ?? '?', 0, 1)) ?>
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-user"><?= htmlspecialchars($result['usuario_nombre'] ?? 'Desconocido') ?></span>
                                                <span class="notification-time"><?= $timeAgo ?></span>
                                            </div>
                                            <div class="notification-desc">
                                                Completó el examen: <strong><?= htmlspecialchars($result['categoria_nombre']) ?></strong>
                                            </div>
                                            <div class="notification-meta">
                                                <?php 
                                                    $badgeClass = $result['puntaje'] >= 70 ? 'success' : ($result['puntaje'] >= 50 ? 'warning' : 'danger');
                                                ?>
                                                <span class="score-badge-sm <?= $badgeClass ?>">
                                                    <?= $result['puntaje'] ?>%
                                                </span>
                                                <span class="meta-item"><i class="fas fa-check"></i> <?= $result['correctas'] ?></span>
                                                <span class="meta-item"><i class="fas fa-times"></i> <?= $result['incorrectas'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <div class="notification-item" style="cursor: default;">
                                        <div class="notification-icon notification-icon-info">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="notification-text">Aún no hay resultados</p>
                                            <span class="notification-time">-</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer">
                                <a href="/admin/resultados" class="view-all-notifications">Ver todos los resultados</a>
                            </div>
                        </div>
                    </div>
                    <div class="admin-user-info" style="gap: 8px;">
                        <?php
                            $adminData = \App\Core\Session::get('admin');
                            $nombre = $adminData['nombre'] ?? $adminData['username'] ?? 'Admin';
                            if (!empty($adminData['foto'])):
                        ?>
                            <img src="/uploads/<?= htmlspecialchars($adminData['foto']) ?>" alt="User" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-circle-user" style="font-size: 1.2rem;"></i>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($nombre) ?></span>
                    </div>
                </div>
            </header>

            <main class="admin-body">
