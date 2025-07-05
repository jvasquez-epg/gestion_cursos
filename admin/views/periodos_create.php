<?php

/*
 * Vista para creación y edición de periodos académicos.
 * Variables esperadas:
 *   $periodData  — Datos del periodo (array vacío si es nuevo)
 *   $formAction  — URL parcial para submit del formulario
 *   $buttonLabel — Etiqueta para el botón de acción ("Crear" o "Actualizar")
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


 
// admin/views/periodos_create.php
// Variables esperadas:
//   $periodData  (array)   — datos del periodo a editar (vacío en creación)
//   $formAction  (string)  — parte final de la URL (e.g. "?action=edit&id=3")
//   $buttonLabel (string)  — "Crear" o "Actualizar"

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isEdit       = isset($periodData['id']);
$anio         = (int) ($periodData['anio']  ?? date('Y'));
$periodo      = (int) ($periodData['periodo'] ?? 1);
$iniEnv       = $periodData['inicio_envio_solicitudes']     ?? '';
$finEnv       = $periodData['fin_envio_solicitudes']       ?? '';
$iniApt       = $periodData['inicio_asignacion_docentes']   ?? '';
$finApt       = $periodData['fin_asignacion_docentes']      ?? '';
$maxC         = (int) ($periodData['maximo_cursos']         ?? 1);
$maxCr        = (int) ($periodData['maximo_creditos']       ?? 1);
$minS         = (int) ($periodData['minimo_solicitudes']    ?? 1);
$inicioYaPaso = $iniEnv && strtotime($iniEnv) <= time();

// Flash messages
$error   = $_SESSION['error']   ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isEdit ? 'Editar Periodo' : 'Nuevo Periodo' ?></title>

  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- Estilos del sistema -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/periodos_create.css">

  <script>
    const IS_EDIT      = <?= $isEdit ? 'true' : 'false' ?>;
    const START_PASSED = <?= $inicioYaPaso ? 'true' : 'false' ?>;
    const DEFAULTS = {
      iniEnv: <?= $iniEnv ? json_encode(substr($iniEnv,0,16)) : 'null' ?>,
      finEnv: <?= $finEnv ? json_encode(substr($finEnv,0,16)) : 'null' ?>,
      iniApt: <?= $iniApt ? json_encode(substr($iniApt,0,16)) : 'null' ?>,
      finApt: <?= $finApt ? json_encode(substr($finApt,0,16)) : 'null' ?>
    };
  </script>
</head>
<body>
  <?php include __DIR__ . '/../../components/header_main.php'; ?>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>
  <?php include __DIR__ . '/../../components/header_user.php'; ?>

  <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <div class="hamburger"><span></span><span></span><span></span></div>
  </button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <main class="dashboard-main">
    <div class="glass-card">
      <h2 class="form-title"><?= htmlspecialchars($buttonLabel) ?> Periodo</h2>

      <form id="periodoForm"
            method="post"
            action="<?= BASE_URL ?>admin/periodos.php<?= htmlspecialchars($formAction) ?>"
            novalidate>
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= (int)$periodData['id'] ?>">
        <?php endif; ?>

        <!-- Año & Periodo -->
        <div class="row">
          <?php if (!$isEdit): ?>
            <div class="col input-group">
              <label for="anio">Año</label>
              <select name="anio" id="anio" required>
                <option value="">Selecciona un año</option>
                <?php for ($y = 2025; $y <= date('Y')+2; $y++): ?>
                  <option value="<?= $y ?>" <?= $y === $anio ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col input-group">
              <label for="periodo">Periodo</label>
              <select name="periodo" id="periodo" required>
                <option value="">Selecciona un periodo</option>
                <option value="1" <?= $periodo===1 ? 'selected' : '' ?>>1 (Nivelación)</option>
                <option value="2" <?= $periodo===2 ? 'selected' : '' ?>>2 (Nivelación)</option>
                <option value="3" <?= $periodo===3 ? 'selected' : '' ?>>3 (Vacacional)</option>
              </select>
            </div>
          <?php else: ?>
            <div class="col card-light">
              <h3>Año</h3>
              <div class="pill"><?= $anio ?></div>
              <input type="hidden" name="anio" value="<?= $anio ?>">
            </div>
            <div class="col card-light">
              <h3>Periodo</h3>
              <div class="pill"><?= $periodo ?></div>
              <input type="hidden" name="periodo" value="<?= $periodo ?>">
            </div>
          <?php endif; ?>
        </div>

        <!-- Cursos / Créditos / Solicitudes -->
        <div class="row">
          <div class="col input-group">
            <label for="maximo_cursos">Cursos Máximos</label>
            <input type="number" name="maximo_cursos" id="maximo_cursos" min="1" required
                   value="<?= $maxC ?>">
          </div>
          <div class="col input-group">
            <label for="maximo_creditos">Créditos Máximos</label>
            <input type="number" name="maximo_creditos" id="maximo_creditos" min="1" required
                   value="<?= $maxCr ?>">
          </div>
          <div class="col input-group">
            <label for="minimo_solicitudes">Solicitudes Mínimas</label>
            <input type="number" name="minimo_solicitudes" id="minimo_solicitudes" min="1" required
                   value="<?= $minS ?>">
          </div>
        </div>

        <!-- Envío de Solicitudes -->
        <div class="dates-section">
          <label>Envío de Solicitudes</label>
          <div class="date-row">
            <div class="input-group clock-wrapper">
              <i class="fa fa-clock-o"></i>
              <input
                id="inicio_envio_solicitudes"
                name="inicio_envio_solicitudes"
                class="flatpickr-datetime"
                placeholder="Selecciona inicio"
                value="<?= $iniEnv ? htmlspecialchars(substr($iniEnv,0,16)) : '' ?>"
                <?= $isEdit && $inicioYaPaso ? 'readonly' : '' ?>>
            </div>
            <span>–</span>
            <div class="input-group clock-wrapper">
              <i class="fa fa-clock-o"></i>
              <input
                id="fin_envio_solicitudes"
                name="fin_envio_solicitudes"
                class="flatpickr-datetime"
                placeholder="Selecciona fin"
                value="<?= $finEnv ? htmlspecialchars(substr($finEnv,0,16)) : '' ?>">
            </div>
          </div>
        </div>

        <!-- Apertura de Cursos -->
        <div class="dates-section">
          <label>Apertura de Cursos</label>
          <div class="date-row">
            <div class="input-group clock-wrapper">
              <i class="fa fa-clock-o"></i>
              <input
                id="inicio_asignacion_docentes"
                name="inicio_asignacion_docentes"
                class="flatpickr-datetime"
                placeholder="Selecciona inicio"
                value="<?= $iniApt ? htmlspecialchars(substr($iniApt,0,16)) : '' ?>">
            </div>
            <span>–</span>
            <div class="input-group clock-wrapper">
              <i class="fa fa-clock-o"></i>
              <input
                id="fin_asignacion_docentes"
                name="fin_asignacion_docentes"
                class="flatpickr-datetime"
                placeholder="Selecciona fin"
                value="<?= $finApt ? htmlspecialchars(substr($finApt,0,16)) : '' ?>">
            </div>
          </div>
        </div>

        <div class="buttons">
          <a href="<?= BASE_URL ?>admin/periodos.php" class="btn-cancel">Cancelar</a>
          <button type="submit" class="btn-submit"><?= htmlspecialchars($buttonLabel) ?></button>
        </div>
      </form>
    </div>
  </main>

  <div id="modal-alert" class="modal-alert"></div>

  <!-- Scripts -->
  <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    (() => {
      // Mostrar flash con SweetAlert2
      document.addEventListener('DOMContentLoaded', () => {
        <?php if ($error): ?>
          Swal.fire('Error', '<?= htmlspecialchars($error) ?>', 'error');
        <?php elseif ($success): ?>
          Swal.fire('Éxito', '<?= htmlspecialchars($success) ?>', 'success');
        <?php endif; ?>
      });

      function pad(n){ return n.toString().padStart(2,'0'); }
      function toLocalDt(d){
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
      }

      const now       = new Date();
      const nowStr    = toLocalDt(now).replace(' ', 'T');
      const oneMin    = new Date(now.getTime() + 60000);
      const oneMinStr = toLocalDt(oneMin).replace(' ', 'T');

      // Márgenes al crear:
      // finEnv = inicio_envio + 1 día
      // iniApt = finEnv + 1 hora
      // finApt = iniApt + 1 día
      let createFinEnvStr, createIniAptStr, createFinAptStr;
      if (!IS_EDIT) {
        const finEnvDate = new Date(oneMin.getTime() + 24*60*60*1000);
        const iniAptDate = new Date(finEnvDate.getTime() + 60*60*1000);
        const finAptDate = new Date(iniAptDate.getTime() + 24*60*60*1000);
        createFinEnvStr  = toLocalDt(finEnvDate).replace(' ', 'T');
        createIniAptStr  = toLocalDt(iniAptDate).replace(' ', 'T');
        createFinAptStr  = toLocalDt(finAptDate).replace(' ', 'T');
      }

      const CFG = {
        enableTime: true,
        time_24hr: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        locale: 'es',
        minuteIncrement: 5
      };

      // Inicio envío
      const fIni = flatpickr('#inicio_envio_solicitudes', {
        ...CFG,
        defaultDate: IS_EDIT ? DEFAULTS.iniEnv?.replace(' ','T') : oneMinStr,
        clickOpens:  IS_EDIT ? !START_PASSED : true,
        minDate:     IS_EDIT ? null : oneMinStr
      });

      // Fin envío
      const fFin = flatpickr('#fin_envio_solicitudes', {
        ...CFG,
        defaultDate: IS_EDIT ? DEFAULTS.finEnv?.replace(' ','T') : createFinEnvStr,
        minDate:     IS_EDIT ? nowStr : oneMinStr
      });

      // Inicio asignación
      const aIni = flatpickr('#inicio_asignacion_docentes', {
        ...CFG,
        defaultDate: IS_EDIT ? DEFAULTS.iniApt?.replace(' ','T') : createIniAptStr,
        minDate:     IS_EDIT ? nowStr : oneMinStr
      });

      // Fin asignación
      const aFin = flatpickr('#fin_asignacion_docentes', {
        ...CFG,
        defaultDate: IS_EDIT ? DEFAULTS.finApt?.replace(' ','T') : createFinAptStr,
        minDate:     IS_EDIT ? nowStr : oneMinStr
      });

      // Sincronizar si el usuario cambia manualmente
      function syncIntervals(){
        const eIni = fIni.selectedDates[0];
        if(eIni){
          const minFE = new Date(eIni.getTime()+60000);
          fFin.set('minDate', toLocalDt(minFE).replace(' ','T'));
          if(fFin.selectedDates[0]<minFE) fFin.setDate(minFE);
        }
        const eFin = fFin.selectedDates[0];
        if(eFin){
          const minIA = new Date(eFin.getTime()+60000);
          aIni.set('minDate', toLocalDt(minIA).replace(' ','T'));
          if(aIni.selectedDates[0]<minIA) aIni.setDate(minIA);
        }
        const sIni = aIni.selectedDates[0];
        if(sIni){
          const minFA = new Date(sIni.getTime()+60000);
          aFin.set('minDate', toLocalDt(minFA).replace(' ','T'));
          if(aFin.selectedDates[0]<minFA) aFin.setDate(minFA);
        }
      }
      [fIni,fFin,aIni].forEach(fp => fp.config.onChange.push(syncIntervals));

      // Confirmación de reasignaciones
      const origSendEnd  = DEFAULTS.finEnv  ? new Date(DEFAULTS.finEnv.replace(' ','T')) : null;
      const origAptStart = DEFAULTS.iniApt  ? new Date(DEFAULTS.iniApt.replace(' ','T')) : null;
      const form         = document.getElementById('periodoForm');

      form.addEventListener('submit', e=>{
        const now = new Date();
        const ie  = fIni.selectedDates[0];
        const fe  = fFin.selectedDates[0];
        const ia  = aIni.selectedDates[0];
        const fa  = aFin.selectedDates[0];

        // 1) Numéricos
        let numErr = '';
        ['anio','periodo','maximo_cursos','maximo_creditos','minimo_solicitudes']
          .forEach(f=>{
            const v = form[f].value;
            if(!v||isNaN(v)||Number(v)<=0) numErr = 'Completa todos los campos numéricos con valores válidos.';
          });
        if(numErr){ e.preventDefault(); Swal.fire('Error',numErr,'error'); return; }

        // 2) Orden fechas
        if(!ie||!fe||fe<=ie){
          e.preventDefault(); Swal.fire('Error','Verifica que el fin de envío sea posterior al inicio.','error'); return;
        }
        if(!ia||!fa||fa<=ia){
          e.preventDefault(); Swal.fire('Error','Verifica que el fin de asignación sea posterior al inicio.','error'); return;
        }

        // 3) No reducir antes de ahora
        if((fe&&fe<now)||(fa&&fa<now)){
          e.preventDefault(); Swal.fire('Error','No puedes poner la fecha final antes del momento actual.','error'); return;
        }

        // 4) Confirmar extensión tras inicio de asignación
        if(
          IS_EDIT &&
          origAptStart && now>origAptStart &&
          fe && origSendEnd && fe.getTime()>origSendEnd.getTime()
        ){
          e.preventDefault();
          Swal.fire({
            icon:'warning',
            title:'Reasignación de docentes',
            text:'Al ampliar el fin de envío se eliminarán todas las asignaciones actuales de docentes. ¿Continuar?',
            showCancelButton:true,
            confirmButtonText:'Sí, continuar',
            cancelButtonText:'No, cancelar'
          }).then(res=>{
            if(res.isConfirmed){
              const inp = document.createElement('input');
              inp.type='hidden'; inp.name='eliminar_asignaciones'; inp.value='1';
              form.appendChild(inp);
              form.submit();
            } else {
              fFin.setDate(origSendEnd);
            }
          });
          return;
        }
        // Envía normalmente
      });
    })();
  </script>
</body>
</html>
