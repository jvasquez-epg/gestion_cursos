<?php
// estudiante/views/progreso_dashboard.php
// Variable disponible: $progreso (array con info del curso y estado)

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Progreso Académico</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">

  <style>
    .dashboard-main {
      padding: 2rem;
      background-color: var(--color-login-bg);
      min-height: 100vh;
    }

    h1 {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--color-primary);
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .resumen-box {
      display: flex;
      justify-content: space-around;
      background-color: var(--color-white);
      padding: 1rem 2rem;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      margin-bottom: 2rem;
    }

    .resumen-item {
      text-align: center;
    }

    .resumen-item h3 {
      color: var(--color-secondary);
      margin-bottom: 0.2rem;
    }

    .resumen-item span {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--color-primary-dark);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.95rem;
      background-color: var(--color-white);
      box-shadow: var(--shadow-sm);
    }

    th,
    td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--color-border-light);
      text-align: left;
    }

    thead {
      background-color: var(--color-primary);
      color: var(--color-light-text);
    }

    .estado-cumplido {
      color: var(--success-color);
      font-weight: bold;
    }

    .estado-pendiente {
      color: var(--danger-color);
      font-weight: bold;
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

  <div class="dashboard-main">
    <h1>Progreso Académico</h1>

    <?php
    $creditosTotales = 0;
    $creditosCumplidos = 0;

    foreach ($progreso as $curso) {
      $creditosTotales += (int) $curso['creditos'];
      if (strtolower($curso['estado']) === 'cumplido') {
        $creditosCumplidos += (int) $curso['creditos'];
      }
    }

    $porcentaje = $creditosTotales > 0 ? round(($creditosCumplidos / $creditosTotales) * 100, 1) : 0;
    ?>

    <div class="resumen-box">
      <div class="resumen-item">
        <h3>Créditos Completados</h3>
        <span><?= $creditosCumplidos ?></span>
      </div>
      <div class="resumen-item">
        <h3>Créditos Totales</h3>
        <span><?= $creditosTotales ?></span>
      </div>
      <div class="resumen-item">
        <h3>Progreso</h3>
        <span><?= $porcentaje ?>%</span>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Curso</th>
          <th>Créditos</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($progreso as $curso): ?>
          <tr>
            <td><?= htmlspecialchars($curso['codigo']) ?></td>
            <td><?= htmlspecialchars($curso['nombre']) ?></td>
            <td><?= (int) $curso['creditos'] ?></td>
            <td class="<?= strtolower($curso['estado']) === 'cumplido' ? 'estado-cumplido' : 'estado-pendiente' ?>">
              <?= ucfirst(strtolower($curso['estado'])) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>