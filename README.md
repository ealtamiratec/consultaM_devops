# Sistema de Consulta Médica Externa - MVP

Sistema de gestión de atenciones médicas de consulta externa desarrollado con PHP, MySQL, HTML, CSS y JavaScript, siguiendo la arquitectura MVC.

## Características Principales

- **Gestión de Pacientes**: Registro completo con historia clínica, datos personales, antecedentes médicos y contacto de emergencia
- **Gestión de Médicos**: Registro de médicos con especialidades y horarios de atención
- **Consultas Médicas**: Programación, atención y seguimiento de consultas con signos vitales
- **Dashboard**: Panel de control con estadísticas y consultas del día
- **Seguridad**: Autenticación, protección CSRF, validación de datos, sesiones seguras

## Requisitos del Sistema

- PHP 8.0 o superior
- MySQL 5.7 o superior / MariaDB 10.3+
- Servidor web (Apache con mod_rewrite o PHP built-in server)
- Extensiones PHP: PDO, pdo_mysql, mbstring, json

## Instalación

### 1. Clonar/Copiar el proyecto

```bash
# Copiar el directorio del proyecto a su ubicación deseada
cp -r consulta_medica /var/www/html/
# O para desarrollo local
cp -r consulta_medica ~/proyectos/
```

### 2. Crear la Base de Datos

```bash
# Acceder a MySQL como root
mysql -u root -p

# Ejecutar el script SQL
source /ruta/al/proyecto/sql/database.sql
```

O directamente:
```bash
mysql -u root -p < sql/database.sql
```

### 3. Crear Usuario de Base de Datos

```sql
CREATE USER 'consulta_app'@'localhost' IDENTIFIED BY 'TuContraseñaSegura';
GRANT ALL PRIVILEGES ON consulta_medica.* TO 'consulta_app'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Configurar la Aplicación

Editar `config/database.php`:
```php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'consulta_medica',
    'username' => 'consulta_app',
    'password' => 'TuContraseñaSegura',
    // ...
];
```

Editar `config/app.php`:
```php
return [
    'base_url' => 'http://localhost:8080', // Ajustar según tu configuración
    // ...
];
```

### 5. Configurar Permisos

```bash
chmod 755 -R /ruta/al/proyecto
chmod 777 -R /ruta/al/proyecto/logs
```

### 6. Iniciar el Servidor

**Opción A - Servidor PHP integrado (desarrollo):**
```bash
cd consulta_medica/public
php -S localhost:8080
```

**Opción B - Apache (producción):**
Configurar VirtualHost apuntando al directorio `public/`

## Credenciales de Acceso

- **Usuario:** admin
- **Contraseña:** password

> ⚠️ **Importante:** Cambiar estas credenciales en producción

## Estructura del Proyecto

```
consulta_medica/
├── app/
│   ├── controllers/     # Controladores MVC
│   ├── models/          # Modelos de datos
│   └── views/           # Vistas (templates PHP)
├── config/
│   ├── app.php          # Configuración general
│   ├── database.php     # Configuración de BD
│   └── routes.php       # Definición de rutas
├── core/
│   ├── bootstrap.php    # Inicialización
│   ├── Controller.php   # Controlador base
│   ├── Database.php     # Conexión PDO
│   ├── Model.php        # Modelo base
│   ├── Router.php       # Enrutador
│   ├── Security.php     # Funciones de seguridad
│   ├── Session.php      # Manejo de sesiones
│   └── Validator.php    # Validación de datos
├── logs/                # Archivos de log
├── public/
│   ├── css/             # Estilos CSS
│   ├── js/              # JavaScript
│   ├── index.php        # Punto de entrada
│   └── .htaccess        # Configuración Apache
├── sql/
│   └── database.sql     # Script de base de datos
└── README.md
```

## Módulos del Sistema

### Pacientes
- Registro con número de historia clínica automático
- Datos personales completos
- Información médica (grupo sanguíneo, alergias, antecedentes)
- Contacto de emergencia
- Historial de consultas

### Médicos
- Código de médico automático
- Número de colegiatura
- Especialidad médica
- Horarios de atención
- Estadísticas de consultas

### Consultas
- Número de consulta automático
- Programación de citas
- Registro de signos vitales (PA, FC, FR, T°, peso, talla, IMC, SpO2)
- Evaluación médica (síntomas, examen físico)
- Diagnóstico y tratamiento
- Estados: Programada, En espera, En atención, Atendida, Cancelada

### Especialidades
- Catálogo de especialidades médicas
- Asociación con médicos

## Seguridad Implementada

1. **Autenticación**
   - Contraseñas hasheadas con bcrypt
   - Sesiones seguras con regeneración de ID
   - Protección contra fuerza bruta

2. **Protección CSRF**
   - Tokens únicos por sesión
   - Validación en todos los formularios POST

3. **Validación de Datos**
   - Sanitización de entradas
   - Validación en servidor
   - Prepared statements (PDO)

4. **Cabeceras de Seguridad**
   - X-Frame-Options
   - X-Content-Type-Options
   - X-XSS-Protection
   - Content-Security-Policy

## API Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/pacientes/buscar?q= | Buscar pacientes |
| GET | /api/medicos/especialidad/{id} | Médicos por especialidad |
| GET | /api/estadisticas | Estadísticas generales |

## Personalización

### Cambiar Tema de Colores
Editar las variables CSS en `public/css/style.css`:
```css
:root {
    --primary-color: #2563eb;
    --success-color: #10b981;
    --danger-color: #ef4444;
    /* ... */
}
```

### Agregar Nuevas Rutas
Editar `config/routes.php`:
```php
$router->get('nueva-ruta', ['ControllerName', 'methodName']);
$router->post('nueva-ruta', ['ControllerName', 'methodName']);
```

## Solución de Problemas

### Error de conexión a base de datos
- Verificar credenciales en `config/database.php`
- Verificar que MySQL esté corriendo
- Verificar permisos del usuario MySQL

### Página en blanco
- Verificar logs en `logs/`
- Habilitar display_errors en desarrollo
- Verificar permisos de archivos

### Error 404 en todas las rutas
- Verificar configuración de mod_rewrite
- Verificar archivo .htaccess
- Verificar base_url en config/app.php

## Licencia

Este proyecto es de código abierto y puede ser utilizado y modificado libremente.

## Soporte

Para reportar problemas o sugerencias, crear un issue en el repositorio del proyecto.

---

**Versión:** 1.0.0  
**Última actualización:** Enero 2026
