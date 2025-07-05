<?php
/**
 * Vista principal de asignación de docentes a cursos.
 * Presenta la lista de cursos con opción para buscar, filtrar por ciclo,
 * asignar, editar o eliminar docentes, y ver cursos publicados.
 * Variables requeridas:
 *   $periodo (array|null) — Periodo académico activo
 *   $cursos  (array)      — Cursos disponibles para asignación
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


// admin/views/asignaciones.php
// Variables esperadas:
//   $periodo (array|null)
//   $cursos  (array)

if (session_status() === PHP_SESSION_NONE)
  session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
  header('Location: ' . BASE_URL . 'login.php');
  exit;
}
$hoy = new DateTime();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Asignación de Docentes | Admin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/asignaciones.css">
  <style>
    /* FAB Publicar Cursos */
    #fab-publicar {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: var(--color-primary);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 14px 20px;
      font-size: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      transition: background var(--transition);
      z-index: 1100;
      display: none;
      /* solo en fase asignación */
    }

    #fab-publicar:hover {
      background: var(--color-primary-dark);
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <h2>Asignación de Docentes</h2>

    <?php if (empty($periodo)): ?>
      <p>No hay periodo activo.</p>
    <?php else:
      $ini = new DateTime($periodo['inicio_asignacion_docentes']);
      $fin = new DateTime($periodo['fin_asignacion_docentes']);
      if ($hoy < $ini): ?>
        <p>Aún no se puede asignar docentes. Comienza el <?= $ini->format('d/m/Y') ?>.</p>
      <?php elseif ($hoy > $fin): ?>
        <p>El periodo de asignación finalizó el <?= $fin->format('d/m/Y') ?>.</p>
      <?php else: ?>
        <div class="controls">
          <input id="search-curso" type="text" placeholder="Buscar curso…">
          <select id="filter-ciclo">
            <option value="">Todos los ciclos</option>
            <?php foreach (array_unique(array_column($cursos, 'ciclo')) as $c): ?>
              <option value="<?= $c ?>"><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <table class="data-table" id="tabla-cursos">
          <thead>
            <tr>
              <th>Código</th>
              <th>Curso</th>
              <th>Ciclo</th>
              <th>Solicitudes</th>
              <th>Docente</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cursos as $c): ?>
              <tr data-codigo="<?= strtolower($c['codigo']) ?>" data-nombre="<?= strtolower($c['nombre']) ?>"
                data-ciclo="<?= $c['ciclo'] ?>">
                <td><?= htmlspecialchars($c['codigo']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['ciclo']) ?></td>
                <td><?= htmlspecialchars($c['total_solicitudes']) ?></td>
                <td><?= htmlspecialchars($c['docente_nombre'] ?? '–') ?></td>
                <td>
                  <button class="btn-assign" data-curso="<?= $c['curso_id'] ?>" data-asig="<?= $c['asignacion_id'] ?? '' ?>">
                    <?= $c['asignacion_id'] ? 'Editar' : 'Asignar' ?>
                  </button>
                  <?php if (!empty($c['asignacion_id'])): ?>
                    <button class="btn-delete" data-delete="<?= $c['asignacion_id'] ?>">
                      Eliminar
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>


        <!-- FAB Publicar Cursos -->
        <button id="fab-publicar">Ver Cursos Publicados</button>

        <!-- Modal -->
        <div class="modal" id="modal-asignacion">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Asignar / Editar Docente</h3>
              <button class="modal-close" id="modal-close">&times;</button>
            </div>
            <div id="modal-body"></div>
          </div>
        </div>
      <?php endif;
    endif; ?>
  </main>

  <script>
    const BASE_URL = '<?= BASE_URL ?>';
    document.addEventListener('DOMContentLoaded', () => {
      const tbody = document.querySelector('#tabla-cursos tbody');
      const search = document.getElementById('search-curso');
      const filtro = document.getElementById('filter-ciclo');
      const fab = document.getElementById('fab-publicar');
      let dirty = false;

      // Mostrar FAB en fase de asignación
      fab.style.display = 'block';

      // Filtrar cursos
      function filtrar() {
        const q = search.value.toLowerCase();
        const ciclo = filtro.value;
        Array.from(tbody.rows).forEach(r => {
          const okText = r.dataset.codigo.includes(q) || r.dataset.nombre.includes(q);
          const okCiclo = !ciclo || r.dataset.ciclo === ciclo;
          r.style.display = (okText && okCiclo) ? '' : 'none';
        });
      }
      search.addEventListener('input', filtrar);
      filtro.addEventListener('change', filtrar);

      // Modal
      const modal = document.getElementById('modal-asignacion');
      const body = document.getElementById('modal-body');
      const close = document.getElementById('modal-close');

      // Asignar / Editar
      document.querySelectorAll('.btn-assign').forEach(btn => {
        btn.addEventListener('click', async () => {
          dirty = true; updateFab();
          const cursoId = btn.dataset.curso;
          const res = await fetch(`${BASE_URL}admin/asignaciones.php?action=detalle&curso=${cursoId}`);
          body.innerHTML = await res.text();
          modal.classList.add('show');
          // inicializa búsqueda/selección dentro del modal
          window.initAsignacionModal();
        });
      });

      // Eliminar con SweetAlert2
      document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
          dirty = true; updateFab();
          Swal.fire({
            title: 'Eliminar asignación?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              const asigId = btn.dataset.delete;
              window.location.href = `${BASE_URL}admin/asignaciones.php?action=delete&asignacion=${asigId}`;
            }
          });
        });
      });

      // Cerrar modal
      close.addEventListener('click', () => modal.classList.remove('show'));

      // FAB acción
      fab.addEventListener('click', () => {
        window.location.href = `${BASE_URL}admin/publicar.php`;
      });

      // Actualiza texto del FAB
      function updateFab() {
        fab.textContent = dirty
          ? 'Actualizar Publicación'
          : 'Ver Cursos Publicados';
      }
      updateFab();
    });
  </script>
  <script src="<?= BASE_URL ?>assets/js/asignaciones.js"></script>
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>


</body>

</html>