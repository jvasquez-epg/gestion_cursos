<?php
/*
 * Controlador para gestión del perfil del usuario estudiante.
 * Permite visualizar y actualizar correo y teléfono del usuario autenticado,
 * validando datos y mostrando mensajes de error o éxito.
 * Autor: Sistema Académico
 * Año: 2025
 */

require_once __DIR__ . '/../models/UsuarioModel.php';

class PerfilController {
    private UsuarioModel $usuarioModel;

    public function __construct(PDO $pdo) {
        $this->usuarioModel = new UsuarioModel($pdo);
    }

    public function index(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ../login.php');
            exit;
        }

        $usuario = $this->usuarioModel->obtenerPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            echo "Usuario no encontrado.";
            exit;
        }

        require_once __DIR__ . '/../views/perfil.php';
    }

    public function actualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: perfil.php');
            exit;
        }

        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $id = $_SESSION['usuario_id'];

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->alertAndBack("Correo no válido.");
            return;
        }

        if (!preg_match('/^\d{9}$/', $telefono)) {
            $this->alertAndBack("El teléfono debe tener 9 dígitos.");
            return;
        }

        $this->usuarioModel->actualizarCorreoTelefono($id, $correo, $telefono);

        $_SESSION['perfil_msg'] = 'Datos actualizados correctamente.';
        header('Location: perfil.php');
    }

    private function alertAndBack(string $msg): void {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '$msg',
            }).then(() => history.back());
        </script>";
        exit;
    }
}
