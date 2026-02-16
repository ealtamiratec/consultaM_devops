<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Atención Médica - Consulta Externa">
    <title><?= e($title ?? 'Sistema de Consulta Médica') ?> - <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= base_url('dashboard') ?>" class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <span class="sidebar-logo-text">Consulta Médica</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($title ?? '') === 'Dashboard' ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="fas fa-home"></i></span>
                        Dashboard
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Gestión</div>
                    <a href="<?= base_url('pacientes') ?>" class="nav-link <?= strpos($title ?? '', 'Paciente') !== false ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="fas fa-users"></i></span>
                        Pacientes
                    </a>
                    <a href="<?= base_url('medicos') ?>" class="nav-link <?= strpos($title ?? '', 'Médico') !== false ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="fas fa-user-md"></i></span>
                        Médicos
                    </a>
                    <a href="<?= base_url('consultas') ?>" class="nav-link <?= strpos($title ?? '', 'Consulta') !== false ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="fas fa-stethoscope"></i></span>
                        Consultas
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Configuración</div>
                    <a href="<?= base_url('especialidades') ?>" class="nav-link <?= strpos($title ?? '', 'Especialidad') !== false ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="fas fa-tags"></i></span>
                        Especialidades
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Cuenta</div>
                    <a href="<?= base_url('logout') ?>" class="nav-link">
                        <span class="nav-link-icon"><i class="fas fa-sign-out-alt"></i></span>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div>
                    <button id="menu-toggle" class="btn btn-sm btn-outline-primary d-none-lg">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="header-title"><?= e($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="header-user">
                    <?php $currentUser = current_user(); ?>
                    <?php if ($currentUser): ?>
                    <div class="user-info">
                        <div class="user-name"><?= e($currentUser['nombre_completo']) ?></div>
                        <div class="user-role"><?= e(ucfirst($currentUser['rol'])) ?></div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser['nombre_completo'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Contenido de Página -->
            <div class="page-content">
                <?php $flash = flash(); ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> fade-in">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
