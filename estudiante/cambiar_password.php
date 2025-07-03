<?php
// estudiante/cambiar_password.php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

// Si el método es POST, procesamos el cambio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual    = trim($_POST['actual']);
    $nueva     = trim($_POST['nueva']);
    $confirmar = trim($_POST['confirmar']);

    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        setMessage('Todos los campos son obligatorios.', 'warning');
        header('Location: cambiar_password.php');
        exit;
    }

    if ($nueva !== $confirmar) {
        setMessage('Las nuevas contraseñas no coinciden.', 'warning');
        header('Location: cambiar_password.php');
        exit;
    }

    if (strlen($nueva) < 6) {
        setMessage('La nueva contraseña debe tener al menos 6 caracteres.', 'warning');
        header('Location: cambiar_password.php');
        exit;
    }

    // Verificar contraseña actual
    $stmt = $pdo->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);
    $hashActual = $stmt->fetchColumn();

    if (!$hashActual || !password_verify($actual, $hashActual)) {
        setMessage('La contraseña actual es incorrecta.', 'error');
        header('Location: cambiar_password.php');
        exit;
    }

    // Todo correcto: actualizamos
    $nuevoHash = password_hash($nueva, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
    $stmt->execute([$nuevoHash, $usuarioId]);

    setMessage('Contraseña actualizada con éxito.', 'success');
    header('Location: cambiar_password.php');
    exit;
}

// Si es GET, mostramos la vista
$usuario = ['id' => $usuarioId]; // Solo se usa para mantener consistencia
require_once __DIR__ . '/views/cambiar_password.php';

// Función para mensajes flash con SweetAlert
function setMessage(string $mensaje, string $tipo = 'info') {
    $_SESSION['pass_msg'] = $mensaje;
    $_SESSION['pass_msg_type'] = $tipo;
}
