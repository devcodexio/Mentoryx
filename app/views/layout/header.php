<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Mentoryx | Simulador de Exámenes UNSCH') ?></title>
    <meta name="description" content="Mentoryx - El mejor simulador de exámenes de admisión para la UNSCH. Practica con los exámenes reales de años anteriores, evalúa tu nivel y revisa resoluciones detalladas al instante.">
    <meta name="keywords" content="examen de la unsch, examen de admision, simulador de examen, mentoryx, unsch, san cristobal de huamanga, preparacion universitaria, examenes resueltos unsch">
    <meta name="author" content="Mentoryx">
    <meta name="robots" content="index, follow">

    <!-- Open Graph (Facebook/WhatsApp/LinkedIn) -->
    <meta property="og:title" content="Mentoryx | Simulador de Exámenes UNSCH">
    <meta property="og:description" content="Practica con exámenes de admisión reales de la UNSCH y evalúa tu nivel con nuestro simulador.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sistemas-per.com">
    <meta property="og:site_name" content="Mentoryx">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <?php if (!empty($loadExamCss)): ?>
        <link rel="stylesheet" href="<?= asset('css/exam.css') ?>">
    <?php endif; ?>
</head>
<body>

    <!-- Loader -->
    <div id="global-loader" class="loader-overlay">
        <div class="loader-spinner"></div>
        <div class="loader-text">Cargando...</div>
    </div>

    <!-- Flash Data -->
    <div id="flash-data"
         data-success="<?= htmlspecialchars(\App\Core\Session::getFlash('success') ?? '') ?>"
         data-error="<?= htmlspecialchars(\App\Core\Session::getFlash('error') ?? '') ?>">
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="/" class="logo">
                <?php 
                    $logo = get_setting('logo'); 
                    $nombreSitio = get_setting('nombre_sitio', 'AutoEvaluación');
                ?>
                <?php if ($logo): ?>
                    <img src="/uploads/<?= htmlspecialchars($logo) ?>" alt="Logo" class="public-logo-img">
                <?php else: ?>
                    <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <?php endif; ?>
                <span><?= htmlspecialchars($nombreSitio) ?></span>
            </a>
            <button class="hamburger" id="hamburger-btn" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="nav-links">
                <a href="/" class="nav-link"><i class="fas fa-house"></i> Inicio</a>
                <a href="/admin/login" class="nav-btn-admin"><i class="fas fa-shield-halved"></i> Admin</a>
            </div>
        </div>
    </nav>

    <div style="display:flex; flex-direction:column; min-height:calc(100vh - 72px);">
