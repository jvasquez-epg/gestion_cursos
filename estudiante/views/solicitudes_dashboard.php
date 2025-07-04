<?php
// estudiante/views/solicitudes_dashboard.php
// Variables recibidas:
//   $periodo, $solicitudesActuales, $puedeEliminar,
//   $puedeDescargarZip, $historial
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Mis Solicitudes</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    /* --- Unificación visual de botones de acción --- */
    .btn-table-action {
      display: inline-flex;
      align-items: center;
      gap: .4em;
      padding: .4em .9em;
      font-size: .93em;
      background: #f8f9fa;
      border: 1px solid #ced4da;
      color: #222;
      border-radius: 6px;
      text-decoration: none;
      transition: background .2s, box-shadow .2s;
      cursor: pointer;
      margin-right: .3em;
      outline: none;
      vertical-align: middle;
    }

    .btn-table-action i {
      font-size: 1em;
    }

    .btn-table-action:disabled,
    .btn-table-action[disabled] {
      opacity: .55;
      cursor: not-allowed !important;
      pointer-events: none;
    }

    .btn-table-action:hover:not(:disabled) {
      background: #e2e6ea;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
    }

    .btn-table-delete {
      background: #dc3545;
      color: #fff;
      border-color: #dc3545;
    }

    .btn-table-delete:hover {
      background: #b52a37;
    }

    .btn-table-view {
      background: #6c757d;
      color: #fff;
      border-color: #6c757d;
    }

    .btn-table-view:hover {
      background: #565e64;
    }

    .btn-table-download {
      background: #0d6efd;
      color: #fff;
      border-color: #0d6efd;
    }

    .btn-table-download:hover {
      background: #084298;
    }

    .btn-table-export {
      background: #198754;
      color: #fff;
      border-color: #198754;
    }

    .btn-table-export:hover {
      background: #146c43;
    }

    /* Botón flotante ZIP */
    .zip-fab {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 999;
      box-shadow: 0 4px 12px rgb(0 0 0 /.25);
      border-radius: 50%;
      width: 54px;
      height: 54px;
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    .dashboard-main {
      padding: 1.5rem 2rem;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    h2,
    h3 {
      margin: .3rem 0 1rem;
      font-size: 1.35rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: .95rem;
      margin-bottom: 2rem;
    }

    th,
    td {
      padding: .45em .65em;
      border-bottom: 1px solid #e3e3e3;
    }

    .actions {
      white-space: nowrap;
    }

    .no-data {
      color: #6c757d;
      font-style: italic;
      text-align: center;
      padding: 1rem 0;
    }

    @media (max-width: 650px) {
      .dashboard-main {
        padding: 1rem .4rem;
      }

      table,
      th,
      td {
        font-size: .93rem;
      }
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

    <!-- ======= Solicitudes actuales ======= -->
    <h2>Solicitudes del periodo&nbsp;
      <?= $periodo ? htmlspecialchars($periodo['anio'] . '-' . $periodo['periodo']) : '—' ?>
    </h2>

    <?php if (empty($solicitudesActuales)): ?>
      <div class="no-data">No tienes solicitudes registradas en este periodo.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Código</th>
            <th>Curso</th>
            <th>Fecha</th>
            <th class="actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($solicitudesActuales as $s): ?>
            <tr id="row-<?= $s['id'] ?>" data-creditos="<?= $s['creditos'] ?>">
              <td><?= htmlspecialchars($s['codigo']) ?></td>
              <td><?= htmlspecialchars($s['nombre']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></td>
              <td class="actions">
                <a class="btn-table-action btn-table-view" target="_blank" href="?action=ver&id=<?= $s['id'] ?>" title="Ver solicitud">
                  <i class="fa fa-eye"></i><span>Ver</span>
                </a>
                <?php if ($puedeEliminar): ?>
                  <button class="btn-table-action btn-table-delete" data-id="<?= $s['id'] ?>" type="button">
                    <i class="fa fa-trash-alt"></i><span>Eliminar</span>
                  </button>
                <?php else: ?>
                  <button class="btn-table-action btn-table-delete" disabled title="Fuera de rango" type="button">
                    <i class="fa fa-trash-alt"></i><span>Eliminar</span>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- ======= Historial ======= -->
    <div class="historial">
      <h3>Historial de periodos</h3>
      <?php if (empty($historial)): ?>
        <div class="no-data">Aún no tienes historial.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Periodo</th>
              <th>Total solicitudes</th>
              <th class="actions">Descargas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($historial as $h): ?>
              <tr data-periodo="<?= $h['periodo_id'] ?>">
                <td><?= htmlspecialchars($h['periodo_label']) ?></td>
                <td class="cnt"><?= $h['total'] ?></td>
                <td class="actions">
                  <a href="?action=descargarZip&periodo_id=<?= $h['periodo_id'] ?>"
                    class="btn-table-action btn-table-download"
                    title="Descargar ZIP">
                    <i class="fa fa-file-archive"></i><span>ZIP</span>
                  </a>
                  <a href="?action=descargarResolucion&periodo_id=<?= $h['periodo_id'] ?>"
                    class="btn-table-action btn-table-export"
                    title="Resolución">
                    <i class="fa fa-file-pdf"></i><span>Resol.</span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- ======= Botón flotante ZIP (tras cierre de envíos) ======= -->
  <?php if ($periodo && $puedeDescargarZip): ?>
    <a class="btn-table-action btn-table-download zip-fab"
      href="?action=descargarZip&periodo_id=<?= (int) $periodo['id'] ?>"
      title="Descargar ZIP">
      <i class="fa fa-file-archive"></i>
    </a>
  <?php endif; ?>

  <!-- ======= JS: eliminación + actualización del historial ======= -->
  <script>
    document.addEventListener('click', e => {
      if (!e.target.closest('.btn-table-delete') || e.target.disabled) return;

      const btn = e.target.closest('.btn-table-delete');
      const id = btn.dataset.id;
      const row = document.getElementById('row-' + id);

      Swal.fire({
        title: '¿Eliminar solicitud?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async r => {
        if (!r.isConfirmed) return;

        try {
          const res = await fetch('?action=eliminar', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id
            })
          });
          const j = await res.json();
          if (!j.success) throw new Error(j.message || 'Error');

          // Remover fila
          row.remove();

          // Actualizar historial
          const histRow = document.querySelector(
            'tr[data-periodo="<?= $periodo ? (int) $periodo['id'] : 0 ?>"]'
          );
          if (histRow) {
            const cntCell = histRow.querySelector('.cnt');
            const nuevo = parseInt(cntCell.textContent, 10) - 1;
            if (nuevo > 0) {
              cntCell.textContent = nuevo;
            } else {
              histRow.remove();
            }
          }

          Swal.fire('Eliminado', 'Solicitud cancelada.', 'success');
        } catch (err) {
          Swal.fire('Error', err.message, 'error');
        }
      });
    });
  </script>

  <?php if (!empty($_GET['error_resolucion']) && isset($_GET['anio'], $_GET['per'])): ?>
    <script>
      Swal.fire({
        icon: 'info',
        title: 'Resolución no disponible',
        text: 'Espere a que finalice el periodo de asignación para consultar la resolución del periodo <?= htmlspecialchars($_GET['anio']) ?>-<?= htmlspecialchars($_GET['per']) ?>.',
        confirmButtonText: 'Entendido'
      });
    </script>
  <?php endif; ?>


  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
</body>

</html>