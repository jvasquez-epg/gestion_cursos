<?php
/**
 * Script para cerrar la sesión de usuario de forma segura.
 * Elimina los datos de sesión, destruye la cookie asociada
 * y redirige al usuario a la pantalla de login.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sesión Cerrada</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Sesión cerrada',
    text: '¡Has salido correctamente!',
    confirmButtonText: 'OK',
    allowOutsideClick: false
  }).then(() => {
    window.location.href = 'login.php';
  });
</script>
</body>
</html>
