<?php
/**
 * Modelo Consulta
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class Consulta extends Model
{
    protected string $table = 'consultas';
    
    protected array $fillable = [
        'numero_consulta',
        'paciente_id',
        'medico_id',
        'fecha_consulta',
        'hora_consulta',
        'tipo_consulta',
        'estado',
        'motivo_consulta',
        'sintomas',
        'examen_fisico',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'proxima_cita',
        'usuario_registro_id'
    ];

    /**
     * Generar número de consulta
     */
    public function generarNumeroConsulta(): string
    {
        $fecha = date('Ymd');
        $sql = "SELECT MAX(CAST(SUBSTRING(numero_consulta, 10) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE numero_consulta LIKE ?";
        $result = $this->db->fetchOne($sql, ["CON{$fecha}%"]);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'CON' . $fecha . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener consulta con detalles completos
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*,
                       p.numero_historia, p.nombres as paciente_nombres, 
                       p.apellido_paterno as paciente_apellido_paterno,
                       p.apellido_materno as paciente_apellido_materno,
                       p.fecha_nacimiento as paciente_fecha_nacimiento,
                       p.sexo as paciente_sexo,
                       p.telefono as paciente_telefono,
                       m.codigo_medico, m.nombres as medico_nombres,
                       m.apellido_paterno as medico_apellido_paterno,
                       m.apellido_materno as medico_apellido_materno,
                       e.nombre as especialidad_nombre,
                       u.nombre_completo as registrado_por
                FROM {$this->table} c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                INNER JOIN usuarios u ON c.usuario_registro_id = u.id
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Obtener consultas del día
     */
    public function getConsultasDelDia(?string $fecha = null): array
    {
        $fecha = $fecha ?? date('Y-m-d');
        $sql = "SELECT c.*, 
                       p.numero_historia, p.nombres as paciente_nombres,
                       p.apellido_paterno as paciente_apellido,
                       m.nombres as medico_nombres, m.apellido_paterno as medico_apellido,
                       e.nombre as especialidad
                FROM {$this->table} c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE c.fecha_consulta = ?
                ORDER BY c.hora_consulta ASC";
        return $this->db->fetchAll($sql, [$fecha]);
    }

    /**
     * Obtener consultas paginadas con filtros
     */
    public function getPaginadas(int $page = 1, int $perPage = 10, array $filtros = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filtros['fecha_desde'])) {
            $where .= " AND c.fecha_consulta >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where .= " AND c.fecha_consulta <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['estado'])) {
            $where .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['medico_id'])) {
            $where .= " AND c.medico_id = ?";
            $params[] = $filtros['medico_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $where .= " AND (p.nombres LIKE ? OR p.apellido_paterno LIKE ? OR p.numero_historia LIKE ? OR c.numero_consulta LIKE ?)";
            $termino = "%{$filtros['busqueda']}%";
            $params = array_merge($params, [$termino, $termino, $termino, $termino]);
        }

        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     INNER JOIN pacientes p ON c.paciente_id = p.id
                     WHERE {$where}";
        $totalResult = $this->db->fetchOne($countSql, $params);
        $total = (int) $totalResult['total'];

        // Obtener datos paginados
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT c.*, 
                       p.numero_historia, p.nombres as paciente_nombres,
                       p.apellido_paterno as paciente_apellido,
                       m.nombres as medico_nombres, m.apellido_paterno as medico_apellido,
                       e.nombre as especialidad
                FROM {$this->table} c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE {$where}
                ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($sql, $params);
        $totalPages = ceil($total / $perPage);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ];
    }

    /**
     * Cambiar estado de consulta
     */
    public function cambiarEstado(int $id, string $estado): bool
    {
        return $this->update($id, ['estado' => $estado]);
    }

    /**
     * Registrar atención médica
     */
    public function registrarAtencion(int $id, array $datosAtencion): bool
    {
        $datosAtencion['estado'] = 'Atendida';
        return $this->update($id, $datosAtencion);
    }

    /**
     * Obtener historial de consultas de un paciente
     */
    public function getHistorialPaciente(int $pacienteId): array
    {
        $sql = "SELECT c.*, 
                       m.nombres as medico_nombres, m.apellido_paterno as medico_apellido,
                       e.nombre as especialidad
                FROM {$this->table} c
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE c.paciente_id = ?
                ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC";
        return $this->db->fetchAll($sql, [$pacienteId]);
    }

    /**
     * Verificar disponibilidad de horario
     */
    public function verificarDisponibilidad(int $medicoId, string $fecha, string $hora, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE medico_id = ? AND fecha_consulta = ? AND hora_consulta = ?
                AND estado NOT IN ('Cancelada', 'No asistió')";
        $params = [$medicoId, $fecha, $hora];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] == 0;
    }

    /**
     * Obtener estadísticas
     */
    public function getEstadisticas(): array
    {
        // Total de consultas
        $totalConsultas = $this->count();

        // Consultas de hoy
        $consultasHoy = $this->count('fecha_consulta = ?', [date('Y-m-d')]);

        // Consultas por estado
        $sql = "SELECT estado, COUNT(*) as total FROM {$this->table} GROUP BY estado";
        $porEstado = $this->db->fetchAll($sql);

        // Consultas del mes
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $consultasMes = $this->count('fecha_consulta BETWEEN ? AND ?', [$inicioMes, $finMes]);

        // Consultas atendidas hoy
        $atendidasHoy = $this->count('fecha_consulta = ? AND estado = ?', [date('Y-m-d'), 'Atendida']);

        return [
            'total' => $totalConsultas,
            'hoy' => $consultasHoy,
            'mes' => $consultasMes,
            'atendidas_hoy' => $atendidasHoy,
            'por_estado' => $porEstado
        ];
    }

    /**
     * Contar consultas pendientes
     */
    public function contarPendientes(): int
    {
        return $this->count("estado IN ('Programada', 'En espera')");
    }
}
