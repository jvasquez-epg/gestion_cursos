<?php
// admin/views/usuarios_list.php
// Variables: $usuarios (array de usuarios), $rol (string), $BASE_URL

if (session_status() === PHP_SESSION_NONE)
    session_start();

$rolCapital = ucfirst($rol);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios - <?= htmlspecialchars($rolCapital) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <style>
        .table-usuarios {
            width: 100%;
            border-collapse: collapse;
            margin: 1.7rem 0 2rem 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 6px #2221;
        }

        .table-usuarios th,
        .table-usuarios td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .table-usuarios th {
            background: #3058a0;
            color: #fff;
            font-size: 1.04rem;
            font-weight: 600;
            letter-spacing: .5px;
        }

        .table-usuarios tbody tr:hover {
            background: #f1f6fb;
        }

        .btn-create {
            margin-bottom: 1rem;
            padding: .7em 1.7em;
            background: #1973ad;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1.02rem;
            transition: background 0.16s;
            font-weight: 500;
            display: inline-block;
        }

        .btn-create:hover {
            background: #134e7a;
        }

        .btn-create:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-accion {
            margin-right: .3rem;
            padding: .45em 1em;
            border: none;
            border-radius: 4px;
            font-size: .98rem;
            text-decoration: none;
            color: #fff;
            transition: background .18s;
            cursor: pointer;
        }

        .btn-edit {
            background: #30846b;
        }

        .btn-edit:hover {
            background: #22644e;
        }

        .btn-delete {
            background: #b73535;
        }

        .btn-delete:hover {
            background: #881e1e;
        }

        .btn-disabled {
            background: #ccc !important;
            cursor: not-allowed !important;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-actions {
            color: #666;
            font-style: italic;
        }
    </style>
    <!-- SweetAlert2 -->
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
        <h2><?= $rolCapital ?>s</h2>

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Botón crear solo para administrador y administrativo -->
        <?php if ($rol !== 'estudiante'): ?>
            <a class="btn-create" href="<?= BASE_URL ?>admin/usuarios.php?action=create&rol=<?= urlencode($rol) ?>">
                Crear <?= $rolCapital ?>
            </a>
        <?php else: ?>
            <p class="alert alert-error">
                Los estudiantes no pueden ser gestionados desde este módulo.
            </p>
        <?php endif; ?>

        <table class="table-usuarios">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                            No hay <?= strtolower($rolCapital) ?>s registrados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombres']) ?></td>
                            <td><?= htmlspecialchars($usuario['apellido_paterno']) ?></td>
                            <td><?= htmlspecialchars($usuario['apellido_materno']) ?></td>
                            <td><?= htmlspecialchars($usuario['dni']) ?></td>
                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td><?= htmlspecialchars($usuario['telefono'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                            <td>
                                <?php if ($rol === 'estudiante'): ?>
                                    <span class="no-actions">Solo lectura</span>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>admin/usuarios.php?action=edit&id=<?= $usuario['id'] ?>&rol=<?= urlencode($rol) ?>"
                                        class="btn-accion btn-edit">Editar</a>

                                    <?php if ($_SESSION['usuario_id'] != $usuario['id']): // No dejar eliminarse a sí mismo ?>
                                        <a href="#" class="btn-accion btn-delete" data-id="<?= $usuario['id'] ?>"
                                            data-rol="<?= htmlspecialchars($rol) ?>"
                                            data-nombre="<?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellido_paterno']) ?>">
                                            Eliminar
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-accion btn-disabled">No eliminar</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        // SweetAlert para eliminar (solo si no es estudiante)
        <?php if ($rol !== 'estudiante'): ?>
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const userId = this.dataset.id;
                    const rol = this.dataset.rol;
                    const nombre = this.dataset.nombre;

                    Swal.fire({
                        title: '¿Eliminar usuario?',
                        html: '¿Seguro que deseas eliminar a <b>' + nombre + '</b>?<br><br><strong>Esta acción no se puede deshacer.</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "<?= BASE_URL ?>admin/usuarios.php?action=delete&id=" + userId + "&rol=" + encodeURIComponent(rol);
                        }
                    });
                });
            });
        <?php endif; ?>
    </script>
    <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>