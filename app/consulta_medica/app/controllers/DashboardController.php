<?php
/**
 * Controlador del Dashboard
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Consulta;

class DashboardController extends Controller
{
    private Paciente $pacienteModel;
    private Medico $medicoModel;
    private Consulta $consultaModel;

    public function __construct()
    {
        // Verificar autenticación
        if (!Session::isAuthenticated()) {
            $this->redirect('login');
            exit;
        }

        $this->pacienteModel = new Paciente();
        $this->medicoModel = new Medico();
        $this->consultaModel = new Consulta();
    }

    /**
     * Mostrar dashboard principal
     */
    public function index(): void
    {
        $user = Session::getUser();

        // Obtener estadísticas
        $stats = [
            'total_pacientes' => $this->pacienteModel->contarActivos(),
            'total_medicos' => $this->medicoModel->contarActivos(),
            'consultas_hoy' => $this->consultaModel->count('fecha_consulta = ?', [date('Y-m-d')]),
            'consultas_pendientes' => $this->consultaModel->contarPendientes()
        ];

        // Obtener consultas del día
        $consultasHoy = $this->consultaModel->getConsultasDelDia();

        // Estadísticas adicionales
        $estadisticasConsultas = $this->consultaModel->getEstadisticas();

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'consultasHoy' => $consultasHoy,
            'estadisticasConsultas' => $estadisticasConsultas
        ]);
    }
}
