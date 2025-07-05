<?php
/*
 * Vista: cambiar_password.php
 * 
 * Formulario para que el estudiante actualice su contraseña:
 * - Solicita la contraseña actual, la nueva y la confirmación
 * - Valida coincidencia de contraseñas en cliente con JavaScript
 * - Usa SweetAlert para alertas visuales y mensajes flash
 * - Incluye diseño responsivo con estilos perfil.css, main.css y palette.css
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
 ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cambiar Contraseña</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/perfil.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <div class="dashboard-main">
    <div class="card" style="max-width: 500px; margin: 2rem auto; padding: 2rem;">
      <h2>Cambiar contraseña</h2>
      <form id="passwordForm" method="POST" action="cambiar_password.php">
        <div class="form-group">
          <label for="actual">Contraseña actual</label>
          <input type="password" name="actual" id="actual" required>
        </div>

        <div class="form-group">
          <label for="nueva">Nueva contraseña</label>
          <input type="password" name="nueva" id="nueva" required minlength="6">
        </div>

        <div class="form-group">
          <label for="confirmar">Confirmar nueva contraseña</label>
          <input type="password" name="confirmar" id="confirmar" required>
        </div>

        <button type="submit" class="btn-primary">Actualizar contraseña</button>
        <a href="perfil.php" class="btn-secondary">Ir a Perfil</a>

      </form>
    </div>
  </div>

  <script>
    // Validación de coincidencia antes de enviar
    document.getElementById("passwordForm").addEventListener("submit", function (e) {
      const nueva = document.getElementById("nueva").value;
      const confirmar = document.getElementById("confirmar").value;

      if (nueva !== confirmar) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Error',
          text: 'Las contraseñas no coinciden.',
        });
      }
    });

    // Mensajes flash desde PHP
    <?php if (isset($_SESSION['pass_msg'])): ?>
      Swal.fire({
        icon: '<?= $_SESSION['pass_msg_type'] ?? "info" ?>',
        title: 'Aviso',
        text: '<?= $_SESSION['pass_msg'] ?>'
      });
      <?php unset($_SESSION['pass_msg'], $_SESSION['pass_msg_type']); ?>
    <?php endif; ?>
  </script>
</body>

</html>