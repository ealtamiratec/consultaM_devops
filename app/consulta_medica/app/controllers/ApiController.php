<?php
/**
 * Controlador de API
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Consulta;

class ApiController extends Controller
{
    public function __construct()
    {
        if (!Session::isAuthenticated()) {
            $this->json(['error' => 'No autorizado'], 401);
            exit;
        }
    }

    /**
     * Buscar pacientes
     */
    public function buscarPacientes(): void
    {
        $termino = $this->getQuery('q') ?? '';
        
        if (strlen($termino) < 2) {
            $this->json(['data' => []]);
            return;
        }

        $pacienteModel = new Paciente();
        $pacientes = $pacienteModel->buscar($termino);
        
        $resultado = array_map(function($p) use ($pacienteModel) {
            return [
                'id' => $p['id'],
                'numero_historia' => $p['numero_historia'],
                'nombre_completo' => $pacienteModel->getNombreCompleto($p),
                'documento' => $p['tipo_documento'] . ': ' . $p['numero_documento'],
                'telefono' => $p['telefono'] ?? '',
                'fecha_nacimiento' => $p['fecha_nacimiento']
            ];
        }, $pacientes);

        $this->json(['data' => $resultado]);
    }

    /**
     * Obtener médicos por especialidad
     */
    public function medicosPorEspecialidad(string $id): void
    {
        $especialidadId = (int) $id;
        $medicoModel = new Medico();
        
        $medicos = $medicoModel->getByEspecialidad($especialidadId);
        
        $resultado = array_map(function($m) use ($medicoModel) {
            return [
                'id' => $m['id'],
                'codigo' => $m['codigo_medico'],
                'nombre_completo' => $medicoModel->getNombreCompleto($m),
                'colegiatura' => $m['numero_colegiatura']
            ];
        }, $medicos);

        $this->json(['data' => $resultado]);
    }

    /**
     * Obtener estadísticas del dashboard
     */
    public function estadisticas(): void
    {
        $pacienteModel = new Paciente();
        $medicoModel = new Medico();
        $consultaModel = new Consulta();

        $stats = [
            'pacientes' => $pacienteModel->contarActivos(),
            'medicos' => $medicoModel->contarActivos(),
            'consultas_hoy' => $consultaModel->count('fecha_consulta = ?', [date('Y-m-d')]),
            'consultas_pendientes' => $consultaModel->contarPendientes(),
            'consultas_mes' => $consultaModel->count(
                'fecha_consulta BETWEEN ? AND ?',
                [date('Y-m-01'), date('Y-m-t')]
            )
        ];

        $this->json(['data' => $stats]);
    }
}
