<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Consulta Médica</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1 class="login-title">HOSPITAL BASICO</h1>
                <p class="login-subtitle">Sistema de Atención Integral de Salud</p>
            </div>

            <?php $flash = flash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST" data-validate>
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="username" class="form-label required">Usuario</label>
                    <div style="position: relative;">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="Ingrese su usuario"
                            required
                            autocomplete="username"
                            style="padding-left: 2.5rem;"
                        >
                        <i class="fas fa-user" style="position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label required">Contraseña</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Ingrese su contraseña"
                            required
                            autocomplete="current-password"
                            style="padding-left: 2.5rem;"
                        >
                        <i class="fas fa-lock" style="position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted" style="font-size: 0.875rem;">
                    <strong>Usuario demo:</strong> admin<br>
                    <strong>Contraseña:</strong> password
                </p>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
