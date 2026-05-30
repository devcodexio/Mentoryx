<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Ingreso Panel Admin') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    
    <style>
        /* Modern Animated Login Styles */
        body.login-bg {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Elements */
        .bg-shape {
            position: absolute;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
            z-index: 0;
        }
        .shape1 {
            width: 400px; height: 400px;
            background: #7c3aed;
            top: -100px; left: -100px;
            border-radius: 50%;
            animation-delay: 0s;
        }
        .shape2 {
            width: 500px; height: 500px;
            background: #f97316;
            bottom: -150px; right: -150px;
            border-radius: 50%;
            animation-delay: -3s;
        }
        .shape3 {
            width: 300px; height: 300px;
            background: #06b6d4;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            animation-delay: -6s;
        }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(40px) scale(1.1); }
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 420px;
            z-index: 10;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(124, 58, 237, 0.2);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            letter-spacing: -1px;
        }
        
        .login-logo i {
            background: linear-gradient(135deg, #7c3aed, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulseGlow 2s infinite alternate;
        }

        @keyframes pulseGlow {
            0% { filter: drop-shadow(0 0 5px rgba(124,58,237,0.5)); }
            100% { filter: drop-shadow(0 0 20px rgba(249,115,22,0.8)); }
        }

        .login-subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 32px;
            font-size: 1rem;
            font-weight: 300;
        }

        /* Input Animations */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 16px 16px 16px 48px;
            background: rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #7c3aed;
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .form-control:focus + i, 
        .form-control:focus ~ i {
            color: #7c3aed;
        }

        /* Animated Button */
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #7c3aed 0%, #f97316 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            font-family: inherit;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary i {
            transition: transform 0.3s;
        }
        
        .btn-primary:hover i {
            transform: translateX(4px);
        }
    </style>
</head>
<body class="login-bg">
    <!-- Animated Shapes Background -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>

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
                <div class="input-icon-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Ingresa tu usuario" 
                           value="<?= htmlspecialchars(old('username')) ?>"
                           required 
                           autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 32px;">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Ingresa tu contraseña" 
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Iniciar Sesión <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <!-- Main JS (loads SweetAlert Toast displayer) -->
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
