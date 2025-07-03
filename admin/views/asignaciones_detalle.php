<?php
// admin/views/asignaciones_detalle.php
// Variables esperadas:
//   $docentes (array) — listado de docentes
//   $curso    (array) — datos del curso con claves:
//                      curso_id, codigo, nombre, ciclo, creditos (opcional),
//                      asignacion_id, docente_id

if (empty($curso)): ?>
  <p>Curso no encontrado o periodo inactivo.</p>
<?php else: ?>
  <div class="asig-det">
    <p><strong>Curso:</strong>
      <?= htmlspecialchars($curso['codigo'] . ' – ' . $curso['nombre']) ?></p>
    <p><strong>Ciclo:</strong> <?= htmlspecialchars($curso['ciclo']) ?></p>
    <?php if (isset($curso['creditos'])): ?>
      <p><strong>Créditos:</strong> <?= (int)$curso['creditos'] ?></p>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>admin/asignaciones.php?action=store">
      <input type="hidden" name="curso_id"      value="<?= (int)$curso['curso_id'] ?>">
      <input type="hidden" name="asignacion_id" value="<?= (int)($curso['asignacion_id'] ?? 0) ?>">
      <input type="hidden" id="docente_id" name="docente_id" value="<?= (int)($curso['docente_id'] ?? 0) ?>">

      <input
        type="text"
        id="filter-docentes"
        class="input-search"
        placeholder="Buscar docente por nombre o DNI…"
      >

      <div class="lista" id="lista-docentes">
        <?php foreach ($docentes as $d):
          $full  = "{$d['nombres']} {$d['apellido_paterno']} {$d['apellido_materno']} ({$d['dni']})";
          $index = strtolower($full);
          $sel   = (isset($curso['docente_id']) && $curso['docente_id'] == $d['id']);
        ?>
          <div
            class="docente-item <?= $sel ? 'selected' : '' ?>"
            data-id="<?= $d['id'] ?>"
            data-index="<?= $index ?>"
          >
            <span class="nombre"><?= htmlspecialchars($full) ?></span>
            <button type="button" class="badge <?= $sel ? 'selected' : 'selectable' ?>">
              <?= $sel ? 'Seleccionado' : 'Seleccionar' ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <button
        type="submit"
        class="btn-save"
        id="btn-save"
        disabled
      ><?= $curso['asignacion_id'] ? 'Actualizar' : 'Asignar' ?></button>
    </form>
  </div>

  <style>
    .asig-det { font-family: sans-serif; }
    .asig-det p { margin: .5rem 0; color: #333; }
    .input-search {
      width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;
      margin: .5rem 0 1rem; font-size:1rem;
    }
    .lista {
      max-height: 200px; overflow-y: auto;
      border:1px solid #ddd; border-radius:4px; padding:0;
    }
    .docente-item {
      display:flex; justify-content:space-between; align-items:center;
      padding:8px; cursor:pointer; border-bottom:1px solid #eee;
    }
    .docente-item:last-child { border-bottom:none; }
    .docente-item:hover { background:#f9f9f9; }
    .docente-item.selected { background:#e0f7fa; }
    .badge {
      padding:4px 12px; border:none; border-radius:12px;
      font-size: .9rem; cursor:pointer;
    }
    .badge.selectable { background:#007bff; color:#fff; }
    .badge.selected   { background:#4caf50; color:#fff; }
    .btn-save {
      margin-top:1rem; padding:8px 16px; font-size:1rem;
      background:#007bff; color:#fff; border:none; border-radius:4px;
      cursor:pointer; opacity:.5;
    }
    .btn-save.enabled { opacity:1; }
  </style>
<?php endif; ?>
