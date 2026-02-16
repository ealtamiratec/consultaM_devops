<?php
/**
 * Controlador de Especialidades
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Especialidad;

class EspecialidadController extends Controller
{
    private Especialidad $especialidadModel;

    public function __construct()
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('login');
            exit;
        }
        $this->especialidadModel = new Especialidad();
    }

    /**
     * Listar especialidades
     */
    public function index(): void
    {
        $especialidades = $this->especialidadModel->getAllWithMedicosCount();

        $this->render('especialidades/index', [
            'title' => 'Especialidades',
            'especialidades' => $especialidades
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void
    {
        $this->render('especialidades/create', [
            'title' => 'Nueva Especialidad',
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Guardar nueva especialidad
     */
    public function store(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('especialidades');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('especialidades/crear');
            return;
        }

        $data = $this->getPost();

        $validator = new Validator($data);
        $validator->validate([
            'nombre' => 'required|min:3|max:100'
        ]);

        if ($validator->hasErrors()) {
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('especialidades/create', [
                'title' => 'Nueva Especialidad',
                'errors' => $validator->getFirstErrors(),
                'old' => $data,
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        if ($this->especialidadModel->nombreExiste($data['nombre'])) {
            set_flash('error', 'Ya existe una especialidad con ese nombre.');
            $this->redirect('especialidades/crear');
            return;
        }

        try {
            $this->especialidadModel->create($data);
            set_flash('success', 'Especialidad creada exitosamente.');
            $this->redirect('especialidades');
        } catch (\Exception $e) {
            set_flash('error', 'Error al crear la especialidad.');
            $this->redirect('especialidades/crear');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id): void
    {
        $especialidad = $this->especialidadModel->find((int) $id);

        if (!$especialidad) {
            set_flash('error', 'Especialidad no encontrada.');
            $this->redirect('especialidades');
            return;
        }

        $this->render('especialidades/edit', [
            'title' => 'Editar Especialidad',
            'especialidad' => $especialidad,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Actualizar especialidad
     */
    public function update(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('especialidades');
            return;
        }

        $especialidadId = (int) $id;
        $especialidad = $this->especialidadModel->find($especialidadId);

        if (!$especialidad) {
            set_flash('error', 'Especialidad no encontrada.');
            $this->redirect('especialidades');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('especialidades/editar/' . $id);
            return;
        }

        $data = $this->getPost();

        $validator = new Validator($data);
        $validator->validate([
            'nombre' => 'required|min:3|max:100'
        ]);

        if ($validator->hasErrors()) {
            set_flash('error', 'Por favor, corrija los errores.');
            $this->redirect('especialidades/editar/' . $id);
            return;
        }

        if ($this->especialidadModel->nombreExiste($data['nombre'], $especialidadId)) {
            set_flash('error', 'Ya existe otra especialidad con ese nombre.');
            $this->redirect('especialidades/editar/' . $id);
            return;
        }

        try {
            $this->especialidadModel->update($especialidadId, $data);
            set_flash('success', 'Especialidad actualizada exitosamente.');
            $this->redirect('especialidades');
        } catch (\Exception $e) {
            set_flash('error', 'Error al actualizar la especialidad.');
            $this->redirect('especialidades/editar/' . $id);
        }
    }
}
