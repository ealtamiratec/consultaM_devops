<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - Sistema de Consulta Médica</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-container { text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: bold; color: #ef4444; line-height: 1; }
        .error-title { font-size: 1.5rem; color: #1e293b; margin: 1rem 0; }
        .error-message { color: #64748b; margin-bottom: 2rem; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #2563eb; color: white; text-decoration: none; border-radius: 0.5rem; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1 class="error-title">Error del Servidor</h1>
        <p class="error-message">Ha ocurrido un error interno. Por favor, intente más tarde.</p>
        <a href="/" class="btn">Volver al Inicio</a>
    </div>
</body>
</html>
