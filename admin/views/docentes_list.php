<?php
// admin/views/docentes_list.php
if (session_status() === PHP_SESSION_NONE) session_start();
$isEditable = in_array($_SESSION['usuario_rol'] ?? '', ['administrador', 'administrativo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Docentes</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/usuarios.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <style>
    .user-tools {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .user-tools input {
      padding: 8px;
      width: 300px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    .btn-create {
      padding: 8px 20px;
      background: #28a745;
      color: #fff;
      border-radius: 20px;
      text-decoration: none;
      transition: 0.3s;
    }
    .btn-create:hover {
      background: #218838;
    }
    .btn-accion {
      padding: 5px 12px;
      margin: 0 4px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
    }
    .btn-edit { background: #007bff; color: #fff; }
    .btn-edit:hover { background: #0069d9; }
    .btn-delete { background: #dc3545; color: #fff; }
    .btn-delete:hover { background: #c82333; }
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
    <h2>Gestión de Docentes</h2>

    <div class="user-tools">
      <input type="text" id="search-docente" placeholder="Buscar por nombre o DNI…">
      <?php if ($isEditable): ?>
        <a class="btn-create" href="<?= BASE_URL ?>admin/docentes.php?action=create">Nuevo Docente</a>
      <?php endif; ?>
    </div>

    <table class="data-table" id="tabla-docentes">
      <thead>
        <tr>
          <th>DNI</th><th>Nombre Completo</th><th>Tipo</th>
          <?php if ($isEditable): ?><th>Acciones</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($docentes as $d): ?>
        <tr data-search="<?= strtolower($d['nombres'] . ' ' . $d['apellido_paterno'] . ' ' . $d['apellido_materno'] . ' ' . $d['dni']) ?>">
          <td><?= htmlspecialchars($d['dni']) ?></td>
          <td><?= htmlspecialchars("{$d['nombres']} {$d['apellido_paterno']} {$d['apellido_materno']}") ?></td>
          <td><?= htmlspecialchars($d['tipo']) ?></td>
          <?php if ($isEditable): ?>
          <td>
            <a href="<?= BASE_URL ?>admin/docentes.php?action=edit&id=<?= $d['id'] ?>" class="btn-accion btn-edit">Editar</a>
            <button class="btn-accion btn-delete" onclick="eliminarDocente(<?= $d['id'] ?>)">Eliminar</button>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('search-docente').addEventListener('input', function () {
      const q = this.value.toLowerCase();
      const filas = document.querySelectorAll('#tabla-docentes tbody tr');
      filas.forEach(fila => {
        fila.style.display = fila.dataset.search.includes(q) ? '' : 'none';
      });
    });

    function eliminarDocente(id) {
      Swal.fire({
        title: '¿Eliminar docente?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = `<?= BASE_URL ?>admin/docentes.php?action=delete&id=${id}`;
        }
      });
    }
  </script>

     <?php if (!empty($_SESSION['error'])): ?>
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $_SESSION['error'] ?>'
        });
        </script>
        <?php unset($_SESSION['error']); endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '<?= $_SESSION['success'] ?>'
        });
        </script>
    <?php unset($_SESSION['success']); endif; ?>

    <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>
