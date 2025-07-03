<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/conexion.php';
// Necesitamos el modelo para cargar permisos
require_once __DIR__ . '/admin/models/UsuarioModel.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpieza de entradas
    $input    = trim($_POST['usuario']);      // DNI o código universitario
    $password = trim($_POST['contrasena']);

    // Consulta unificada: estudiantes por código, resto por DNI
    $sql = "
        SELECT 
            u.id,
            u.nombres,
            u.apellido_paterno,
            u.apellido_materno,
            u.`contraseña` AS contrasena,
            r.nombre           AS rol_nombre
        FROM usuarios u
        INNER JOIN roles r      ON u.rol_id = r.id
        LEFT JOIN estudiantes e ON e.id     = u.id
        WHERE 
            (r.nombre = 'estudiante' 
             AND e.codigo_universitario = ?)
         OR ((r.nombre IN ('administrador','administrativo'))
             AND u.dni = ?)
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar existencia y contraseña
    if ($user && password_verify($password, $user['contrasena'])) {
        // Sesión básica
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = trim("{$user['nombres']} {$user['apellido_paterno']} {$user['apellido_materno']}");
        $_SESSION['usuario_rol']    = $user['rol_nombre'];

        // Instanciamos el modelo para permisos
        $usuarioModel = new UsuarioModel($pdo);

        if ($user['rol_nombre'] === 'administrador') {
            // El administrador tiene TODOS los permisos
            $todos = $usuarioModel->getAllPermisos();           // retorna [ ['id'=>..,'nombre'=>..], ... ]
            $_SESSION['permisos'] = array_column($todos, 'nombre');
        }
        elseif ($user['rol_nombre'] === 'administrativo') {
            // El administrativo solo los permisos asignados
            $permisosNombres = $usuarioModel->getPermisosPorUsuario($user['id']);
            // getPermisosPorUsuario devuelve [id, ...], pero queremos nombres:
            $stmt2 = $pdo->prepare("
                SELECT p.nombre 
                FROM permisos p 
                WHERE p.id IN (" . implode(',', array_map('intval', $permisosNombres)) . ")
                ORDER BY p.nombre
            ");
            $stmt2->execute();
            $_SESSION['permisos'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        }
        // (no necesitamos permisos para estudiantes)

        // Redirección según rol y permisos
        if ($user['rol_nombre'] === 'administrador') {
            header("Location: " . BASE_URL . "admin/periodos.php");
            exit;
        }
        if ($user['rol_nombre'] === 'administrativo') {
            // Mapeo permiso => URL
            $rutas = [
                'periodos'         => BASE_URL . 'admin/periodos.php',
                'solicitudes'      => BASE_URL . 'admin/solicitudes.php',
                'asignar_docente'  => BASE_URL . 'admin/asignaciones.php',
                'planilla_docente' => BASE_URL . 'admin/docentes.php',
            ];
            // Redirigir al primer permiso que coincida
            foreach ($_SESSION['permisos'] as $perm) {
                if (isset($rutas[$perm])) {
                    header("Location: " . $rutas[$perm]);
                    exit;
                }
            }
            // Si no tiene ninguno (caso raro), al dashboard de periodos
            header("Location: " . BASE_URL . "admin/periodos.php");
            exit;
        }
        if ($user['rol_nombre'] === 'estudiante') {
            header("Location: " . BASE_URL . "estudiante/dashboard.php");
            exit;
        }

        // Rol desconocido
        $error = "Rol de usuario no válido.";
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Gestión de Cursos</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Segoe+UI:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login_estilos.css">
</head>
<body>
  <div class="login-container">
    <div class="login-left">
      <div class="facultad-header">
        <img src="assets/img/logo_fisi_color.png" class="logo-unap" alt="Logo UNAP">
        <div class="facultad-text">
          Facultad de Ingeniería de<br>Sistemas e Informática
        </div>
      </div>
      <h1 class="login-title">
        GESTIÓN DE CURSOS DE NIVELACIÓN Y VACACIONAL
      </h1>

      <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="login-form" method="post" action="">
        <input 
          id="usuario"
          class="login-input" 
          type="text" 
          name="usuario" 
          placeholder="Usuario" 
          required 
          autocomplete="username"
        >
        <input 
          id="contrasena"
          class="login-input" 
          type="password" 
          name="contrasena" 
          placeholder="Contraseña" 
          required 
          autocomplete="current-password"
        >

        <button class="login-button" type="submit">INGRESAR</button>

        <p class="section-subtitle">
          Si aún no tienes cuenta, regístrate en 
          <a href="signup.php">SIGNUP</a>
        </p>
      </form>
    </div>
    <div class="login-right"></div>
  </div>
</body>
</html>
