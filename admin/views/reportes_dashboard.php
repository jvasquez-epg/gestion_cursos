<?php

/*
 * admin/views/reportes_dashboard.php
 * Vista de dashboard de reportes por periodo académico.
 * Variables esperadas:
 *   $periodos   — Lista de periodos disponibles (array)
 *   $periodoId  — ID del periodo seleccionado (int)
 *   $datos      — Listado de reportes y resoluciones con URL de ver/descargar y estado de datos (array)
 * Incluye filtro de periodo, tarjetas de acceso a reportes, botones de acción y leyenda de tipos.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Reportes</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <style>
    .dashboard-main {
      padding: 1.8rem 2rem;
    }

    h2 {
      margin-bottom: 1rem;
    }

    .filtro-periodo {
      margin-bottom: 1.6rem;
    }

    .filtro-periodo select {
      padding: 0.4em 1em;
      font-size: 1rem;
    }

    .report-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
      gap: 1.4rem;
    }

    .report-card {
      padding: 1.3rem 1rem;
      border-radius: 1rem;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      font-family: 'Segoe UI', sans-serif;
    }

    .report-card h3 {
      font-size: 1.05rem;
      margin-bottom: 1.2rem;
      min-height: 48px;
    }

    .report-card .btn {
      padding: .5em 1.1em;
      font-size: .9rem;
      border-radius: 1.5em;
      border: none;
      color: #fff;
      cursor: pointer;
      margin: .3rem auto;
      width: 90%;
      max-width: 180px;
    }

    .report-card.resolucion {
      background-color: #28a74515;
      border-left: 4px solid #28a745;
    }

    .report-card.reporte {
      background-color: #0d6efd15;
      border-left: 4px solid #0d6efd;
    }

    .btn-ver {
      background-color: #00bcd4;
    }

    .btn-ver:hover {
      background-color: #0199ac;
    }

    .btn-descargar {
      background-color: #007bff;
    }

    .btn-descargar:hover {
      background-color: #005ec2;
    }

    .leyenda {
      margin-top: 2rem;
      font-size: .95rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .leyenda span {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
    }

    .cuadro {
      display: inline-block;
      width: 18px;
      height: 18px;
      border-radius: 4px;
    }

    .verde {
      background-color: #28a745;
    }

    .azul {
      background-color: #0d6efd;
    }

    @media (max-width: 600px) {
      .report-card h3 {
        font-size: .95rem;
        min-height: auto;
      }
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <div class="dashboard-main">
    <h2>Reportes por periodo</h2>

    <form class="filtro-periodo" method="get" action="reportes.php">
      <label for="periodo_id">Seleccione un periodo:</label>
      <select name="periodo_id" id="periodo_id" onchange="this.form.submit()">
        <?php foreach ($periodos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $p['id'] == $periodoId ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['label']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <div class="report-grid">
      <?php foreach ($datos as $r): ?>
        <div class="report-card <?= $r['tipo'] === 'resolucion' ? 'resolucion' : 'reporte' ?>">
          <h3><?= htmlspecialchars($r['titulo']) ?></h3>
          <a href="<?= htmlspecialchars($r['ver_url']) ?>" class="btn btn-ver">VER</a>
          <?php if ($r['hay_datos']): ?>
            <a href="<?= htmlspecialchars($r['descargar_url']) ?>" class="btn btn-descargar">DESCARGAR</a>
          <?php else: ?>
            <button class="btn btn-descargar" disabled style="opacity: 0.6; cursor: not-allowed;">SIN DATOS</button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>


    <div class="leyenda">
      <span><span class="cuadro verde"></span> Resoluciones</span>
      <span><span class="cuadro azul"></span> Reportes</span>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
</body>

</html>