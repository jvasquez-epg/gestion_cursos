<?php
/*
 * admin/publicar.php
 * Punto de entrada para el módulo de publicación de cursos en periodo activo.
 * Variables y modelos involucrados:
 *   - PeriodoModel   → para obtener periodo activo y fechas de fase de asignación.
 *   - AsignacionModel→ para listar cursos asignados, sin docente e insuficientes.
 * Flujo:
 *   - Solo accesible para roles administrador y administrativo.
 *   - Muestra resumen tabulado de cursos según estado de asignación.
 *   - Acceso a la fase de asignación y navegación de regreso.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/models/AsignacionModel.php';
require_once __DIR__ . '/models/PeriodoModel.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (
  !isset($_SESSION['usuario_rol']) ||
  !in_array($_SESSION['usuario_rol'], ['administrador', 'administrativo'], true)
) {
  header('Location: ' . BASE_URL . 'login.php');
  exit;
}

$periodoModel = new PeriodoModel($pdo);
$asigModel = new AsignacionModel($pdo);
$periodo = $periodoModel->getActivo();
$hoy = new DateTime();

// ¿Estamos en fase de asignación?
$faseAsign = false;
if ($periodo) {
  $ini = new DateTime($periodo['inicio_asignacion_docentes']);
  $fin = new DateTime($periodo['fin_asignacion_docentes']);
  $faseAsign = ($hoy >= $ini && $hoy <= $fin);
}

$asignados = [];
$sinDocente = [];
$insuficientes = [];

if ($faseAsign) {
  // Todos los cursos que cumplen el mínimo
  $todos = $asigModel->getCursosParaAsignar(
    (int) $periodo['id'],
    (int) $periodo['minimo_solicitudes']
  );
  foreach ($todos as $c) {
    if (!empty($c['asignacion_id'])) {
      $asignados[] = $c;
    } else {
      $sinDocente[] = $c;
    }
  }
  // Insuficientes: 1 ≤ solicitudes < mínimo
  $stmt = $pdo->prepare("
        SELECT c.id AS curso_id, c.codigo, c.nombre, c.ciclo, COUNT(s.id) AS total_solicitudes
          FROM cursos c
          JOIN solicitudes s
            ON s.curso_id = c.id
           AND s.periodo_id = ?
         GROUP BY c.id
        HAVING COUNT(s.id) < ?
        ORDER BY total_solicitudes DESC, c.codigo
    ");
  $stmt->execute([(int) $periodo['id'], (int) $periodo['minimo_solicitudes']]);
  $insuficientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Publicar Cursos | Admin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <style>
    @keyframes fadeIn {
      from {
        opacity: 0
      }

      to {
        opacity: 1
      }
    }

    .tab-buttons {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
    }

    .tab-buttons button {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      background: var(--color-secondary);
      color: #fff;
      cursor: pointer;
      transition: background .3s, transform .2s;
    }

    .tab-buttons button.active {
      background: var(--color-primary);
      transform: translateY(-2px);
    }

    .tab-buttons button.disabled {
      background: #ccc;
      color: #888;
      cursor: not-allowed;
    }

    .tab-content {
      display: none;
      animation: fadeIn .3s ease-out;
    }

    .tab-content.active {
      display: block;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1rem;
    }

    .data-table th,
    .data-table td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }

    .data-table thead {
      background: #295959;
      color: #fff;
    }

    .floating-btn {
      position: fixed;
      bottom: 24px;
      right: 24px;
      padding: 12px 20px;
      background: var(--color-primary);
      color: #fff;
      border: none;
      border-radius: 50px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: transform .2s, box-shadow .2s;
      display: <?= $faseAsign ? 'block' : 'none' ?>;
    }

    .floating-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/../components/header_main.php'; ?>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../components/header_user.php'; ?>

  <main class="dashboard-main">
    <h2>Publicar Cursos</h2>

    <?php if (!$faseAsign): ?>
      <?php if (!$periodo): ?>
        <p>No hay periodo activo.</p>
      <?php else: ?>
        <p>La fase de asignación de docentes no está activa.</p>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>admin/asignaciones.php" class="btn-create">
        Volver a Asignación
      </a>

    <?php else: ?>

      <div class="tab-buttons">
        <button data-tab="asignados" class="<?= empty($asignados) ? 'disabled' : 'active' ?>" <?= empty($asignados) ? 'disabled' : '' ?>>
          Con Docente (<?= count($asignados) ?>)
        </button>
        <button data-tab="sinDocente" class="active">
          Sin Docente (<?= count($sinDocente) ?>)
        </button>
        <button data-tab="insuficientes" class="<?= empty($insuficientes) ? 'disabled' : '' ?>" <?= empty($insuficientes) ? 'disabled' : '' ?>>
          Insuficientes (<?= count($insuficientes) ?>)
        </button>
      </div>

      <!-- Con Docente -->
      <div id="asignados" class="tab-content <?= empty($asignados) ? '' : 'active' ?>">
        <?php if (empty($asignados)): ?>
          <p style="text-align:center;color:#666;">No hay cursos con docente asignado.</p>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Curso</th>
                <th>Ciclo</th>
                <th>Docente</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($asignados as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['codigo']) ?></td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['ciclo']) ?></td>
                  <td><?= htmlspecialchars($c['docente_nombre']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Sin Docente -->
      <div id="sinDocente" class="tab-content active">
        <?php if (empty($sinDocente)): ?>
          <p style="text-align:center;color:#666;">Todos los cursos ya tienen docente.</p>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Curso</th>
                <th>Ciclo</th>
                <th>Solicitudes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sinDocente as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['codigo']) ?></td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['ciclo']) ?></td>
                  <td><?= htmlspecialchars($c['total_solicitudes']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Insuficientes -->
      <div id="insuficientes" class="tab-content <?= empty($insuficientes) ? '' : 'active' ?>">
        <?php if (empty($insuficientes)): ?>
          <p style="text-align:center;color:#666;">No hay cursos con solicitudes insuficientes.</p>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Curso</th>
                <th>Ciclo</th>
                <th>Solicitudes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($insuficientes as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['codigo']) ?></td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['ciclo']) ?></td>
                  <td><?= htmlspecialchars($c['total_solicitudes']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <a href="<?= BASE_URL ?>admin/asignaciones.php" class="btn-create" style="margin-top:1rem;">
        Volver a Asignación
      </a>
    <?php endif; ?>
  </main>

  <script>
    // lógica de pestañas
    document.querySelectorAll('.tab-buttons button').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.disabled) return;
        document.querySelectorAll('.tab-buttons button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
      });
    });
    // publicar
    document.getElementById('btn-publicar')?.addEventListener('click', () => {
      window.location.href = '<?= BASE_URL ?>admin/publicar.php?action=publish';
    });
  </script>
</body>

</html>