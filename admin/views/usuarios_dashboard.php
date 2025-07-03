<?php
// admin/views/usuarios_dashboard.php
// Variables: $cuentas (array: conteo por rol), $BASE_URL

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard de Usuarios</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <style>
        .usuarios-cards {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .card-user {
            background: #fff;
            box-shadow: 0 2px 8px rgba(44, 54, 89, 0.07);
            border-radius: 8px;
            padding: 1.7rem 2.3rem;
            flex: 1 1 220px;
            min-width: 220px;
            max-width: 300px;
            transition: transform 0.15s, box-shadow 0.2s;
            text-align: center;
        }

        .card-user:hover {
            transform: translateY(-5px) scale(1.04);
            box-shadow: 0 4px 16px rgba(44, 54, 89, 0.16);
        }

        .card-user .num {
            font-size: 2.6rem;
            font-weight: 600;
            margin-bottom: .5rem;
            color: #2b566b;
        }

        .card-user .rol {
            font-size: 1.1rem;
            letter-spacing: 0.7px;
            color: #304057;
            font-weight: 500;
        }

        .btn-user-manage {
            margin-top: 1.2rem;
            padding: .6em 1.5em;
            border: none;
            border-radius: 4px;
            background: #185fad;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-user-manage:hover {
            background: #183b8f;
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
        <h2>Gestión de Usuarios</h2>
        <div class="usuarios-cards">
            <div class="card-user">
                <div class="num"><?= $cuentas['administrador'] ?? 0 ?></div>
                <div class="rol">Administradores</div>
                <a class="btn-user-manage" href="<?= BASE_URL ?>admin/usuarios.php?action=list&rol=administrador">
                    Gestionar
                </a>
            </div>
            <div class="card-user">
                <div class="num"><?= $cuentas['administrativo'] ?? 0 ?></div>
                <div class="rol">Administrativos</div>
                <a class="btn-user-manage" href="<?= BASE_URL ?>admin/usuarios.php?action=list&rol=administrativo">
                    Gestionar
                </a>
            </div>
            <div class="card-user">
                <div class="num"><?= $cuentas['estudiante'] ?? 0 ?></div>
                <div class="rol">Estudiantes</div>
                <a class="btn-user-manage" href="<?= BASE_URL ?>admin/usuarios.php?action=list&rol=estudiante">
                    Gestionar
                </a>
            </div>
        </div>
    </main>

    <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>