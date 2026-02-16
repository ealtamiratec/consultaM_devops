<?php
/**
 * Modelo Paciente
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class Paciente extends Model
{
    protected string $table = 'pacientes';
    
    protected array $fillable = [
        'numero_historia',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        'direccion',
        'telefono',
        'email',
        'grupo_sanguineo',
        'alergias',
        'antecedentes',
        'contacto_emergencia',
        'telefono_emergencia',
        'activo'
    ];

    /**
     * Generar número de historia clínica
     */
    public function generarNumeroHistoria(): string
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(numero_historia, 4) AS UNSIGNED)) as max_num FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'HC-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Buscar por número de documento
     */
    public function findByDocumento(string $tipoDoc, string $numDoc): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE tipo_documento = ? AND numero_documento = ?";
        return $this->db->fetchOne($sql, [$tipoDoc, $numDoc]);
    }

    /**
     * Buscar por número de historia
     */
    public function findByHistoria(string $numeroHistoria): ?array
    {
        return $this->findBy('numero_historia', $numeroHistoria);
    }

    /**
     * Buscar pacientes por nombre o documento
     */
    public function buscar(string $termino): array
    {
        $termino = "%{$termino}%";
        $sql = "SELECT * FROM {$this->table} 
                WHERE activo = 1 
                AND (
                    nombres LIKE ? 
                    OR apellido_paterno LIKE ? 
                    OR apellido_materno LIKE ?
                    OR numero_documento LIKE ?
                    OR numero_historia LIKE ?
                )
                ORDER BY apellido_paterno, nombres
                LIMIT 20";
        
        return $this->db->fetchAll($sql, [$termino, $termino, $termino, $termino, $termino]);
    }

    /**
     * Obtener nombre completo
     */
    public function getNombreCompleto(array $paciente): string
    {
        return trim($paciente['nombres'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? ''));
    }

    /**
     * Calcular edad
     */
    public function calcularEdad(string $fechaNacimiento): int
    {
        $nacimiento = new \DateTime($fechaNacimiento);
        $hoy = new \DateTime();
        return $hoy->diff($nacimiento)->y;
    }

    /**
     * Obtener pacientes activos paginados
     */
    public function getPaginados(int $page = 1, int $perPage = 10, string $busqueda = ''): array
    {
        $where = 'activo = 1';
        $params = [];

        if (!empty($busqueda)) {
            $where .= " AND (nombres LIKE ? OR apellido_paterno LIKE ? OR numero_documento LIKE ? OR numero_historia LIKE ?)";
            $termino = "%{$busqueda}%";
            $params = [$termino, $termino, $termino, $termino];
        }

        return $this->paginate($page, $perPage, $where, $params);
    }

    /**
     * Verificar si documento ya existe
     */
    public function documentoExiste(string $tipoDoc, string $numDoc, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE tipo_documento = ? AND numero_documento = ?";
        $params = [$tipoDoc, $numDoc];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Obtener historial de consultas del paciente
     */
    public function getHistorialConsultas(int $pacienteId): array
    {
        $sql = "SELECT c.*, m.nombres as medico_nombres, m.apellido_paterno as medico_apellido,
                       e.nombre as especialidad
                FROM consultas c
                INNER JOIN medicos m ON c.medico_id = m.id
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE c.paciente_id = ?
                ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC";
        
        return $this->db->fetchAll($sql, [$pacienteId]);
    }

    /**
     * Contar total de pacientes activos
     */
    public function contarActivos(): int
    {
        return $this->count('activo = 1');
    }
}
