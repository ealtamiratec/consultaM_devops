<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in text-center">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
            <h1 style="font-size: 3rem; margin-bottom: 0.5rem;">404</h1>
            <p class="text-muted" style="font-size: 1.25rem; margin-bottom: 2rem;">Página no encontrada</p>
            <a href="<?= base_url('dashboard') ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>
</body>
</html>
