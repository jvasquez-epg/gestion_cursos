<?php
/*
 * admin/views/solicitudes.php
 * Vista de resumen de solicitudes por curso en el periodo activo.
 * Variables esperadas:
 *   $periodo — Periodo activo (array|null)
 *   $minReq  — Mínimo de solicitudes requerido (int)
 *   $cursos  — Listado de cursos con solicitudes (array)
 * Permite búsqueda y filtrado por ciclo, y muestra detalle de estudiantes por curso en modal.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Sólo admin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
  header('Location: ' . BASE_URL . 'login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Solicitudes por Curso | Admin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <style>
    .controls {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .controls input,
    .controls select {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
      padding: 12px 8px;
      border: 1px solid #ddd;
      text-align: center;
    }

    .data-table thead {
      background: #295959;
      color: #fff;
    }

    .btn-detail {
      padding: 6px 12px;
      background: #1a73e8;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .btn-detail:hover {
      background: #155ab0;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .modal.show {
      display: flex;
    }

    .modal-content {
      background: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      width: 90%;
      max-width: 800px;
      max-height: 90%;
      overflow: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .modal-header h3 {
      margin: 0;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
    }

    .inner-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .inner-table th,
    .inner-table td {
      padding: 8px;
      border: 1px solid #ccc;
    }

    .inner-table thead {
      background: #356e8b;
      color: #fff;
    }

    .search-inner {
      margin-top: 0.5rem;
      padding: 6px;
      width: 100%;
      box-sizing: border-box;
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
    <h2>Solicitudes por Curso</h2>

    <?php if (empty($periodo)): ?>
      <p>No hay periodo activo.</p>
    <?php else: ?>
      <div class="controls">
        <input type="text" id="search" placeholder="Buscar código o nombre…">
        <select id="filter-ciclo">
          <option value="">Todos los ciclos</option>
          <?php
          $ciclos = array_unique(array_column($cursos, 'ciclo'));
          sort($ciclos);
          foreach ($ciclos as $ciclo): ?>
            <option value="<?= $ciclo ?>"><?= $ciclo ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <table class="data-table" id="tabla-cursos">
        <thead>
          <tr>
            <th>CÓDIGO</th>
            <th>CURSO</th>
            <th>CICLO</th>
            <th>SOLICITUDES</th>
            <th>MÍNIMO REQ.</th>
            <th>Detalle</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cursos as $curso): ?>
            <tr data-codigo="<?= strtolower($curso['codigo']) ?>" data-nombre="<?= strtolower($curso['nombre']) ?>"
              data-ciclo="<?= $curso['ciclo'] ?>">
              <td><?= htmlspecialchars($curso['codigo']) ?></td>
              <td><?= htmlspecialchars($curso['nombre']) ?></td>
              <td><?= $curso['ciclo'] ?></td>
              <td><?= $curso['total_solicitudes'] ?></td>
              <td><?= $minReq ?></td>
              <td><button class="btn-detail" data-id="<?= $curso['id'] ?>">Ver detalle</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="modal" id="modal-detalle">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle de Solicitudes</h3>
          <button class="modal-close" id="modal-close">&times;</button>
        </div>
        <input type="text" id="search-inner" class="search-inner" placeholder="Buscar alumno por código, nombre o DNI…">
        <div id="modal-body"></div>
      </div>
    </div>

  </main>

  <script>
    const tabla = document.querySelector('#tabla-cursos tbody');
    const search = document.getElementById('search');
    const filterC = document.getElementById('filter-ciclo');
    const filtrar = () => {
      const term = search.value.toLowerCase();
      const cicloSel = filterC.value;
      Array.from(tabla.rows).forEach(row => {
        const matchSearch = row.dataset.codigo.includes(term) || row.dataset.nombre.includes(term);
        const matchCiclo = !cicloSel || row.dataset.ciclo === cicloSel;
        row.style.display = matchSearch && matchCiclo ? '' : 'none';
      });
    };
    search.addEventListener('input', filtrar);
    filterC.addEventListener('change', filtrar);

    const modal = document.getElementById('modal-detalle');
    const body = document.getElementById('modal-body');
    const close = document.getElementById('modal-close');

    document.querySelectorAll('.btn-detail').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        const res = await fetch(`<?= BASE_URL ?>admin/solicitudes.php?action=detalle&curso=${id}`);
        const html = await res.text();
        body.innerHTML = html;
        modal.classList.add('show');
        const innerSearch = document.getElementById('search-inner');
        const innerTable = document.querySelector('#inner-detalle tbody');
        innerSearch.addEventListener('input', () => {
          const q = innerSearch.value.toLowerCase();
          Array.from(innerTable.rows).forEach(r => {
            const text = Array.from(r.cells).map(c => c.textContent.toLowerCase()).join(' ');
            r.style.display = text.includes(q) ? '' : 'none';
          });
        });
      });
    });
    close.addEventListener('click', () => modal.classList.remove('show'));
  </script>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>