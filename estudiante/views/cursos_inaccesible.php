<?php

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cursos</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <style>
    .mensaje-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 160px);
      /* descarta headers */
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 1.2rem;
      color: #555;
      text-align: center;
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
  <div class="mensaje-wrapper">
    <?= htmlspecialchars($mensaje ?? 'No disponible en este momento.') ?>
  </div>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>