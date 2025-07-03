<?php
// components/sidebar.php
// Sidebar dinámico según el rol de usuario con soporte para móvil

// Asegurarnos de que la sesión esté iniciada
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$role = $_SESSION['usuario_rol'] ?? '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// Helper para marcar el link activo
function isActiveLink(string $url, string $currentPath): bool
{
    return parse_url($url, PHP_URL_PATH) === parse_url($currentPath, PHP_URL_PATH);
}

$menuItems = [];

switch ($role) {
    case 'administrador':
        // El administrador ve todo sin restricciones
        $menuItems = [
            'Periodos' => ['url' => BASE_URL . "admin/periodos.php"],
            'Solicitudes' => ['url' => BASE_URL . "admin/solicitudes.php"],
            'Asignar Docente' => ['url' => BASE_URL . "admin/asignaciones.php"],
            'Gestión de Usuarios' => ['url' => BASE_URL . "admin/usuarios.php"],
            'Planilla Docente' => ['url' => BASE_URL . "admin/docentes.php"],
        ];
        break;

    case 'administrativo':
        // Permisos que cargaste en login (p.ej. ["Asignar docente","Crear/Editar periodos",...])
        $permisosSesion = array_map('strtolower', $_SESSION['permisos'] ?? []);

        // Mapa de sección → permisos necesarios
        $config = [
            'Periodos' => [
                'url' => BASE_URL . "admin/periodos.php",
                'perms' => ['ver periodos', 'crear/editar periodos']
            ],
            'Solicitudes' => [
                'url' => BASE_URL . "admin/solicitudes.php",
                'perms' => ['ver solicitudes', 'gestionar solicitudes']
            ],
            'Asignar Docente' => [
                'url' => BASE_URL . "admin/asignaciones.php",
                'perms' => ['asignar docente']
            ],
            'Planilla Docente' => [
                'url' => BASE_URL . "admin/docentes.php",
                'perms' => ['planilla docente']
            ],
        ];

        // Filtrar sólo lo autorizado
        $autorizados = [];
        foreach ($config as $label => $cfg) {
            foreach ($cfg['perms'] as $permReq) {
                if (in_array($permReq, $permisosSesion, true)) {
                    $autorizados[$label] = ['url' => $cfg['url']];
                    break;
                }
            }
        }

        if (!empty($autorizados)) {
            // “Inicio” apunta a la primera sección autorizada
            $firstUrl = reset($autorizados)['url'];
            $menuItems['Inicio'] = ['url' => $firstUrl];
            // Luego el resto de secciones
            foreach ($autorizados as $label => $it) {
                $menuItems[$label] = $it;
            }
        } else {
            // Fallback: al menos que vea periodos
            $menuItems['Inicio'] = ['url' => BASE_URL . "admin/periodos.php"];
        }
        break;

    case 'estudiante':
        $menuItems = [
            'Inicio' => ['url' => BASE_URL . "estudiante/dashboard.php"],
            'Solicitar cursos' => ['url' => BASE_URL . "estudiante/cursos.php"],
            'Solicitudes Enviadas' => ['url' => BASE_URL . "estudiante/solicitudes.php"],
            'Malla curricular' => ['url' => BASE_URL . "estudiante/malla.php"],
            'Progreso Académico' => ['url' => BASE_URL . "estudiante/progreso.php"],
        ];
        break;
}

// Render del menú
?>
<nav class="sidebar" role="navigation" aria-label="Menú principal">
    <div class="sidebar-header">
        <h2 class="sidebar-title"><?= ucfirst(htmlspecialchars($role)) ?></h2>
    </div>
    <ul class="sidebar-menu" role="menubar">
        <?php foreach ($menuItems as $label => $item):
            $active = isActiveLink($item['url'], $currentPath) ? 'active' : '';
            $ariaCurr = $active ? 'aria-current="page"' : '';
            ?>
            <li role="none">
                <a href="<?= htmlspecialchars($item['url']) ?>" class="sidebar-link <?= $active ?>" role="menuitem"
                    <?= $ariaCurr ?> tabindex="0">
                    <span class="sidebar-icon" aria-hidden="true"></span>
                    <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <span class="user-role-badge"><?= ucfirst(htmlspecialchars($role)) ?></span>
    </div>
</nav>

<style>
    /* Estilos adicionales para el sidebar */
    .sidebar-header {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
    }

    .sidebar-title {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .sidebar-menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        padding: 8px 20px;
    }

    .sidebar-link.active {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-icon {
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .sidebar-text {
        flex: 1;
        font-size: 14px;
        color: #fff;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
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
        letter-spacing: 0.5px;
    }
</style>