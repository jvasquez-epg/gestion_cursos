<?php
/**
 * Modelo de usuarios del sistema de gestión académica.
 * Gestiona operaciones de consulta, registro, actualización y eliminación
 * de usuarios con control de duplicidad sobre datos clave (DNI, correo, usuario),
 * así como reportes por rol y utilidades complementarias.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

 
// admin/models/UsuarioModel.php

class UsuarioModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cuenta usuarios por cada rol válido.
     *
     * @return array<string,int>
     */
    public function contarPorRoles(): array
    {
        $stmt = $this->pdo->query(
            "SELECT r.nombre AS rol, COUNT(u.id) AS cantidad
             FROM usuarios u
             JOIN roles r ON u.rol_id = r.id
             GROUP BY r.nombre"
        );
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Inicializar conteo solo para roles esperados
        $conteo = [
            'administrador' => 0,
            'administrativo' => 0,
            'estudiante' => 0,
        ];

        foreach ($resultados as $fila) {
            $rol = strtolower($fila['rol']);
            if (isset($conteo[$rol])) {
                $conteo[$rol] = (int) $fila['cantidad'];
            }
        }

        return $conteo;
    }

    /**
     * Recupera todos los usuarios de un rol dado.
     *
     * @param string $rol
     * @return array<array>
     */
    public function getByRolNombre(string $rol): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*
              FROM usuarios u
              JOIN roles r ON u.rol_id = r.id
             WHERE r.nombre = ?
             ORDER BY u.apellido_paterno, u.apellido_materno, u.nombres"
        );
        $stmt->execute([$rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recupera un usuario por su ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crea un nuevo usuario.
     *
     * @param array $data
     */
    public function crear(array $data): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios
              (nombres, apellido_paterno, apellido_materno,
               dni, correo, telefono, usuario, `contraseña`, rol_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            trim($data['nombres']),
            trim($data['apellido_paterno']),
            trim($data['apellido_materno']),
            trim($data['dni']),
            trim($data['correo']),
            trim($data['telefono'] ?? ''),
            trim($data['usuario']),
            password_hash($data['contraseña'], PASSWORD_DEFAULT),
            $data['rol_id'],
        ]);
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * @param array $data
     */
    public function actualizar(array $data): void
    {
        $campos = [
            'nombres = ?',
            'apellido_paterno = ?',
            'apellido_materno = ?',
            'dni = ?',
            'correo = ?',
            'telefono = ?',
            'usuario = ?',
            'rol_id = ?'
        ];
        $params = [
            trim($data['nombres']),
            trim($data['apellido_paterno']),
            trim($data['apellido_materno']),
            trim($data['dni']),
            trim($data['correo']),
            trim($data['telefono'] ?? ''),
            trim($data['usuario']),
            $data['rol_id']
        ];

        // Si hay nueva contraseña, incluirla
        if (!empty($data['contraseña'])) {
            $campos[] = '`contraseña` = ?';
            $params[] = password_hash($data['contraseña'], PASSWORD_DEFAULT);
        }

        $params[] = $data['id'];

        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Elimina un usuario por su ID.
     *
     * @param int $id
     */
    public function eliminar(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Verifica si ya existe un DNI (puede excluir un ID dado para edición).
     *
     * @param string   $dni
     * @param int|null $excludeId
     * @return bool
     */
    public function existeDni(string $dni, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE dni = ?";
        $params = [$dni];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /**
     * Verifica si ya existe un correo (puede excluir un ID dado para edición).
     *
     * @param string   $correo
     * @param int|null $excludeId
     * @return bool
     */
    public function existeCorreo(string $correo, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE correo = ?";
        $params = [$correo];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /**
     * Verifica si ya existe un nombre de usuario (puede excluir un ID dado para edición).
     *
     * @param string   $usuario
     * @param int|null $excludeId
     * @return bool
     */
    public function existeUsuario(string $usuario, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $params = [$usuario];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /**
     * Devuelve el último ID auto-increment insertado.
     *
     * @return int
     */
    public function getUltimoIdInsertado(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
