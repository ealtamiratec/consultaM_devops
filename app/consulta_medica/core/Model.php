<?php
/**
 * Clase Model - Modelo base abstracto
 * Sistema de Atención Médica - Consulta Externa
 */

namespace Core;

abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los registros
     */
    public function all(array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table} ORDER BY {$this->primaryKey} DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Buscar por ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Buscar por columna
     */
    public function findBy(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetchOne($sql, [$value]);
    }

    /**
     * Buscar todos por columna
     */
    public function findAllBy(string $column, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$this->primaryKey} DESC";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Crear nuevo registro
     */
    public function create(array $data): int
    {
        // Filtrar solo campos permitidos
        $data = $this->filterFillable($data);
        return $this->db->insert($this->table, $data);
    }

    /**
     * Actualizar registro
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $affected = $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
        return $affected > 0;
    }

    /**
     * Eliminar registro
     */
    public function delete(int $id): bool
    {
        $affected = $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
        return $affected > 0;
    }

    /**
     * Soft delete (marcar como inactivo)
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Contar registros
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Paginación
     */
    public function paginate(int $page = 1, int $perPage = 10, string $where = '1=1', array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($where, $params);
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$this->primaryKey} DESC LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->fetchAll($sql, $params);

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
     * Ejecutar consulta personalizada
     */
    public function query(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Ejecutar consulta y obtener un registro
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Filtrar campos permitidos
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Ocultar campos sensibles
     */
    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        return $data;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
