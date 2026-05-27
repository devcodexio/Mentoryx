<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Ingreso Panel Admin') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login-bg">

    <!-- Flash notifications wrapper -->
    <div id="flash-data" 
         data-success="<?= htmlspecialchars(\App\Core\Session::getFlash('success') ?? '') ?>" 
         data-error="<?= htmlspecialchars(\App\Core\Session::getFlash('error') ?? '') ?>">
    </div>

    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-graduation-cap"></i> AutoEvaluación
        </div>
        <p class="login-subtitle">Ingreso al Panel de Control</p>

        <form action="/admin/login" method="POST">
            <!-- CSRF Field -->
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Usuario</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 16px; top: 14px; color: rgba(255,255,255,0.4);"></i>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           style="padding-left: 45px;" 
                           placeholder="Ingresa tu usuario" 
                           value="<?= htmlspecialchars(old('username')) ?>"
                           required 
                           autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password" class="form-label">Contraseña</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 16px; top: 14px; color: rgba(255,255,255,0.4);"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           style="padding-left: 45px;" 
                           placeholder="Ingresa tu contraseña" 
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
    </div>

    <!-- Main JS (loads SweetAlert Toast displayer) -->
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
