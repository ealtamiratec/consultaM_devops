<?php
/**
 * Modelo Especialidad
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class Especialidad extends Model
{
    protected string $table = 'especialidades';
    
    protected array $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    /**
     * Obtener especialidades activas
     */
    public function getActivas(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Verificar si nombre existe
     */
    public function nombreExiste(string $nombre, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nombre = ?";
        $params = [$nombre];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Contar médicos por especialidad
     */
    public function contarMedicos(int $especialidadId): int
    {
        $sql = "SELECT COUNT(*) as total FROM medicos WHERE especialidad_id = ? AND activo = 1";
        $result = $this->db->fetchOne($sql, [$especialidadId]);
        return (int) $result['total'];
    }

    /**
     * Obtener especialidades con conteo de médicos
     */
    public function getAllWithMedicosCount(): array
    {
        $sql = "SELECT e.*, COUNT(m.id) as total_medicos
                FROM {$this->table} e
                LEFT JOIN medicos m ON e.id = m.especialidad_id AND m.activo = 1
                WHERE e.activo = 1
                GROUP BY e.id
                ORDER BY e.nombre ASC";
        return $this->db->fetchAll($sql);
    }
}
