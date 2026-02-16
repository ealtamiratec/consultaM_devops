<?php
/**
 * Definición de Rutas de la Aplicación
 * Sistema de Atención Médica - Consulta Externa
 */

use Core\Router;

$router = new Router();

// =====================================================
// Rutas de Autenticación
// =====================================================
$router->get('', ['AuthController', 'index']);
$router->get('login', ['AuthController', 'login']);
$router->post('login', ['AuthController', 'authenticate']);
$router->get('logout', ['AuthController', 'logout']);

// =====================================================
// Rutas del Dashboard
// =====================================================
$router->get('dashboard', ['DashboardController', 'index']);

// =====================================================
// Rutas de Pacientes
// =====================================================
$router->get('pacientes', ['PacienteController', 'index']);
$router->get('pacientes/crear', ['PacienteController', 'create']);
$router->post('pacientes/crear', ['PacienteController', 'store']);
$router->get('pacientes/ver/{id}', ['PacienteController', 'show']);
$router->get('pacientes/editar/{id}', ['PacienteController', 'edit']);
$router->post('pacientes/editar/{id}', ['PacienteController', 'update']);
$router->post('pacientes/eliminar/{id}', ['PacienteController', 'delete']);
$router->get('pacientes/buscar', ['PacienteController', 'search']);

// =====================================================
// Rutas de Médicos
// =====================================================
$router->get('medicos', ['MedicoController', 'index']);
$router->get('medicos/crear', ['MedicoController', 'create']);
$router->post('medicos/crear', ['MedicoController', 'store']);
$router->get('medicos/ver/{id}', ['MedicoController', 'show']);
$router->get('medicos/editar/{id}', ['MedicoController', 'edit']);
$router->post('medicos/editar/{id}', ['MedicoController', 'update']);
$router->post('medicos/eliminar/{id}', ['MedicoController', 'delete']);

// =====================================================
// Rutas de Consultas Médicas
// =====================================================
$router->get('consultas', ['ConsultaController', 'index']);
$router->get('consultas/crear', ['ConsultaController', 'create']);
$router->post('consultas/crear', ['ConsultaController', 'store']);
$router->get('consultas/ver/{id}', ['ConsultaController', 'show']);
$router->get('consultas/editar/{id}', ['ConsultaController', 'edit']);
$router->post('consultas/editar/{id}', ['ConsultaController', 'update']);
$router->post('consultas/atender/{id}', ['ConsultaController', 'atender']);
$router->post('consultas/cancelar/{id}', ['ConsultaController', 'cancelar']);
$router->get('consultas/historial/{paciente_id}', ['ConsultaController', 'historial']);

// =====================================================
// Rutas de Especialidades
// =====================================================
$router->get('especialidades', ['EspecialidadController', 'index']);
$router->get('especialidades/crear', ['EspecialidadController', 'create']);
$router->post('especialidades/crear', ['EspecialidadController', 'store']);
$router->get('especialidades/editar/{id}', ['EspecialidadController', 'edit']);
$router->post('especialidades/editar/{id}', ['EspecialidadController', 'update']);

// =====================================================
// Rutas API (JSON)
// =====================================================
$router->get('api/pacientes/buscar', ['ApiController', 'buscarPacientes']);
$router->get('api/medicos/especialidad/{id}', ['ApiController', 'medicosPorEspecialidad']);
$router->get('api/estadisticas', ['ApiController', 'estadisticas']);

return $router;
