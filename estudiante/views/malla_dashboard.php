<?php
// estudiante/views/malla_dashboard.php
// Variable disponible: $ciclos  (array ciclo => lista de cursos)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Malla Curricular</title>

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">

  <style>

  </style>
</head>

<body>
  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false"
    aria-controls="sidebar-menu">
    <div class="hamburger">
      <span></span><span></span><span></span>
    </div>
  </button>

  <!-- Overlay para móvil -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="dashboard-main">

    <h1>Malla Curricular</h1>

    <?php if (empty($ciclos)): ?>
      <p>No se encontraron cursos en tu malla curricular.</p>
    <?php else: ?>
      <?php ksort($ciclos);
      foreach ($ciclos as $cicloNum => $lista): ?>
        <h2>Ciclo <?= $cicloNum ?></h2>
        <table>
          <thead>
            <tr>
              <th>Código</th>
              <th>Asignatura</th>
              <th>Créditos</th>
              <th>Prerrequisitos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lista as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['codigo']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= (int) $c['creditos'] ?></td>
                <td><?= htmlspecialchars($c['prerequisitos']) ?: '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
  <!-- /dashboard-main -->

</body>

</html>