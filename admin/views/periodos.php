<?php
// admin/views/periodos.php
// Variables esperadas:
//   $periodoActivo (array|null)
//   $historial     (array)
//   $error         (string|null)
//   $success       (string|null)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Periodos | Admin</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/periodos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <div class="hamburger"><span></span><span></span><span></span></div>
  </button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <main class="dashboard-main">
    <!-- PERIODO ACTUAL -->
    <section class="period-current">
      <h2>Periodo Actual</h2>
      <?php if (!$periodoActivo): ?>
        <p>No hay periodo activo.</p>
        <a href="<?= BASE_URL ?>admin/periodos.php?action=create" class="btn-action btn-edit">
          <i class="fa fa-plus"></i><span>Crear Periodo</span>
        </a>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Año</th>
              <th>Periodo</th>
              <th>Solicitudes</th>
              <th>Cursos Asignados</th>
              <th>Env. Inicio</th>
              <th>Env. Fin</th>
              <th>Apert. Inicio</th>
              <th>Apert. Fin</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= htmlspecialchars($periodoActivo['anio']) ?></td>
              <td><?= htmlspecialchars($periodoActivo['periodo']) ?></td>
              <td><?= (int) ($periodoActivo['total_solicitudes'] ?? 0) ?></td>
              <td><?= (int) ($periodoActivo['cursos_asignados'] ?? 0) ?></td>
              <td><?= (new DateTime($periodoActivo['inicio_envio_solicitudes']))->format('d/m/Y H:i') ?></td>
              <td><?= (new DateTime($periodoActivo['fin_envio_solicitudes']))->format('d/m/Y H:i') ?></td>
              <td><?= (new DateTime($periodoActivo['inicio_asignacion_docentes']))->format('d/m/Y H:i') ?></td>
              <td><?= (new DateTime($periodoActivo['fin_asignacion_docentes']))->format('d/m/Y H:i') ?></td>
              <td>
                <div class="action-buttons">
                  <?php if ($periodoActivo['estado'] === 'activo'): ?>
                    <a href="<?= BASE_URL ?>admin/periodos.php?action=edit&id=<?= (int) $periodoActivo['id'] ?>" class="btn-table-action btn-table-edit" title="Editar Periodo <?= $periodoActivo['anio'] ?>-<?= $periodoActivo['periodo'] ?>">
                      <i class="fa fa-edit"></i><span>Editar</span>
                    </a>
                  <?php else: ?>
                    &mdash;
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <!-- HISTORIAL DE PERIODOS -->
    <?php if (!empty($historial)): ?>
      <section class="period-history">
        <h2>Historial de Periodos</h2>
        <table class="data-table">
          <thead>
            <tr>
              <th>Año</th>
              <th>Periodo</th>
              <th>Solicitudes</th>
              <th>Cursos Asignados</th>
              <th>Resolución</th>
              <th>Solicitudes</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($historial as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['anio']) ?></td>
                <td><?= htmlspecialchars($p['periodo']) ?></td>
                <td><?= (int) ($p['total_solicitudes'] ?? 0) ?></td>
                <td><?= (int) ($p['cursos_asignados'] ?? 0) ?></td>

                <!-- Columna Resolución -->
                <td>
                  <div class="action-buttons">
                    <?php if (!empty($p['ultima_resolucion'])): ?>
                      <!-- Ya hay PDF: Descargar -->
                      <a href="<?= BASE_URL ?>uploads/resoluciones/<?= urlencode($p['ultima_resolucion']) ?>"
                        download
                        class="btn-table-action btn-table-download"
                        title="Descargar Resolución <?= $p['anio'] ?>-<?= $p['periodo'] ?>">
                        <i class="fa fa-file-download"></i><span>Descargar</span>
                      </a>
                    <?php elseif ($p['estado'] === 'cerrado'): ?>
                      <!-- Cerrado y sin PDF: Generar -->
                      <a href="<?= BASE_URL ?>admin/periodos.php?action=resolucion&id=<?= (int)$p['id'] ?>"
                        class="btn-table-action btn-table-export"
                        title="Generar Resolución <?= $p['anio'] ?>-<?= $p['periodo'] ?>">
                        <i class="fa fa-file-pdf"></i><span>Generar</span>
                      </a>
                    <?php else: ?>
                      &mdash;
                    <?php endif; ?>
                  </div>
                </td>

                <td>
                  <div class="action-buttons">
                    <?php if ($p['estado'] === 'cerrado'): ?>
                      <a href="<?= BASE_URL ?>admin/periodos.php?action=export&id=<?= (int) $p['id'] ?>"
                        class="btn-table-action btn-table-export"
                        title="Exportar Periodo <?= $p['anio'] ?>-<?= $p['periodo'] ?>">
                        <i class="fa fa-file-export"></i><span>Exportar</span>
                      </a>
                    <?php else: ?>
                      &mdash;
                    <?php endif; ?>
                  </div>
                </td>

                <td>
                  <div class="action-buttons">
                    <?php if ($p['estado'] === 'activo' && !$this->periodoModel->hasSolicitudes($p['id'])): ?>
                      <a href="<?= BASE_URL ?>admin/periodos.php?action=delete&id=<?= (int) $p['id'] ?>" class="btn-table-action btn-table-delete" data-anio="<?= $p['anio'] ?>" data-periodo="<?= $p['periodo'] ?>" title="Eliminar Periodo <?= $p['anio'] ?>-<?= $p['periodo'] ?>">
                        <i class="fa fa-trash-alt"></i><span>Eliminar</span>
                      </a>
                    <?php else: ?>
                      &mdash;
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php endif; ?>
  </main>

  <?php if (!empty($error) || !empty($success)): ?>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
          icon: <?= !empty($success) ? "'success'" : "'error'" ?>,
          title: <?= !empty($success) ? "'¡Éxito!'" : "'¡Error!'" ?>,
          text: <?= json_encode($success ?? $error) ?>,
          timer: 3000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end',
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        });
      });
    </script>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.btn-table-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const url = this.href;
          const anio = this.dataset.anio;
          const periodo = this.dataset.periodo;

          Swal.fire({
            title: `¿Eliminar periodo ${anio}-${periodo}?`,
            text: '¡Atención! Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = url;
            }
          });
        });
      });
    });
  </script>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
</body>

</html>