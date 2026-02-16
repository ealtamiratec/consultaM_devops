-- =====================================================
-- Sistema de Atención Médica - Consulta Externa
-- Base de Datos MySQL
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS consulta_medica 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE consulta_medica;

-- =====================================================
-- Tabla de Usuarios (para autenticación)
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    rol ENUM('admin', 'medico', 'recepcionista') NOT NULL DEFAULT 'recepcionista',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    token_recuperacion VARCHAR(100) NULL,
    token_expiracion DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Pacientes
-- =====================================================
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_historia VARCHAR(20) NOT NULL UNIQUE,
    tipo_documento ENUM('DNI', 'CE', 'Pasaporte', 'Otro') NOT NULL DEFAULT 'DNI',
    numero_documento VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('M', 'F', 'Otro') NOT NULL,
    estado_civil ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Conviviente') NULL,
    direccion VARCHAR(255) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    grupo_sanguineo VARCHAR(5) NULL,
    alergias TEXT NULL,
    antecedentes TEXT NULL,
    contacto_emergencia VARCHAR(100) NULL,
    telefono_emergencia VARCHAR(20) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numero_historia (numero_historia),
    INDEX idx_documento (tipo_documento, numero_documento),
    INDEX idx_nombres (nombres, apellido_paterno)
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Especialidades Médicas
-- =====================================================
CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Médicos
-- =====================================================
CREATE TABLE IF NOT EXISTS medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    codigo_medico VARCHAR(20) NOT NULL UNIQUE,
    numero_colegiatura VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NULL,
    especialidad_id INT NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    horario_atencion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE RESTRICT,
    INDEX idx_codigo_medico (codigo_medico),
    INDEX idx_colegiatura (numero_colegiatura),
    INDEX idx_especialidad (especialidad_id)
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Consultas Médicas (Atenciones)
-- =====================================================
CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_consulta VARCHAR(20) NOT NULL UNIQUE,
    paciente_id INT NOT NULL,
    medico_id INT NOT NULL,
    fecha_consulta DATE NOT NULL,
    hora_consulta TIME NOT NULL,
    tipo_consulta ENUM('Primera vez', 'Control', 'Emergencia', 'Referencia') NOT NULL DEFAULT 'Primera vez',
    estado ENUM('Programada', 'En espera', 'En atención', 'Atendida', 'Cancelada', 'No asistió') NOT NULL DEFAULT 'Programada',
    motivo_consulta TEXT NOT NULL,
    sintomas TEXT NULL,
    examen_fisico TEXT NULL,
    diagnostico TEXT NULL,
    tratamiento TEXT NULL,
    observaciones TEXT NULL,
    proxima_cita DATE NULL,
    usuario_registro_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_numero_consulta (numero_consulta),
    INDEX idx_paciente (paciente_id),
    INDEX idx_medico (medico_id),
    INDEX idx_fecha (fecha_consulta),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Signos Vitales
-- =====================================================
CREATE TABLE IF NOT EXISTS signos_vitales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    presion_sistolica INT NULL,
    presion_diastolica INT NULL,
    frecuencia_cardiaca INT NULL,
    frecuencia_respiratoria INT NULL,
    temperatura DECIMAL(4,2) NULL,
    peso DECIMAL(5,2) NULL,
    talla DECIMAL(4,2) NULL,
    imc DECIMAL(4,2) NULL,
    saturacion_oxigeno INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Tabla de Logs de Auditoría
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- Datos Iniciales
-- =====================================================

-- Insertar especialidades médicas básicas
INSERT INTO especialidades (nombre, descripcion) VALUES
('Medicina General', 'Atención médica primaria y preventiva'),
('Pediatría', 'Atención médica para niños y adolescentes'),
('Ginecología', 'Salud reproductiva femenina'),
('Cardiología', 'Enfermedades del corazón y sistema cardiovascular'),
('Dermatología', 'Enfermedades de la piel'),
('Traumatología', 'Lesiones del sistema músculo-esquelético'),
('Oftalmología', 'Enfermedades de los ojos'),
('Otorrinolaringología', 'Enfermedades de oído, nariz y garganta'),
('Neurología', 'Enfermedades del sistema nervioso'),
('Psiquiatría', 'Trastornos mentales y emocionales');

-- Insertar usuario administrador por defecto
-- Password: Admin123! (hasheado con password_hash de PHP)
INSERT INTO usuarios (username, email, password, nombre_completo, rol) VALUES
('admin', 'admin@consultamedica.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin');

-- Insertar médico de ejemplo
INSERT INTO medicos (codigo_medico, numero_colegiatura, nombres, apellido_paterno, apellido_materno, especialidad_id, telefono, email) VALUES
('MED001', 'CMP-12345', 'Juan Carlos', 'García', 'López', 1, '999888777', 'jgarcia@consultamedica.local');

-- Insertar paciente de ejemplo
INSERT INTO pacientes (numero_historia, tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno, fecha_nacimiento, sexo, direccion, telefono) VALUES
('HC-000001', 'DNI', '12345678', 'María Elena', 'Rodríguez', 'Sánchez', '1985-03-15', 'F', 'Av. Principal 123', '987654321');
