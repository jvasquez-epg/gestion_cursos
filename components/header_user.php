<?php
/*
 * components/header_user.php
 * Encabezado superior derecho con información del usuario en sesión.
 * Elementos mostrados:
 *   - Nombre de usuario autenticado
 *   - Menú de perfil con acceso a edición de perfil y cierre de sesión
 * Características:
 *   - Dropdown interactivo para el usuario
 *   - Confirmación de logout con SweetAlert2
 * Uso: Se incluye en todas las vistas que requieran sesión activa.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
?>
<header class="header-user">
  <div class="user-info">
    <span class="user-name">
      <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Invitado') ?>
    </span>
    <div class="user-menu">
      <img
        id="userIcon"
        class="user-icon"
        src="<?= BASE_URL ?>assets/img/icono_usuario.png"
        alt="Perfil"
      >
      <ul id="userDropdown" class="dropdown-menu">
        <li><a href="<?= BASE_URL ?>estudiante/perfil.php">Perfil</a></li>
        <li><a href="<?= BASE_URL ?>logout.php" class="logout-link">Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>
</header>

<!-- IMPORTANTE: Pon este bloque justo ANTES de cerrar </body> de tu plantilla -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    console.log('header_user.js cargado');

    // 1) Logout con SweetAlert
    document.querySelectorAll('.logout-link').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        Swal.fire({
          title: '¿Cerrar sesión?',
          text:  '¿Estás seguro que deseas salir del sistema?',
          icon:  'question',
          showCancelButton: true,
          confirmButtonText: 'Sí, salir',
          cancelButtonText:  'Cancelar'
        }).then(result => {
          if (result.isConfirmed) window.location.href = link.href;
        });
      });
    });

    // 2) Dropdown toggle
    const icon = document.getElementById('userIcon');
    const menu = document.getElementById('userDropdown');
    if (!icon || !menu) {
      console.error('No encontré userIcon o userDropdown');
      return;
    }

    icon.addEventListener('click', e => {
      e.stopPropagation();
      menu.classList.toggle('show');
    });

    menu.addEventListener('click', e => e.stopPropagation());

    // 3) Click fuera => cerrar (con delay)
    document.addEventListener('click', () => {
      setTimeout(() => menu.classList.remove('show'), 500);
    });
  });
</script>