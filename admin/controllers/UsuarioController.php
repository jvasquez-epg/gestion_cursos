<?php
// admin/controllers/UsuarioController.php

require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {
    private UsuarioModel $model;
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        // Asegurarnos de que la sesión está iniciada (para mensajes flash y credenciales)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->pdo   = $pdo;
        $this->model = new UsuarioModel($pdo);
    }

    /**
     * Dashboard general: muestra conteo por roles
     */
    public function dashboard() {
        $cuentas = $this->model->contarPorRoles();
        require __DIR__ . '/../views/usuarios_dashboard.php';
    }

    /**
     * Lista de usuarios filtrada por rol
     */
    public function index(string $rol) {
        $usuarios = $this->model->getByRolNombre($rol);
        require __DIR__ . '/../views/usuarios_list.php';
    }

    /**
     * Formulario para crear un nuevo usuario.
     * Cargamos también el listado de permisos para mostrarlos.
     */
    public function create(string $rol) {
        $usuario          = null;
        $errores          = [];
        $permisos         = $this->model->getAllPermisos();
        $usuarioPermisos  = [];
        require __DIR__ . '/../views/usuarios_form.php';
    }

    /**
     * Formulario para editar un usuario existente.
     * Cargamos sus datos, la lista de todos los permisos,
     * y cuáles ya están asignados a ese usuario.
     */
    public function edit(int $id, string $rol) {
        $usuario = $this->model->getById($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header("Location: " . BASE_URL . "admin/usuarios.php?action=list&rol=$rol");
            exit;
        }
        $errores         = [];
        $permisos        = $this->model->getAllPermisos();
        $usuarioPermisos = $this->model->getPermisosPorUsuario($id);
        require __DIR__ . '/../views/usuarios_form.php';
    }

    /**
     * Procesa la creación de un usuario.
     * Valida, crea, asigna permisos y redirige.
     */
    public function store(array $data) {
        $rol      = $data['rol'] ?? '';
        $errores  = $this->validarDatos($data);

        // Siempre recargamos permisos para el form en caso de error
        $permisos        = $this->model->getAllPermisos();
        $usuarioPermisos = $data['permisos'] ?? [];

        if (!empty($errores)) {
            $usuario = null;
            require __DIR__ . '/../views/usuarios_form.php';
            return;
        }

        try {
            // Para administrador y administrativo: el nombre de usuario será el DNI
            if (in_array($rol, ['administrador', 'administrativo'])) {
                $data['usuario'] = $data['dni'];
            }

            $data['rol_id'] = $this->getRolIdPorNombre($rol);
            $this->model->crear($data);

            // Obtener ID del nuevo usuario y asignar permisos
            $newUserId = $this->model->getUltimoIdInsertado();
            $this->model->asignarPermisosUsuario($newUserId, $data['permisos'] ?? []);

            $_SESSION['success'] = "Usuario registrado correctamente.";
            header("Location: " . BASE_URL . "admin/usuarios.php?action=list&rol=$rol");
            exit;
        } catch (Exception $e) {
            $errores[] = 'Error al crear el usuario: ' . $e->getMessage();
            $usuario = null;
            require __DIR__ . '/../views/usuarios_form.php';
        }
    }

    /**
     * Procesa la actualización de un usuario existente.
     * Valida, actualiza, reasigna permisos y redirige.
     */
    public function update(int $id, array $data) {
        $rol      = $data['rol'] ?? '';
        $errores  = $this->validarDatos($data, $id);

        // En caso de error recargamos todo para el form
        $usuario          = $this->model->getById($id);
        $permisos         = $this->model->getAllPermisos();
        $usuarioPermisos  = $data['permisos'] ?? [];

        if (!empty($errores)) {
            require __DIR__ . '/../views/usuarios_form.php';
            return;
        }

        try {
            // Para administrador y administrativo: el nombre de usuario será el DNI
            if (in_array($rol, ['administrador', 'administrativo'])) {
                $data['usuario'] = $data['dni'];
            }

            $data['rol_id'] = $this->getRolIdPorNombre($rol);
            $data['id']     = $id;
            $this->model->actualizar($data);

            // Reasignar permisos
            $this->model->asignarPermisosUsuario($id, $data['permisos'] ?? []);

            $_SESSION['success'] = "Usuario actualizado correctamente.";
            header("Location: " . BASE_URL . "admin/usuarios.php?action=list&rol=$rol");
            exit;
        } catch (Exception $e) {
            $errores[] = 'Error al actualizar el usuario: ' . $e->getMessage();
            require __DIR__ . '/../views/usuarios_form.php';
        }
    }

    /**
     * Elimina un usuario (con restricciones).
     */
    public function delete(int $id, string $rol) {
        if ($_SESSION['usuario_id'] == $id) {
            $_SESSION['error'] = "No puedes eliminarte a ti mismo.";
        } elseif ($rol === 'estudiante') {
            $_SESSION['error'] = "Los estudiantes no pueden ser eliminados.";
        } else {
            try {
                $this->model->eliminar($id);
                $_SESSION['success'] = "Usuario eliminado correctamente.";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al eliminar el usuario: " . $e->getMessage();
            }
        }
        header("Location: " . BASE_URL . "admin/usuarios.php?action=list&rol=$rol");
        exit;
    }

    /**
     * Valida los datos del formulario (creación y edición).
     */
    private function validarDatos(array $data, ?int $id = null): array {
        $errores = [];

        // Campos requeridos
        foreach (['nombres','apellido_paterno','apellido_materno','dni','correo','rol'] as $campo) {
            if (empty(trim($data[$campo] ?? ''))) {
                $errores[] = "El campo " . str_replace('_',' ',$campo) . " es requerido.";
            }
        }

        // DNI
        if (!empty($data['dni'])) {
            if (!preg_match('/^\d{8}$/',$data['dni'])) {
                $errores[] = "El DNI debe tener exactamente 8 dígitos.";
            } elseif ($this->model->existeDni($data['dni'],$id)) {
                $errores[] = "Ya existe un usuario con este DNI.";
            }
        }

        // Correo
        if (!empty($data['correo'])) {
            if (!filter_var($data['correo'],FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El formato del correo electrónico no es válido.";
            } elseif ($this->model->existeCorreo($data['correo'],$id)) {
                $errores[] = "Ya existe un usuario con este correo electrónico.";
            }
        }

        // Teléfono (opcional)
        if (!empty($data['telefono']) && !preg_match('/^\d{9}$/',$data['telefono'])) {
            $errores[] = "El teléfono debe tener exactamente 9 dígitos.";
        }

        // Usuario (solo para estudiantes)
        if (!empty($data['usuario']) && ($data['rol'] ?? '') === 'estudiante') {
            if ($this->model->existeUsuario($data['usuario'],$id)) {
                $errores[] = "Ya existe un usuario con este nombre de usuario.";
            }
        }

        // Contraseña
        if ($id === null) {
            // creación
            if (empty($data['contraseña'])) {
                $errores[] = "La contraseña es requerida.";
            } elseif (strlen($data['contraseña']) < 6) {
                $errores[] = "La contraseña debe tener al menos 6 caracteres.";
            }
        } else {
            // edición
            if (!empty($data['contraseña']) && strlen($data['contraseña']) < 6) {
                $errores[] = "La contraseña debe tener al menos 6 caracteres.";
            }
        }

        return $errores;
    }

    /**
     * Obtiene el rol_id a partir de su nombre.
     */
    private function getRolIdPorNombre(string $rol): int {
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE nombre = ?");
        $stmt->execute([$rol]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : 0;
    }
}
