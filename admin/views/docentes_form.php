<?php
/**
 * Vista de formulario para registrar o editar un docente.
 * Permite ingresar nombres, apellidos, DNI y tipo de contrato.
 * Variables requeridas:
 *   $title      — Título del formulario
 *   $formAction — Acción del formulario (ruta POST)
 *   $docente    — (opcional) Datos del docente para edición
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


 
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <style>
    form {
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 14px rgba(0, 0, 0, 0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin: 12px 0 4px;
      font-weight: 600;
    }
    input, select {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid var(--color-border);
      font-size: 1rem;
    }
    .form-actions {
      margin-top: 2rem;
      display: flex;
      justify-content: space-between;
    }
    .btn {
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
    }
    .btn-submit {
      background: var(--color-primary);
      color: #fff;
    }
    .btn-cancel {
      background: #ccc;
      color: #000;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <div class="hamburger">
      <span></span><span></span><span></span>
    </div>
  </button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <main class="dashboard-main">
    <form action="<?= $formAction ?>" method="post">
      <h2><?= $title ?></h2>

      <?php if (!empty($docente['id'])): ?>
        <input type="hidden" name="id" value="<?= (int)$docente['id'] ?>">
      <?php endif; ?>

      <label for="nombres">Nombres:</label>
      <input type="text" name="nombres" id="nombres" required value="<?= htmlspecialchars($docente['nombres'] ?? '') ?>">

      <label for="apellido_paterno">Apellido Paterno:</label>
      <input type="text" name="apellido_paterno" id="apellido_paterno" required value="<?= htmlspecialchars($docente['apellido_paterno'] ?? '') ?>">

      <label for="apellido_materno">Apellido Materno:</label>
      <input type="text" name="apellido_materno" id="apellido_materno" required value="<?= htmlspecialchars($docente['apellido_materno'] ?? '') ?>">

      <label for="dni">DNI:</label>
      <input type="text" name="dni" id="dni" required value="<?= htmlspecialchars($docente['dni'] ?? '') ?>">

      <label for="tipo">Tipo:</label>
      <select name="tipo" id="tipo" required>
        <option value="Nombrado" <?= (!empty($docente['tipo']) && $docente['tipo'] === 'Nombrado') ? 'selected' : '' ?>>Nombrado</option>
        <option value="Contratado" <?= (!empty($docente['tipo']) && $docente['tipo'] === 'Contratado') ? 'selected' : '' ?>>Contratado</option>
      </select>

      <div class="form-actions">
        <button type="submit" class="btn btn-submit">Guardar</button>
        <a href="<?= BASE_URL ?>admin/docentes.php" class="btn btn-cancel">Cancelar</a>
      </div>
    </form>
  </main>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>
