<?php
/*
 * Vista: estudiante/views/perfil.php
 *
 * Muestra y permite actualizar los datos del perfil del estudiante.
 * Incluye:
 * - Datos personales: nombres, apellidos, DNI (solo lectura)
 * - Formulario para editar correo y teléfono
 * - Validación con HTML5 y SweetAlert
 * - Enlace para cambiar contraseña
 *
 * Variables esperadas:
 * - $usuario : array con datos del estudiante autenticado
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil del Estudiante</title>
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
    <div class="card" style="max-width: 600px; margin: 2rem auto; padding: 2rem;">
      <h2>Mi Perfil</h2>
      <form id="perfilForm" method="POST" action="perfil.php?action=actualizar">
        <div class="form-group">
          <label>Nombre completo:</label>
          <p><strong><?= $usuario['nombres'] ?> <?= $usuario['apellido_paterno'] ?> <?= $usuario['apellido_materno'] ?></strong></p>
        </div>
        <div class="form-group">
          <label>DNI:</label>
          <p><strong><?= $usuario['dni'] ?></strong></p>
        </div>
        <div class="form-group">
          <label for="correo">Correo:</label>
          <input type="email" name="correo" id="correo" value="<?= $usuario['correo'] ?>" required>
        </div>
        <div class="form-group">
          <label for="telefono">Teléfono:</label>
          <input type="text" name="telefono" id="telefono" value="<?= $usuario['telefono'] ?>" required pattern="\d{9}" title="Debe tener 9 dígitos">
        </div>
        <div class="form-group" style="margin-top: 1rem;">
          <button type="submit" class="btn-primary">Guardar cambios</button>
          <a href="perfil.php?action=cambiar_contraseña" class="btn-secondary">Cambiar contraseña</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    <?php if (isset($_SESSION['perfil_msg'])): ?>
      Swal.fire({
        icon: 'success',
        title: '¡Actualizado!',
        text: '<?= $_SESSION['perfil_msg'] ?>',
      });
      <?php unset($_SESSION['perfil_msg']); ?>
    <?php endif; ?>
  </script>
</body>
</html>
