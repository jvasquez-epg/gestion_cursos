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
  <style>
    .wrapper {
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
    .actions button,
    .actions a {
      margin-right: .4rem;
      padding: .3rem .6rem;
      font-size: .85rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-view {
      background: #0069d9;
      color: #fff;
    }

    .btn-del {
      background: #dc3545;
      color: #fff;
    }

    .btn-disabled {
      opacity: .5;
      cursor: not-allowed;
    }

    .zip-fab {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 999;
      background: #198754;
      color: #fff;
      width: 54px;
      height: 54px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgb(0 0 0 /.25);
      font-size: 1.4rem;
      cursor: pointer;
      transition: background .2s;
    }

    .zip-fab:hover {
      background: #146c43;
    }

    .historial h3 {
      margin-bottom: .5rem;
      font-size: 1.15rem;
    }

    .historial table td.actions a {
      background: #0d6efd;
      color: #fff;
    }

    .historial table td.actions a.resol {
      background: #6c757d;
    }

    .no-data {
      color: #6c757d;
      font-style: italic;
      text-align: center;
      padding: 1rem 0;
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
                <a class="btn-view" target="_blank" href="?action=ver&id=<?= $s['id'] ?>">Ver</a>

                <?php if ($puedeEliminar): ?>
                  <button class="btn-del" data-id="<?= $s['id'] ?>">Eliminar</button>
                <?php else: ?>
                  <button class="btn-del btn-disabled" disabled title="Fuera de rango">Eliminar</button>
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
                  <a href="?action=descargarZip&periodo_id=<?= $h['periodo_id'] ?>" class="btn-view"
                    title="Descargar ZIP">ZIP</a>
                  <a href="?action=descargarResolucion&periodo_id=<?= $h['periodo_id'] ?>" class="btn-view resol"
                    title="Resolución">Resol.</a>
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
    <a class="zip-fab" href="?action=descargarZip&periodo_id=<?= (int) $periodo['id'] ?>" title="Descargar ZIP"><span
        style="transform:rotate(90deg)">⤓</span></a>
  <?php endif; ?>

  <!-- ======= JS: eliminación + actualización del historial ======= -->
  <script>
    document.addEventListener('click', e => {
      if (!e.target.classList.contains('btn-del') || e.target.disabled) return;

      const id = e.target.dataset.id;
      const row = document.getElementById('row-' + id);
      const creditosFila = parseInt(row.dataset.creditos, 10) || 0;

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
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
          });
          const j = await res.json();
          if (!j.success) throw new Error(j.message || 'Error');

          /* 1) Remover fila */
          row.remove();

          /* 2) Actualizar historial */
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
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>