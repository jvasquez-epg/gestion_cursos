// assets/js/asignaciones.js

document.addEventListener('DOMContentLoaded', () => {
  const tbody        = document.querySelector('#tabla-cursos tbody');
  const searchCurso  = document.getElementById('search-curso');
  const filterCiclo  = document.getElementById('filter-ciclo');
  const modal        = document.getElementById('modal-asignacion');
  const modalBody    = document.getElementById('modal-body');
  const modalClose   = document.getElementById('modal-close');

  // 1) Filtrar la tabla de cursos
  function filtrarCursos() {
    const q     = searchCurso.value.toLowerCase();
    const ciclo = filterCiclo.value;
    Array.from(tbody.rows).forEach(row => {
      const okTexto = row.dataset.codigo.includes(q) || row.dataset.nombre.includes(q);
      const okCiclo = !ciclo || row.dataset.ciclo === ciclo;
      row.style.display = (okTexto && okCiclo) ? '' : 'none';
    });
  }
  searchCurso.addEventListener('input', filtrarCursos);
  filterCiclo.addEventListener('change', filtrarCursos);

  // 2) Abrir el modal (Asignar / Editar)
  document.querySelectorAll('.btn-assign').forEach(btn => {
    btn.addEventListener('click', async () => {
      const cursoId = btn.dataset.curso;
      const res     = await fetch(`${BASE_URL}admin/asignaciones.php?action=detalle&curso=${cursoId}`);
      modalBody.innerHTML = await res.text();
      modal.classList.add('show');
      initAsignacionModal();
    });
  });

  // 3) Eliminar asignación
document.querySelectorAll('button.btn-delete').forEach(btn => {
  btn.addEventListener('click', () => {
    const asigId = btn.dataset.delete;
    Swal.fire({
      title: '¿Borrar asignación?',
      text:  'Esta acción no se puede deshacer.',
      icon:  'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
    }).then(result => {
      if (result.isConfirmed) {
        window.location.href = `${BASE_URL}admin/asignaciones.php?action=delete&asignacion=${asigId}`;
      }
    });
  });
});


  // 4) Cerrar modal
  modalClose.addEventListener('click', () => {
    modal.classList.remove('show');
  });

  /**
   * Inicializa la lógica dentro del modal:
   * - Filtrado de docentes
   * - Selección única de docente
   * - Habilitar botón Guardar
   */
  window.initAsignacionModal = function() {
    const filterInput = document.getElementById('filter-docentes');
    const list        = document.getElementById('lista-docentes');
    const items       = Array.from(list.querySelectorAll('.docente-item'));
    const hiddenInput = document.getElementById('docente_id');
    const saveBtn     = document.getElementById('btn-save');

    // Filtrar docentes por nombre o DNI
    filterInput.addEventListener('input', () => {
      const q = filterInput.value.toLowerCase();
      items.forEach(li => {
        li.style.display = li.dataset.index.includes(q) ? '' : 'none';
      });
    });

    // Selección única de docente
    items.forEach(li => {
      li.addEventListener('click', () => {
        // Quitar selección anterior
        items.forEach(other => {
          other.classList.remove('selected');
          const badge = other.querySelector('.badge');
          badge.textContent = 'Seleccionar';
          badge.classList.remove('selected');
          badge.classList.add('selectable');
        });
        // Marcar el actual
        li.classList.add('selected');
        hiddenInput.value = li.dataset.id;
        const badge = li.querySelector('.badge');
        badge.textContent = 'Seleccionado';
        badge.classList.remove('selectable');
        badge.classList.add('selected');
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
      });
    });

    // Estado inicial del botón Guardar
    if (hiddenInput.value && Number(hiddenInput.value) > 0) {
      saveBtn.disabled = false;
      saveBtn.classList.add('enabled');
    }
  };
});
