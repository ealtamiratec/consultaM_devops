<?php
/**
 * Modelo Medico
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class Medico extends Model
{
    protected string $table = 'medicos';
    
    protected array $fillable = [
        'usuario_id',
        'codigo_medico',
        'numero_colegiatura',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'especialidad_id',
        'telefono',
        'email',
        'horario_atencion',
        'activo'
    ];

    /**
     * Generar código de médico
     */
    public function generarCodigoMedico(): string
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo_medico, 4) AS UNSIGNED)) as max_num FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'MED' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener médico con especialidad
     */
    public function findWithEspecialidad(int $id): ?array
    {
        $sql = "SELECT m.*, e.nombre as especialidad_nombre, e.descripcion as especialidad_descripcion
                FROM {$this->table} m
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE m.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Obtener todos los médicos con especialidad
     */
    public function getAllWithEspecialidad(): array
    {
        $sql = "SELECT m.*, e.nombre as especialidad_nombre
                FROM {$this->table} m
                INNER JOIN especialidades e ON m.especialidad_id = e.id
                WHERE m.activo = 1
                ORDER BY m.apellido_paterno, m.nombres";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtener médicos por especialidad
     */
    public function getByEspecialidad(int $especialidadId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE especialidad_id = ? AND activo = 1
                ORDER BY apellido_paterno, nombres";
        return $this->db->fetchAll($sql, [$especialidadId]);
    }

    /**
     * Buscar médico por colegiatura
     */
    public function findByColegiatura(string $colegiatura): ?array
    {
        return $this->findBy('numero_colegiatura', $colegiatura);
    }

    /**
     * Verificar si colegiatura existe
     */
    public function colegiaturaExiste(string $colegiatura, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE numero_colegiatura = ?";
        $params = [$colegiatura];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Obtener nombre completo del médico
     */
    public function getNombreCompleto(array $medico): string
    {
        return 'Dr(a). ' . trim($medico['nombres'] . ' ' . $medico['apellido_paterno'] . ' ' . ($medico['apellido_materno'] ?? ''));
    }

    /**
     * Obtener médicos paginados
     */
    public function getPaginados(int $page = 1, int $perPage = 10, string $busqueda = ''): array
    {
        $where = 'activo = 1';
        $params = [];

        if (!empty($busqueda)) {
            $where .= " AND (nombres LIKE ? OR apellido_paterno LIKE ? OR numero_colegiatura LIKE ? OR codigo_medico LIKE ?)";
            $termino = "%{$busqueda}%";
            $params = [$termino, $termino, $termino, $termino];
        }

        return $this->paginate($page, $perPage, $where, $params);
    }

    /**
     * Contar consultas del médico
     */
    public function contarConsultas(int $medicoId, ?string $estado = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM consultas WHERE medico_id = ?";
        $params = [$medicoId];

        if ($estado) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int) $result['total'];
    }

    /**
     * Contar médicos activos
     */
    public function contarActivos(): int
    {
        return $this->count('activo = 1');
    }
}
