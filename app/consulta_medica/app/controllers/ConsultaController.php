<?php
/**
 * Controlador de Consultas Médicas
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\SignosVitales;

class ConsultaController extends Controller
{
    private Consulta $consultaModel;
    private Paciente $pacienteModel;
    private Medico $medicoModel;
    private Especialidad $especialidadModel;
    private SignosVitales $signosModel;

    public function __construct()
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('login');
            exit;
        }
        $this->consultaModel = new Consulta();
        $this->pacienteModel = new Paciente();
        $this->medicoModel = new Medico();
        $this->especialidadModel = new Especialidad();
        $this->signosModel = new SignosVitales();
    }

    /**
     * Listar consultas
     */
    public function index(): void
    {
        $page = (int) ($this->getQuery('page') ?? 1);
        $filtros = [
            'fecha_desde' => $this->getQuery('fecha_desde'),
            'fecha_hasta' => $this->getQuery('fecha_hasta'),
            'estado' => $this->getQuery('estado'),
            'medico_id' => $this->getQuery('medico_id'),
            'busqueda' => $this->getQuery('busqueda')
        ];

        $consultas = $this->consultaModel->getPaginadas($page, 10, $filtros);
        $medicos = $this->medicoModel->getAllWithEspecialidad();

        $this->render('consultas/index', [
            'title' => 'Consultas Médicas',
            'consultas' => $consultas,
            'medicos' => $medicos,
            'filtros' => $filtros
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void
    {
        $especialidades = $this->especialidadModel->getActivas();
        $medicos = $this->medicoModel->getAllWithEspecialidad();
        
        // Precargar paciente si viene por parámetro
        $pacienteId = $this->getQuery('paciente_id');
        $paciente = null;
        if ($pacienteId) {
            $paciente = $this->pacienteModel->find((int) $pacienteId);
        }

        $this->render('consultas/create', [
            'title' => 'Nueva Consulta',
            'especialidades' => $especialidades,
            'medicos' => $medicos,
            'paciente' => $paciente,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Guardar nueva consulta
     */
    public function store(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('consultas');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('consultas/crear');
            return;
        }

        $data = $this->getPost();

        // Validar datos
        $validator = new Validator($data);
        $validator->validate([
            'paciente_id' => 'required|integer',
            'medico_id' => 'required|integer',
            'fecha_consulta' => 'required|date',
            'hora_consulta' => 'required|time',
            'tipo_consulta' => 'required|in:Primera vez,Control,Emergencia,Referencia',
            'motivo_consulta' => 'required|min:10|max:500'
        ]);

        if ($validator->hasErrors()) {
            $especialidades = $this->especialidadModel->getActivas();
            $medicos = $this->medicoModel->getAllWithEspecialidad();
            set_flash('error', 'Por favor, corrija los errores en el formulario.');
            $this->render('consultas/create', [
                'title' => 'Nueva Consulta',
                'especialidades' => $especialidades,
                'medicos' => $medicos,
                'errors' => $validator->getFirstErrors(),
                'old' => $data,
                'csrf_token' => Session::getCsrfToken()
            ]);
            return;
        }

        // Verificar que el paciente existe
        $paciente = $this->pacienteModel->find((int) $data['paciente_id']);
        if (!$paciente) {
            set_flash('error', 'El paciente seleccionado no existe.');
            $this->redirect('consultas/crear');
            return;
        }

        // Verificar disponibilidad del médico
        if (!$this->consultaModel->verificarDisponibilidad(
            (int) $data['medico_id'],
            $data['fecha_consulta'],
            $data['hora_consulta']
        )) {
            set_flash('error', 'El médico ya tiene una consulta programada en ese horario.');
            $this->redirect('consultas/crear');
            return;
        }

        // Generar número de consulta
        $data['numero_consulta'] = $this->consultaModel->generarNumeroConsulta();
        $data['usuario_registro_id'] = Session::getUserId();
        $data['estado'] = 'Programada';

        try {
            $id = $this->consultaModel->create($data);
            app_log('info', 'Consulta creada', ['consulta_id' => $id, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Consulta registrada exitosamente. Número: ' . $data['numero_consulta']);
            $this->redirect('consultas/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al crear consulta', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al registrar la consulta.');
            $this->redirect('consultas/crear');
        }
    }

    /**
     * Ver detalle de consulta
     */
    public function show(string $id): void
    {
        $consulta = $this->consultaModel->findWithDetails((int) $id);

        if (!$consulta) {
            set_flash('error', 'Consulta no encontrada.');
            $this->redirect('consultas');
            return;
        }

        // Obtener signos vitales
        $signosVitales = $this->signosModel->getByConsulta((int) $id);

        // Calcular edad del paciente
        $edad = $this->pacienteModel->calcularEdad($consulta['paciente_fecha_nacimiento']);

        $this->render('consultas/show', [
            'title' => 'Detalle de Consulta',
            'consulta' => $consulta,
            'signosVitales' => $signosVitales,
            'edad' => $edad,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Formulario de edición / atención
     */
    public function edit(string $id): void
    {
        $consulta = $this->consultaModel->findWithDetails((int) $id);

        if (!$consulta) {
            set_flash('error', 'Consulta no encontrada.');
            $this->redirect('consultas');
            return;
        }

        $signosVitales = $this->signosModel->getByConsulta((int) $id);
        $edad = $this->pacienteModel->calcularEdad($consulta['paciente_fecha_nacimiento']);

        $this->render('consultas/edit', [
            'title' => 'Atender Consulta',
            'consulta' => $consulta,
            'signosVitales' => $signosVitales,
            'edad' => $edad,
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Actualizar consulta
     */
    public function update(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('consultas');
            return;
        }

        $consultaId = (int) $id;
        $consulta = $this->consultaModel->find($consultaId);

        if (!$consulta) {
            set_flash('error', 'Consulta no encontrada.');
            $this->redirect('consultas');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('consultas/editar/' . $id);
            return;
        }

        $data = $this->getPost();

        try {
            // Actualizar signos vitales si se proporcionaron
            if (!empty($data['presion_sistolica']) || !empty($data['peso'])) {
                $signosData = [
                    'presion_sistolica' => $data['presion_sistolica'] ?? null,
                    'presion_diastolica' => $data['presion_diastolica'] ?? null,
                    'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? null,
                    'frecuencia_respiratoria' => $data['frecuencia_respiratoria'] ?? null,
                    'temperatura' => $data['temperatura'] ?? null,
                    'peso' => $data['peso'] ?? null,
                    'talla' => $data['talla'] ?? null,
                    'saturacion_oxigeno' => $data['saturacion_oxigeno'] ?? null
                ];
                $this->signosModel->actualizarSignos($consultaId, $signosData);
            }

            // Actualizar consulta
            $consultaData = [
                'sintomas' => $data['sintomas'] ?? null,
                'examen_fisico' => $data['examen_fisico'] ?? null,
                'diagnostico' => $data['diagnostico'] ?? null,
                'tratamiento' => $data['tratamiento'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'proxima_cita' => !empty($data['proxima_cita']) ? $data['proxima_cita'] : null
            ];

            $this->consultaModel->update($consultaId, $consultaData);
            
            app_log('info', 'Consulta actualizada', ['consulta_id' => $consultaId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Consulta actualizada exitosamente.');
            $this->redirect('consultas/ver/' . $id);
        } catch (\Exception $e) {
            app_log('error', 'Error al actualizar consulta', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al actualizar la consulta.');
            $this->redirect('consultas/editar/' . $id);
        }
    }

    /**
     * Marcar consulta como atendida
     */
    public function atender(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('consultas');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('consultas/ver/' . $id);
            return;
        }

        $consultaId = (int) $id;
        
        try {
            $this->consultaModel->cambiarEstado($consultaId, 'Atendida');
            app_log('info', 'Consulta atendida', ['consulta_id' => $consultaId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Consulta marcada como atendida.');
        } catch (\Exception $e) {
            app_log('error', 'Error al atender consulta', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al actualizar el estado de la consulta.');
        }

        $this->redirect('consultas/ver/' . $id);
    }

    /**
     * Cancelar consulta
     */
    public function cancelar(string $id): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('consultas');
            return;
        }

        if (!Session::validateCsrfToken($this->getPost('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido.');
            $this->redirect('consultas');
            return;
        }

        $consultaId = (int) $id;
        
        try {
            $this->consultaModel->cambiarEstado($consultaId, 'Cancelada');
            app_log('info', 'Consulta cancelada', ['consulta_id' => $consultaId, 'user_id' => Session::getUserId()]);
            set_flash('success', 'Consulta cancelada exitosamente.');
        } catch (\Exception $e) {
            app_log('error', 'Error al cancelar consulta', ['error' => $e->getMessage()]);
            set_flash('error', 'Error al cancelar la consulta.');
        }

        $this->redirect('consultas');
    }

    /**
     * Ver historial de consultas de un paciente
     */
    public function historial(string $paciente_id): void
    {
        $pacienteId = (int) $paciente_id;
        $paciente = $this->pacienteModel->find($pacienteId);

        if (!$paciente) {
            set_flash('error', 'Paciente no encontrado.');
            $this->redirect('pacientes');
            return;
        }

        $historial = $this->consultaModel->getHistorialPaciente($pacienteId);

        $this->render('consultas/historial', [
            'title' => 'Historial de Consultas',
            'paciente' => $paciente,
            'historial' => $historial,
            'edad' => $this->pacienteModel->calcularEdad($paciente['fecha_nacimiento'])
        ]);
    }
}
