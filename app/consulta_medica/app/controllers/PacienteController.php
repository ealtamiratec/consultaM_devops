<?php
/**
 * Controlador de Pacientes
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Paciente;

class PacienteController extends Controller
{
    private Paciente $pacienteModel;

    public function __construct()
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('login');
            exit;
        }
        $this->pacienteModel = new Paciente();
    }

    /**
     * Listar pacientes
     */
    public function index(): void
    {
        $page = (int) ($this->getQuery('page') ?? 1);
        $busqueda = $this->getQuery('busqueda') ?? '';

        $pacientes = $this->pacienteModel->getPaginados($page, 10, $busqueda);

        $this->render('pacientes/index', [
            'title' => 'Pacientes',
            'pacientes' => $pacientes,
            'busqueda' => $busqueda
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void
    {
        $this->render('pacientes/create', [
            'title' => 'Nuevo Paciente',
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Guardar nuevo paciente
     */
    public function store(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('pacientes');
            return;
        }

        // Validar CSRF
        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('pacientes/crear');
            return;
        }

        $data = $this->getPost();

        // Validar datos
        $validator = new Validator($data);
        $validator->validate([
            'tipo_documento' => 'required|in:DNI,CE,Pasaporte,Otro',
            'numero_documento' => 'required|documento|min:6|max:20',
            'nombres' => 'required|alpha|min:2|max:100',
            'apellido_paterno' => 'required|alpha|min:2|max:50',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:M,F,Otro'
        ]);

        if ($validator->hasErrors()) {
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('pacientes/create', [
                'title' => 'Nuevo Paciente',
                'errors' => $validator->getFirstErrors(),
                'old' => $data,
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Verificar documento único
        if ($this->pacienteModel->documentoExiste($data['tipo_documento'], $data['numero_documento'])) {
            set_flash('error', 'Ya existe un paciente con ese documento.');
            $this->render('pacientes/create', [
                'title' => 'Nuevo Paciente',
                'old' => $data,
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Generar número de historia
        $data['numero_historia'] = $this->pacienteModel->generarNumeroHistoria();

        try {
            $id = $this->pacienteModel->create($data);
            app_log('info', 'Paciente creado', ['paciente_id' => $id, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Paciente registrado exitosamente. Historia: ' . $data['numero_historia']);
            $this->redirect('pacientes/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al crear paciente', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al registrar el paciente.');
            $this->redirect('pacientes/crear');
        }
    }

    /**
     * Ver detalle de paciente
     */
    public function show(string $id): void
    {
        $paciente = $this->pacienteModel->find((int) $id);

        if (!$paciente) {
            set_flash('error', 'Paciente no encontrado.');
            $this->redirect('pacientes');
            return;
        }

        // Obtener historial de consultas
        $historial = $this->pacienteModel->getHistorialConsultas((int) $id);

        $this->render('pacientes/show', [
            'title' => 'Detalle de Paciente',
            'paciente' => $paciente,
            'historial' => $historial,
            'edad' => $this->pacienteModel->calcularEdad($paciente['fecha_nacimiento'])
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id): void
    {
        $paciente = $this->pacienteModel->find((int) $id);

        if (!$paciente) {
            set_flash('error', 'Paciente no encontrado.');
            $this->redirect('pacientes');
            return;
        }

        $this->render('pacientes/edit', [
            'title' => 'Editar Paciente',
            'paciente' => $paciente,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Actualizar paciente
     */
    public function update(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('pacientes');
            return;
        }

        $pacienteId = (int) $id;
        $paciente = $this->pacienteModel->find($pacienteId);

        if (!$paciente) {
            set_flash('error', 'Paciente no encontrado.');
            $this->redirect('pacientes');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('pacientes/editar/' . $id);
            return;
        }

        $data = $this->getPost();

        // Validar datos
        $validator = new Validator($data);
        $validator->validate([
            'tipo_documento' => 'required|in:DNI,CE,Pasaporte,Otro',
            'numero_documento' => 'required|documento|min:6|max:20',
            'nombres' => 'required|alpha|min:2|max:100',
            'apellido_paterno' => 'required|alpha|min:2|max:50',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:M,F,Otro'
        ]);

        if ($validator->hasErrors()) {
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('pacientes/edit', [
                'title' => 'Editar Paciente',
                'paciente' => array_merge($paciente, $data),
                'errors' => $validator->getFirstErrors(),
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Verificar documento único (excluyendo el actual)
        if ($this->pacienteModel->documentoExiste($data['tipo_documento'], $data['numero_documento'], $pacienteId)) {
            set_flash('error', 'Ya existe otro paciente con ese documento.');
            $this->redirect('pacientes/editar/' . $id);
            return;
        }

        try {
            $this->pacienteModel->update($pacienteId, $data);
            app_log('info', 'Paciente actualizado', ['paciente_id' => $pacienteId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Paciente actualizado exitosamente.');
            $this->redirect('pacientes/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al actualizar paciente', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al actualizar el paciente.');
            $this->redirect('pacientes/editar/' . $id);
        }
    }

    /**
     * Eliminar paciente (soft delete)
     */
    public function delete(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('pacientes');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('pacientes');
            return;
        }

        $pacienteId = (int) $id;
        
        try {
            $this->pacienteModel->softDelete($pacienteId);
            app_log('info', 'Paciente eliminado', ['paciente_id' => $pacienteId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Paciente eliminado exitosamente.');
        } catch (\Exception $e) {
            app_log('error', 'Error al eliminar paciente', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al eliminar el paciente.');
        }

        $this->redirect('pacientes');
    }

    /**
     * Buscar pacientes (AJAX)
     */
    public function search(): void
    {
        $termino = $this->getQuery('q') ?? '';
        
        if (strlen($termino) < 2) {
            $this->json(['data' => []]);
            return;
        }

        $pacientes = $this->pacienteModel->buscar($termino);
        
        $resultado = array_map(function($p) {
            return [
                'id' => $p['id'],
                'numero_historia' => $p['numero_historia'],
                'nombre_completo' => $this->pacienteModel->getNombreCompleto($p),
                'documento' => $p['tipo_documento'] . ': ' . $p['numero_documento'],
                'telefono' => $p['telefono'] ?? ''
            ];
        }, $pacientes);

        $this->json(['data' => $resultado]);
    }
}
