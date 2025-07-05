<?php
/*
 * components/sidebar.php
 * Menú lateral de navegación contextual según el rol de usuario en sesión.
 * Características:
 *   - Resalta el enlace activo según la URL.
 *   - Soporte para submenús desplegables (ejemplo: gestión de solicitudes en estudiante).
 *   - Muestra el rol del usuario en encabezado y footer.
 *   - Incluye estilos personalizados y accesibilidad.
 * Uso: Se incluye en todas las vistas protegidas del sistema.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$role = $_SESSION['usuario_rol'] ?? '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function isActiveLink(string $url, string $currentPath): bool
{
    return parse_url($url, PHP_URL_PATH) === parse_url($currentPath, PHP_URL_PATH);
}

$menuItems = [];

switch ($role) {
    case 'administrador':
        $menuItems = [
            'Periodos Académicos' => ['url' => BASE_URL . "admin/periodos.php"],
            'Solicitudes' => ['url' => BASE_URL . "admin/solicitudes.php"],
            'Asignar Docente' => ['url' => BASE_URL . "admin/asignaciones.php"],
            'Gestión de Usuarios' => ['url' => BASE_URL . "admin/usuarios.php"],
            'Gestión de Docente' => ['url' => BASE_URL . "admin/docentes.php"],
            'Reportes' => ['url' => BASE_URL . "admin/reportes.php"],
        ];
        break;

    case 'estudiante':
        $menuItems = [
            'Inicio' => ['url' => BASE_URL . "estudiante/dashboard.php"],
            'Gestión de solicitudes' => [
                'submenu' => [
                    'Solicitar cursos' => ['url' => BASE_URL . "estudiante/cursos.php"],
                    'Mis solicitudes' => ['url' => BASE_URL . "estudiante/solicitudes.php"],
                ]
            ],
            'Malla curricular' => ['url' => BASE_URL . "estudiante/malla.php"],
            'Progreso académico' => ['url' => BASE_URL . "estudiante/progreso.php"],
        ];
        break;
}
?>

<!-- Sidebar HTML -->
<nav class="sidebar" role="navigation" aria-label="Menú principal">
    <div class="sidebar-header">
        <h2 class="sidebar-title"><?= ucfirst(htmlspecialchars($role)) ?></h2>
    </div>
    <ul class="sidebar-menu" role="menubar">
        <?php foreach ($menuItems as $label => $item): ?>
            <?php if (isset($item['submenu'])): ?>
                <?php
                $submenuOpen = false;
                foreach ($item['submenu'] as $subItem) {
                    if (isActiveLink($subItem['url'], $currentPath)) {
                        $submenuOpen = true;
                        break;
                    }
                }
                $openClass = $submenuOpen ? ' open' : '';
                $ariaExpanded = $submenuOpen ? 'true' : 'false';
                ?>
                <li class="sidebar-submenu<?= $openClass ?>" role="none">
                    <button class="sidebar-link has-submenu <?= $submenuOpen ? 'active' : '' ?>" type="button" aria-expanded="<?= $ariaExpanded ?>">
                        <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                        <span class="arrow-icon" aria-hidden="true">▾</span>
                    </button>
                    <ul class="sidebar-submenu-list">
                        <?php foreach ($item['submenu'] as $subLabel => $subItem):
                            $active = isActiveLink($subItem['url'], $currentPath) ? 'active' : '';
                            $ariaCurr = $active ? 'aria-current="page"' : '';
                        ?>
                            <li role="none">
                                <a href="<?= htmlspecialchars($subItem['url']) ?>" class="sidebar-link <?= $active ?>" role="menuitem" <?= $ariaCurr ?>>
                                    <span class="sidebar-text"><?= htmlspecialchars($subLabel) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php else:
                $active = isActiveLink($item['url'], $currentPath) ? 'active' : '';
                $ariaCurr = $active ? 'aria-current="page"' : '';
            ?>
                <li role="none">
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="sidebar-link <?= $active ?>" role="menuitem" <?= $ariaCurr ?>>
                        <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <span class="user-role-badge"><?= ucfirst(htmlspecialchars($role)) ?></span>
    </div>
</nav>

<!-- Sidebar Styles -->
<style>
    .sidebar-menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 20px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.85);
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }

    .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .sidebar-submenu-list {
        display: none;
        flex-direction: column;
        padding-left: 1rem;
    }

    .sidebar-submenu.open>.sidebar-submenu-list {
        display: flex;
    }

    .has-submenu {
        background: none;
        border: none;
        cursor: pointer;
        width: 100%;
        text-align: left;
    }

    .arrow-icon {
        font-size: 12px;
        transition: transform 0.3s ease;
    }

    .sidebar-submenu.open .arrow-icon {
        transform: rotate(180deg);
    }

    .sidebar-footer {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
    }

    .sidebar-header {
        padding: 16px 20px 12px 24px;
        /* padding izquierdo aumentado */
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
    }

    .sidebar-title {
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    .user-role-badge {
        display: inline-block;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }
</style>

<!-- Submenú JS -->
<script>
    document.querySelectorAll('.sidebar-link.has-submenu').forEach(button => {
        button.addEventListener('click', () => {
            const parent = button.closest('.sidebar-submenu');
            const isOpen = parent.classList.contains('open');

            // Cierra todos
            document.querySelectorAll('.sidebar-submenu').forEach(item => item.classList.remove('open'));
            document.querySelectorAll('.sidebar-link.has-submenu').forEach(btn => btn.setAttribute('aria-expanded', 'false'));

            // Abre si estaba cerrado
            if (!isOpen) {
                parent.classList.add('open');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });
</script>