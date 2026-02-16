<?php
/**
 * Modelo Usuario
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Models;

use Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';
    
    protected array $fillable = [
        'username',
        'email',
        'password',
        'nombre_completo',
        'rol',
        'activo',
        'ultimo_acceso',
        'token_recuperacion',
        'token_expiracion'
    ];

    protected array $hidden = ['password', 'token_recuperacion'];

    /**
     * Buscar usuario por username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Verificar credenciales de login
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return null;
        }

        if (!$user['activo']) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        // Actualizar último acceso
        $this->update($user['id'], [
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ]);

        // Ocultar campos sensibles
        return $this->hideFields($user);
    }

    /**
     * Crear nuevo usuario con password hasheado
     */
    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        return $this->create($data);
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        return $this->update($id, ['password' => $hashedPassword]);
    }

    /**
     * Verificar si username existe
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Verificar si email existe
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Obtener usuarios activos
     */
    public function getActivos(): array
    {
        return $this->findAllBy('activo', 1);
    }

    /**
     * Obtener usuarios por rol
     */
    public function getByRol(string $rol): array
    {
        return $this->findAllBy('rol', $rol);
    }
}
