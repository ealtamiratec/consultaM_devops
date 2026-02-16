<?php
/**
 * Controlador de Médicos
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Medico;
use App\Models\Especialidad;

class MedicoController extends Controller
{
    private Medico $medicoModel;
    private Especialidad $especialidadModel;

    public function __construct()
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('login');
            exit;
        }
        $this->medicoModel = new Medico();
        $this->especialidadModel = new Especialidad();
    }

    /**
     * Listar médicos
     */
    public function index(): void
    {
        $page = (int) ($this->getQuery('page') ?? 1);
        $busqueda = $this->getQuery('busqueda') ?? '';

        $medicos = $this->medicoModel->getPaginados($page, 10, $busqueda);
        
        // Agregar información de especialidad
        foreach ($medicos['data'] as &$medico) {
            $medicoCompleto = $this->medicoModel->findWithEspecialidad($medico['id']);
            $medico['especialidad_nombre'] = $medicoCompleto['especialidad_nombre'] ?? '';
        }

        $this->render('medicos/index', [
            'title' => 'Médicos',
            'medicos' => $medicos,
            'busqueda' => $busqueda
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void
    {
        $especialidades = $this->especialidadModel->getActivas();

        $this->render('medicos/create', [
            'title' => 'Nuevo Médico',
            'especialidades' => $especialidades,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Guardar nuevo médico
     */
    public function store(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('medicos');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('medicos/crear');
            return;
        }

        $data = $this->getPost();

        // Validar datos
        $validator = new Validator($data);
        $validator->validate([
            'numero_colegiatura' => 'required|min:5|max:20',
            'nombres' => 'required|alpha|min:2|max:100',
            'apellido_paterno' => 'required|alpha|min:2|max:50',
            'especialidad_id' => 'required|integer'
        ]);

        if ($validator->hasErrors()) {
            $especialidades = $this->especialidadModel->getActivas();
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('medicos/create', [
                'title' => 'Nuevo Médico',
                'especialidades' => $especialidades,
                'errors' => $validator->getFirstErrors(),
                'old' => $data,
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Verificar colegiatura única
        if ($this->medicoModel->colegiaturaExiste($data['numero_colegiatura'])) {
            set_flash('error', 'Ya existe un médico con ese número de colegiatura.');
            $this->redirect('medicos/crear');
            return;
        }

        // Generar código de médico
        $data['codigo_medico'] = $this->medicoModel->generarCodigoMedico();

        try {
            $id = $this->medicoModel->create($data);
            app_log('info', 'Médico creado', ['medico_id' => $id, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Médico registrado exitosamente. Código: ' . $data['codigo_medico']);
            $this->redirect('medicos/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al crear médico', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al registrar el médico.');
            $this->redirect('medicos/crear');
        }
    }

    /**
     * Ver detalle de médico
     */
    public function show(string $id): void
    {
        $medico = $this->medicoModel->findWithEspecialidad((int) $id);

        if (!$medico) {
            set_flash('error', 'Médico no encontrado.');
            $this->redirect('medicos');
            return;
        }

        // Contar consultas
        $totalConsultas = $this->medicoModel->contarConsultas((int) $id);
        $consultasAtendidas = $this->medicoModel->contarConsultas((int) $id, 'Atendida');

        $this->render('medicos/show', [
            'title' => 'Detalle de Médico',
            'medico' => $medico,
            'totalConsultas' => $totalConsultas,
            'consultasAtendidas' => $consultasAtendidas
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id): void
    {
        $medico = $this->medicoModel->find((int) $id);

        if (!$medico) {
            set_flash('error', 'Médico no encontrado.');
            $this->redirect('medicos');
            return;
        }

        $especialidades = $this->especialidadModel->getActivas();

        $this->render('medicos/edit', [
            'title' => 'Editar Médico',
            'medico' => $medico,
            'especialidades' => $especialidades,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Actualizar médico
     */
    public function update(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('medicos');
            return;
        }

        $medicoId = (int) $id;
        $medico = $this->medicoModel->find($medicoId);

        if (!$medico) {
            set_flash('error', 'Médico no encontrado.');
            $this->redirect('medicos');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('medicos/editar/' . $id);
            return;
        }

        $data = $this->getPost();

        // Validar datos
        $validator = new Validator($data);
        $validator->validate([
            'numero_colegiatura' => 'required|min:5|max:20',
            'nombres' => 'required|alpha|min:2|max:100',
            'apellido_paterno' => 'required|alpha|min:2|max:50',
            'especialidad_id' => 'required|integer'
        ]);

        if ($validator->hasErrors()) {
            $especialidades = $this->especialidadModel->getActivas();
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('medicos/edit', [
                'title' => 'Editar Médico',
                'medico' => array_merge($medico, $data),
                'especialidades' => $especialidades,
                'errors' => $validator->getFirstErrors(),
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Verificar colegiatura única (excluyendo el actual)
        if ($this->medicoModel->colegiaturaExiste($data['numero_colegiatura'], $medicoId)) {
            set_flash('error', 'Ya existe otro médico con ese número de colegiatura.');
            $this->redirect('medicos/editar/' . $id);
            return;
        }

        try {
            $this->medicoModel->update($medicoId, $data);
            app_log('info', 'Médico actualizado', ['medico_id' => $medicoId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Médico actualizado exitosamente.');
            $this->redirect('medicos/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al actualizar médico', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al actualizar el médico.');
            $this->redirect('medicos/editar/' . $id);
        }
    }

    /**
     * Eliminar médico (soft delete)
     */
    public function delete(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('medicos');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('medicos');
            return;
        }

        $medicoId = (int) $id;
        
        try {
            $this->medicoModel->softDelete($medicoId);
            app_log('info', 'Médico eliminado', ['medico_id' => $medicoId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Médico eliminado exitosamente.');
        } catch (\Exception $e) {
            app_log('error', 'Error al eliminar médico', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al eliminar el médico.');
        }

        $this->redirect('medicos');
    }
}
