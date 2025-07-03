<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cursos disponibles</title>
  <!-- estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cursos_dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
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

  <div class="dashboard-main">
    <div class="top-bar">
      <div class="summary">
        <span style="width: 500px;" class="periodo">Periodo:
          <strong>
            <?= $periodo ? htmlspecialchars($periodo['anio'] . '-' . $periodo['periodo']) : '—' ?>
          </strong>
          <?php if ($periodo): ?>
            <?php
            $tipoPeriodo = in_array($periodo['periodo'], [1, 2]) ? 'Nivelación' : ($periodo['periodo'] == 3 ? 'Vacacional' : '');
            ?>
            <small style="display:block; font-size: 0.85em; color: #fff; font-weight: 600;">
              <?= $tipoPeriodo ?>
            </small>
          <?php endif; ?>
        </span>


        <span>Cursos restantes:
          <strong id="remainingCourses"><?= $maxCursos - count($cursosSolicitados) ?></strong>
          de <?= $maxCursos ?>
        </span>
        <span>Créditos restantes:
          <strong id="remainingCredits"><?= $maxCreditos - $creditosUtilizados ?></strong>
          de <?= $maxCreditos ?>
        </span>
        <span>Solicitudes Enviada:
          <strong id="pendingRequests"><?= count($cursosSolicitados) ?></strong> solicitudes
        </span>
      </div>
      <div class="controls" style="position:relative;">
        <input type="search" id="buscador" placeholder="Buscar por código o nombre del curso..."
          style="padding-right:32px;">
        <i data-lucide="search"
          style="position:absolute;right:8px;top:9px;width:20px;height:20px;pointer-events:none;color:#64748b;"></i>
      </div>
    </div>
    <div id="cardsContainer" class="cards">
      <?php if (empty($cursosDisponibles)): ?>
        <div class="no-cursos">
          <i data-lucide="book-x" style="width:42px;height:42px;"></i>
          No hay cursos disponibles que cumplan los requisitos.
        </div>
      <?php else: ?>
        <?php foreach ($cursosDisponibles as $c): ?>
          <div class="course-card" data-id="<?= $c['id'] ?>" data-creditos="<?= $c['creditos'] ?>">
            <div class="card-header">
              <h3><?= htmlspecialchars($c['codigo']) ?> – <?= htmlspecialchars($c['nombre']) ?></h3>
              <div class="course-checkbox" tabindex="0">
                <i data-lucide="check-square"></i>
              </div>
            </div>
            <div class="course-info">
              <span class="info-tag">Ciclo <?= $c['ciclo'] ?></span>
              <span class="info-tag"><?= $c['creditos'] ?> créditos</span>
            </div>
            <button class="btn-solicitar">
              Solicitar curso
            </button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Flotantes -->
  <div class="floating-elements">
    <div class="selection-summary" id="selectionSummary">
      <div class="summary-text">
        <strong id="selectedCount">0</strong> cursos seleccionados
      </div>
      <div class="summary-text">
        <strong id="selectedCredits">0</strong> créditos totales
      </div>
      <div class="limit-warning" id="limitWarning">
        <i data-lucide="alert-triangle"></i>
        Has alcanzado el límite disponible
      </div>
    </div>
    <button class="btn-solicitar-seleccionados" id="solicitarSeleccionados">
      <i data-lucide="check-circle"></i>
      Solicitar seleccionados
    </button>
  </div>

<script>
  // Parámetros iniciales desde PHP
  const maxCursos     = <?= $maxCursos ?>;
  const maxCreditos   = <?= $maxCreditos ?>;

  // Elementos del DOM
  const elRemCursos       = document.getElementById('remainingCourses');
  const elRemCreditos     = document.getElementById('remainingCredits');
  const elPendingRequests = document.getElementById('pendingRequests');
  const cardsContainer    = document.getElementById('cardsContainer');
  const solicitarBtn      = document.getElementById('solicitarSeleccionados');
  const selectionSummary  = document.getElementById('selectionSummary');
  const selectedCountEl   = document.getElementById('selectedCount');
  const selectedCreditsEl = document.getElementById('selectedCredits');
  const limitWarning      = document.getElementById('limitWarning');

  // Contadores numéricos
  let remainingCursos      = maxCursos - <?= count($cursosSolicitados) ?>;
  let remainingCreditos    = maxCreditos - <?= $creditosUtilizados ?>;
  let pendingRequestsCount = <?= count($cursosSolicitados) ?>;

  // Selección múltiple
  let selectedCourses = new Set();

  function actualizarBarra() {
    elRemCursos.textContent       = remainingCursos;
    elRemCreditos.textContent     = remainingCreditos;
    elPendingRequests.textContent = pendingRequestsCount;
  }

  function getCurrentSelectedCredits() {
    let total = 0;
    selectedCourses.forEach(id => {
      const card = document.querySelector(`[data-id="${id}"]`);
      if (card) total += parseInt(card.dataset.creditos, 10);
    });
    return total;
  }

  function actualizarSelectionSummary() {
    const count        = selectedCourses.size;
    const totalCredits = getCurrentSelectedCredits();
    const atLimit      = count >= remainingCursos || totalCredits >= remainingCreditos;

    selectedCountEl.textContent   = count;
    selectedCreditsEl.textContent = totalCredits;
    limitWarning.classList.toggle('visible', atLimit);

    // Mostrar u ocultar contenedor flotante
    if (count > 0) {
      selectionSummary.classList.add('visible');
      solicitarBtn.classList.add('visible');
    } else {
      selectionSummary.classList.remove('visible');
      solicitarBtn.classList.remove('visible');
    }

    // Deshabilitar botones individuales si hay selección
    document.querySelectorAll('.btn-solicitar').forEach(btn => {
      btn.disabled = count > 0;
    });

    verificarLimites();
  }

  function verificarLimites() {
    document.querySelectorAll('.course-card').forEach(card => {
      const id       = card.dataset.id;
      const creditos = parseInt(card.dataset.creditos, 10);
      if (selectedCourses.has(id)) {
        card.classList.remove('disabled');
        return;
      }
      const wouldExceedCourses = selectedCourses.size >= remainingCursos;
      const wouldExceedCredits = getCurrentSelectedCredits() + creditos > remainingCreditos;
      card.classList.toggle('disabled', wouldExceedCourses || wouldExceedCredits);
    });
  }

  // Filtro de búsqueda
  function filtrar() {
    const texto = document.getElementById('buscador').value.toLowerCase();
    let visible = 0;
    [...cardsContainer.children].forEach(card => {
      if (!card.classList.contains('course-card')) return;
      const match = card.textContent.toLowerCase().includes(texto);
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    const msg = cardsContainer.querySelector('.no-cursos');
    if (msg) msg.style.display = visible === 0 ? '' : 'none';
  }
  document.getElementById('buscador').addEventListener('input', filtrar);

  // Gestión de clicks en tarjetas
  cardsContainer.addEventListener('click', async e => {
    const card = e.target.closest('.course-card');
    if (!card || card.classList.contains('disabled')) return;

    const id       = card.dataset.id;
    const creditos = parseInt(card.dataset.creditos, 10);

    // Toggle selección
    if (e.target.closest('.course-checkbox')) {
      const checkbox = card.querySelector('.course-checkbox');
      if (selectedCourses.has(id)) {
        selectedCourses.delete(id);
        card.classList.remove('selected');
        checkbox.classList.remove('checked');
      } else {
        if (selectedCourses.size >= remainingCursos) {
          return Swal.fire('Límite de cursos', `Solo puedes seleccionar ${remainingCursos} más.`, 'warning');
        }
        if (getCurrentSelectedCredits() + creditos > remainingCreditos) {
          return Swal.fire('Créditos insuficientes', `Te quedan ${remainingCreditos}, pero este curso requiere ${creditos}.`, 'warning');
        }
        selectedCourses.add(id);
        card.classList.add('selected');
        checkbox.classList.add('checked');
      }
      return actualizarSelectionSummary();
    }

    // Solicitud individual
    if (e.target.closest('.btn-solicitar')) {
      if (remainingCursos <= 0) {
        return Swal.fire('Límite de cursos', 'Ya alcanzaste el máximo.', 'info');
      }
      if (remainingCreditos < creditos) {
        return Swal.fire('Créditos insuficientes', 'No cuentas con créditos suficientes.', 'info');
      }
      const { isConfirmed } = await Swal.fire({
        title: '¿Solicitar este curso?',
        text: 'El documento se firmará digitalmente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar',
        cancelButtonText: 'Cancelar'
      });
      if (!isConfirmed) return;

      try {
        const res = await fetch('?action=solicitar', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ curso_id: id })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Error desconocido');

        remainingCursos--;
        remainingCreditos -= creditos;
        pendingRequestsCount++;
        actualizarBarra();

        if (selectedCourses.has(id)) {
          selectedCourses.delete(id);
          actualizarSelectionSummary();
        }
        card.remove();
        filtrar();
        Swal.fire('¡Solicitado!', 'Tu solicitud fue registrada.', 'success');
      } catch (err) {
        Swal.fire('Error', err.message, 'error');
      }
    }
  });

  // Solicitar múltiples
  solicitarBtn.addEventListener('click', async () => {
    if (selectedCourses.size === 0) return;
    const totalCredits = getCurrentSelectedCredits();
    const { isConfirmed } = await Swal.fire({
      title: `¿Solicitar ${selectedCourses.size} cursos?`,
      text: `Total: ${totalCredits} créditos.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, solicitar todos',
      cancelButtonText: 'Cancelar'
    });
    if (!isConfirmed) return;

    try {
      const cursosArray = Array.from(selectedCourses);
      const res = await fetch('?action=solicitar_multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cursos_ids: cursosArray })
      });
      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'Error desconocido');

      remainingCursos     -= cursosArray.length;
      remainingCreditos   -= totalCredits;
      pendingRequestsCount += cursosArray.length;
      actualizarBarra();

      cursosArray.forEach(id => {
        const c = document.querySelector(`[data-id="${id}"]`);
        if (c) c.remove();
      });
      selectedCourses.clear();
      actualizarSelectionSummary();
      filtrar();
      Swal.fire('¡Solicitados!', `${cursosArray.length} cursos registrados.`, 'success');
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  // Arranque
  actualizarBarra();
  verificarLimites();
  filtrar();
</script>

  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>

</body>

</html>