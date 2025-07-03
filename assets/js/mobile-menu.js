// assets/js/mobile-menu.js
document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if (!toggle || !sidebar || !overlay) {
    console.error("Elementos del menú móvil no encontrados");
    return;
  }

  // Estado inicial
  let isMenuOpen = false;

  // Función para abrir el sidebar
  function openSidebar() {
    sidebar.classList.add("active");
    overlay.classList.add("active");
    toggle.classList.add("active");
    toggle.setAttribute("aria-label", "Cerrar menú");
    toggle.setAttribute("aria-expanded", "true");
    
    // Prevenir scroll del body cuando el menú está abierto
    document.body.style.overflow = "hidden";
    isMenuOpen = true;
    
    // Focus en el primer enlace del menú para accesibilidad
    const firstLink = sidebar.querySelector(".sidebar-link");
    if (firstLink) {
      setTimeout(() => firstLink.focus(), 100);
    }
  }

  // Función para cerrar el sidebar
  function closeSidebar() {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
    toggle.classList.remove("active");
    toggle.setAttribute("aria-label", "Abrir menú");
    toggle.setAttribute("aria-expanded", "false");
    
    // Restaurar scroll del body
    document.body.style.overflow = "";
    isMenuOpen = false;
    
    // Devolver focus al botón toggle
    toggle.focus();
  }

  // Función para alternar el sidebar
  function toggleSidebar() {
    if (isMenuOpen) {
      closeSidebar();
    } else {
      openSidebar();
    }
  }

  // Event listener para el botón toggle
  toggle.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    toggleSidebar();
  });

  // Event listener para el overlay
  overlay.addEventListener("click", function (e) {
    e.preventDefault();
    closeSidebar();
  });

  // Cerrar sidebar al hacer clic en un enlace (solo en móvil)
  const sidebarLinks = sidebar.querySelectorAll(".sidebar-link");
  sidebarLinks.forEach(function (link) {
    link.addEventListener("click", function () {
      // Solo cerrar en móvil/tablet
      if (window.innerWidth <= 768) {
        setTimeout(() => closeSidebar(), 150); // Pequeño delay para mejor UX
      }
    });
  });

  // Manejar redimensionamiento de ventana
  let resizeTimer;
  window.addEventListener("resize", function () {
    // Debounce para mejor rendimiento
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (window.innerWidth > 768 && isMenuOpen) {
        closeSidebar();
      }
    }, 100);
  });

  // Manejar navegación con teclado
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && isMenuOpen) {
      e.preventDefault();
      closeSidebar();
    }
    
    // Navegación con Tab dentro del menú
    if (isMenuOpen && e.key === "Tab") {
      const focusableElements = sidebar.querySelectorAll(
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];
      
      if (e.shiftKey) {
        // Shift + Tab
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        }
      } else {
        // Tab
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    }
  });

  // Cerrar menú si se hace clic fuera (adicional al overlay)
  document.addEventListener("click", function (e) {
    if (
      isMenuOpen &&
      !sidebar.contains(e.target) &&
      !toggle.contains(e.target) &&
      window.innerWidth <= 768
    ) {
      closeSidebar();
    }
  });

  // Manejar orientación en dispositivos móviles
  window.addEventListener("orientationchange", function () {
    setTimeout(() => {
      if (window.innerWidth > 768 && isMenuOpen) {
        closeSidebar();
      }
    }, 100);
  });

  // Función para detectar swipe gestures (opcional)
  let touchStartX = 0;
  let touchEndX = 0;

  sidebar.addEventListener("touchstart", function (e) {
    touchStartX = e.changedTouches[0].screenX;
  });

  sidebar.addEventListener("touchend", function (e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  });

  function handleSwipe() {
    const swipeThreshold = 50;
    const swipeLength = touchEndX - touchStartX;
    
    // Swipe hacia la izquierda para cerrar
    if (swipeLength < -swipeThreshold && isMenuOpen) {
      closeSidebar();
    }
  }

  // Mejorar la accesibilidad
  function initAccessibility() {
    // Agregar atributos ARIA
    toggle.setAttribute("aria-controls", "sidebar-menu");
    toggle.setAttribute("aria-expanded", "false");
    sidebar.setAttribute("id", "sidebar-menu");
    sidebar.setAttribute("aria-hidden", "true");
    
    // Cuando el menú se abre/cierra, actualizar aria-hidden
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.attributeName === "class") {
          const isActive = sidebar.classList.contains("active");
          sidebar.setAttribute("aria-hidden", isActive ? "false" : "true");
        }
      });
    });
    
    observer.observe(sidebar, { attributes: true });
  }

  // Inicializar accesibilidad
  initAccessibility();

  // Función de utilidad para debugging (solo en desarrollo)
  if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") {
    window.debugMobileMenu = function () {
      console.log("Estado del menú:", {
        isOpen: isMenuOpen,
        sidebarClasses: sidebar.className,
        overlayClasses: overlay.className,
        toggleClasses: toggle.className,
        windowWidth: window.innerWidth
      });
    };
  }
});