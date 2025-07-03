<?php
ini_set('session.gc_maxlifetime', 0); // 0 = sin límite
session_set_cookie_params(0);
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/conexion.php';

function limpiar(string $v): string
{
  return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

// Función para verificar si el código ya existe en la BD
function verificarCodigoExistente($codigo, $pdo): bool
{
  $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = ? OR dni = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$codigo, $codigo]);
  return $stmt->fetchColumn() > 0;
}

// Paso actual
$step = isset($_POST['step']) ? (int) $_POST['step'] : 1;
$error = '';

// Inicializa la sesión de registro si no existe
if (!isset($_SESSION['registro'])) {
  $_SESSION['registro'] = [];
}

// ==== FLUJO PRINCIPAL DE REGISTRO ====

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Paso 1: Validación de SIGAU y guardado en sesión
  if ($step === 1) {
    $codigo = limpiar($_POST['codigo']);
    $sigPassword = limpiar($_POST['sig_password']);
    $email = limpiar($_POST['correo']);
    $telefono = limpiar($_POST['telefono']);
    $tc = $_POST['tc_accepted'] ?? '';

    // Validaciones rápidas
    if (empty($tc)) {
      $error = 'Debes aceptar los términos y condiciones.';
    } elseif (empty($codigo) || empty($sigPassword) || empty($email) || empty($telefono)) {
      $error = 'Completa todos los campos.';
    } else {
      // ► VERIFICAR SI EL CÓDIGO YA EXISTE EN LA BASE DE DATOS
      try {
        if (verificarCodigoExistente($codigo, $pdo)) {
          $error = 'El estudiante ya está registrado en el sistema. ';
        } else {
          // Ejecuta Python para obtener perfil/malla/progreso
          $pythonExe = 'C:\\xampp\\htdocs\\gestion_cursos\\sigau\\venv\\Scripts\\python.exe';
          $script = 'C:\\xampp\\htdocs\\gestion_cursos\\sigau\\main.py';
          $cmd = sprintf(
            '"%s" "%s" %s %s',
            $pythonExe,
            $script,
            escapeshellarg($codigo),
            escapeshellarg($sigPassword)
          );
          $output = shell_exec($cmd);
          $res = json_decode($output, true);

          if (!$res) {
            $error = 'Error al procesar datos desde SIGAU' ;
          } elseif (isset($res['error'])) {
            $error = $res['error'];
          } else {
            $_SESSION['registro'] = [
              'codigo' => $codigo,
              'correo' => $email,
              'telefono' => $telefono,
              'perfil' => $res['perfil'] ?? [],
              'malla' => $res['malla'] ?? [],
              'progreso' => $res['progreso'] ?? [],
            ];
            $step = 2;
          }
        }
      } catch (Exception $e) {
        $error = 'Error al verificar los datos: ' . $e->getMessage();
      }
    }
  }
  // Paso 2/3/4: Simplemente avanzar o retroceder pasos, guardando cambios si los hubiera
  elseif (in_array($step, [2, 3, 4])) {
    // Permite regresar (en la navegación con botones tipo Regresar)
    if (isset($_POST['back'])) {
      $step = max(1, $step - 1);
    } else {
      $step = min(5, $step + 1);
    }
  }
  // Paso 5: Registro final en base de datos
  elseif ($step === 5) {
    $newPass = limpiar($_POST['new_password']);
    $confirm = limpiar($_POST['confirm_password']);
    $data = $_SESSION['registro'] ?? [];

    // Validaciones
    if ($newPass !== $confirm) {
      $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($newPass) < 8 || !preg_match('/[!@#$%^&*]/', $newPass)) {
      $error = 'Mínimo 8 caracteres y 1 carácter especial.';
    } else {
      // ► VERIFICACIÓN FINAL ANTES DE INSERTAR (por seguridad)
      try {
        if (verificarCodigoExistente($data['codigo'], $pdo)) {
          $error = 'El código de estudiante ya fue registrado por otro usuario. Por favor, contacta al administrador.';
        } else {
          // GUARDAR EN LA BASE DE DATOS
          $pdo->beginTransaction();

          // 1. Insertar en tabla usuarios
          $perfil = $data['perfil'];
          $hash = password_hash($newPass, PASSWORD_BCRYPT);

          $sqlUser = "INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, dni, correo, telefono, usuario, contraseña, rol_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
          $stmt = $pdo->prepare($sqlUser);
          $stmt->execute([
            $perfil['nombres'] ?? '',
            $perfil['apellido_paterno'] ?? '',
            $perfil['apellido_materno'] ?? '',
            $perfil['dni'] ?? $data['codigo'], // Si no hay DNI, usar código
            $data['correo'],
            $data['telefono'],
            $data['codigo'], // Usar código como usuario
            $hash,
            1 // rol_id = 1 para estudiante
          ]);
          $usuario_id = $pdo->lastInsertId();

          // 2. Verificar/crear malla curricular
          $escuela = $perfil['escuela'] ?? 'No definida';
          $hash_malla = md5($escuela . serialize($data['malla']));

          // Buscar si ya existe esta malla
          $sqlCheckMalla = "SELECT id FROM mallas WHERE hash_malla = ?";
          $stmt = $pdo->prepare($sqlCheckMalla);
          $stmt->execute([$hash_malla]);
          $malla_existente = $stmt->fetch();

          if ($malla_existente) {
            $malla_id = $malla_existente['id'];
          } else {
            // Crear nueva malla
            $sqlMalla = "INSERT INTO mallas (escuela, hash_malla) VALUES (?, ?)";
            $stmt = $pdo->prepare($sqlMalla);
            $stmt->execute([$escuela, $hash_malla]);
            $malla_id = $pdo->lastInsertId();

            // Insertar cursos de la malla
            if (!empty($data['malla'])) {
              $sqlCurso = "INSERT INTO cursos (malla_id, ciclo, codigo, nombre, creditos) VALUES (?, ?, ?, ?, ?)";
              $stmt = $pdo->prepare($sqlCurso);
              foreach ($data['malla'] as $curso) {
                $stmt->execute([
                  $malla_id,
                  $curso['ciclo'] ?? 1,
                  $curso['codigo'] ?? '',
                  $curso['nombre'] ?? '',
                  $curso['creditos'] ?? 0
                ]);
              }
            }
          }

          // <---- BLOQUE DE PRERREQUISITOS ---->
          $sqlCursosMap = "SELECT id, codigo, nombre FROM cursos WHERE malla_id = ?";
          $stmtCursosMap = $pdo->prepare($sqlCursosMap);
          $stmtCursosMap->execute([$malla_id]);
          $codigoToId = [];
          $nombreToCodigo = [];
          foreach ($stmtCursosMap->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $codigoToId[$row['codigo']] = $row['id'];
            $nombreToCodigo[strtoupper($row['nombre'])] = $row['codigo'];
          }

          $sqlPrerreq = "INSERT INTO prerrequisitos (curso_id, prerrequisito_id) VALUES (?, ?)";
          $stmtPrerreq = $pdo->prepare($sqlPrerreq);

          foreach ($data['malla'] as $curso) {
            $curso_codigo = $curso['codigo'] ?? '';
            $curso_id = $codigoToId[$curso_codigo] ?? null;
            if ($curso_id && !empty($curso['prerrequisitos'])) {
              foreach ($curso['prerrequisitos'] as $prerreq) {
                $prer_codigo = $prerreq['codigo'] ?? null;
                if (!$prer_codigo && !empty($prerreq['nombre'])) {
                  $prer_codigo = $nombreToCodigo[strtoupper($prerreq['nombre'])] ?? null;
                }
                $prer_id = $codigoToId[$prer_codigo] ?? null;
                if ($prer_id) {
                  $stmtPrerreq->execute([$curso_id, $prer_id]);
                }
              }
            }
          }

          // 3. Insertar en tabla estudiantes
          $firma_hash = md5($data['codigo'] . $escuela . date('Y-m-d'));
          $sqlEstudiante = "INSERT INTO estudiantes (id, codigo_universitario, escuela, firma_hash, malla_id) VALUES (?, ?, ?, ?, ?)";
          $stmt = $pdo->prepare($sqlEstudiante);
          $stmt->execute([
            $usuario_id,
            $data['codigo'],
            $escuela,
            $firma_hash,
            $malla_id
          ]);

          // 4. Insertar progreso académico
          if (!empty($data['progreso'])) {
            $sqlProgreso = "INSERT INTO progreso (estudiante_id, curso_id, estado) VALUES (?, ?, ?)";
            $stmtProgreso = $pdo->prepare($sqlProgreso);

            // Obtener IDs de cursos para el progreso
            $sqlGetCurso = "SELECT id FROM cursos WHERE codigo = ? AND malla_id = ?";
            $stmtGetCurso = $pdo->prepare($sqlGetCurso);

            foreach ($data['progreso'] as $prog) {
              $stmtGetCurso->execute([$prog['codigo'], $malla_id]);
              $curso = $stmtGetCurso->fetch();

              if ($curso) {
                $estado = ($prog['estado'] === 'Cumplido') ? 'Cumplido' : 'Pendiente';
                $stmtProgreso->execute([
                  $usuario_id, // estudiante_id es el mismo que usuario_id
                  $curso['id'],
                  $estado
                ]);
              }
            }
          }

          $pdo->commit();
          session_unset();
          echo '
            <!DOCTYPE html>
            <html lang="es">
            <head>
              <meta charset="UTF-8">
              <title>Registro exitoso</title>
              <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
              <script>
                Swal.fire({
                  icon: "success",
                  title: "¡Registro completado!",
                  html: "<p>Tu cuenta ha sido creada correctamente.</p><p><strong>Usuario:</strong> ' . htmlspecialchars($data['codigo']) . '</p>",
                  confirmButtonText: "Ir a Iniciar sesión",
                  allowOutsideClick: false
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href = "login.php";
                  }
                });
              </script>
            </body>
            </html>';
          exit;


        }
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Error en la base de datos: ' . $e->getMessage();
      }
    }
  }
}

// Restaurar datos de sesión para mostrar en cada paso
$reg = $_SESSION['registro'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sistema de Registro Académico</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="assets/css/signup.css">
</head>

<body>
  <div class="container">
    <!-- Barra de progreso -->
    <div class="progress-wrapper">
      <div class="progress-bar">
        <div class="progress-fill" style="width: <?= ($step / 5) * 100 ?>%"></div>
      </div>
      <div class="steps">
        <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
          <div class="step-number">1</div>
          <div class="step-label">SIGAU</div>
        </div>
        <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
          <div class="step-number">2</div>
          <div class="step-label">Perfil</div>
        </div>
        <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">
          <div class="step-number">3</div>
          <div class="step-label">Malla</div>
        </div>
        <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">
          <div class="step-number">4</div>
          <div class="step-label">Progreso</div>
        </div>
        <div class="step <?= $step >= 5 ? 'active' : '' ?>">
          <div class="step-number">5</div>
          <div class="step-label">Contraseña</div>
        </div>
      </div>
    </div>

    <!-- Contenido del formulario -->
    <div class="form-container">
      <form method="POST" id="signupForm">
        <input type="hidden" name="step" value="<?= $step ?>">

        <?php if ($error): ?>
          <div class="error-message">
            <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
          <div class="form-section">
            <h2 class="section-title">Credenciales SIGAU</h2>
            <p class="section-subtitle">Ingresa tus credenciales del sistema SIGAU para validar tu información académica
            </p>

            <div class="form-group">
              <label class="form-label" for="codigo">Código SIGAU</label>
              <input type="text" id="codigo" name="codigo" class="form-input" required pattern="\w+"
                title="Solo se permiten letras y números" placeholder="Ingresa tu código de estudiante">
            </div>

            <div class="form-group">
              <label class="form-label" for="sig_password">Contraseña SIGAU</label>
              <div class="input-wrapper">
                <input type="password" id="sig_password" name="sig_password" class="form-input" required minlength="6"
                  placeholder="Ingresa tu contraseña SIGAU">
                <button type="button" class="password-toggle" data-target="sig_password">
                  <svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="correo">Correo Electrónico</label>
              <input type="email" id="correo" name="correo" class="form-input" required placeholder="ejemplo@correo.com">
            </div>

            <div class="form-group">
              <label class="form-label" for="telefono">Número de Teléfono</label>
              <input type="tel" id="telefono" name="telefono" class="form-input" pattern="[0-9+\-]+"
                title="Solo números, + y -" placeholder="999 999 999">
            </div>

            <div class="checkbox-group">
              <input type="checkbox" id="tc_check" class="checkbox-input">
              <label class="checkbox-label" for="tc_check">
                Acepto los <a href="#" id="viewTC" class="link">términos y condiciones</a> del sistema
              </label>
            </div>
            <input type="hidden" name="tc_accepted" id="tc_accepted">
          </div>

          <div class="form-actions">
            <div></div>
            <button type="submit" id="btnNext1" class="btn btn-primary" disabled>
              Validar Credenciales
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
          <a class="section-subtitle">Si ya tiene una cuenta vaya al</a><a href="login.php"> Login</a>


        <?php elseif ($step === 2): ?>
          <div class="form-section">
            <h2 class="section-title">Información del Perfil</h2>
            <p class="section-subtitle">Revisa que tu información personal sea correcta</p>

            <div class="info-list">
              <?php foreach ($reg['perfil'] as $k => $v): ?>
                <div class="info-item">
                  <span class="info-label"><?= ucwords(str_replace('_', ' ', $k)) ?></span>
                  <span class="info-value"><?= htmlspecialchars($v) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location='?step=1';">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              Continuar
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

        <?php elseif ($step === 3): ?>
          <div class="form-section">
            <h2 class="section-title">Malla Curricular</h2>
            <p class="section-subtitle">Cursos correspondientes a tu plan de estudios, organizados por ciclo</p>

            <?php
            $mallaPorCiclo = [];
            foreach ($reg['malla'] as $curso) {
              $ciclo = $curso['ciclo'] ?? 1;
              $mallaPorCiclo[$ciclo][] = $curso;
            }
            ksort($mallaPorCiclo);
            ?>

            <?php foreach ($mallaPorCiclo as $ciclo => $cursos): ?>
              <h3 class="ciclo-heading">Ciclo <?= $ciclo ?></h3>
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Nombre del Curso</th>
                    <th>Créditos</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cursos as $c): ?>
                    <tr>
                      <td><?= htmlspecialchars($c['codigo']) ?></td>
                      <td><?= htmlspecialchars($c['nombre']) ?></td>
                      <td><?= $c['creditos'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endforeach; ?>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location='?step=2';">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Regresar
            </button>
            <button type="submit" class="btn btn-primary">
              Continuar
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

        <?php elseif ($step === 4): ?>
          <div class="form-section">
            <h2 class="section-title">Progreso Académico</h2>
            <p class="section-subtitle">Estado actual de tus cursos, organizados por ciclo</p>

            <?php
            $progresoPorCiclo = [];
            foreach ($reg['progreso'] as $curso) {
              $ciclo = $curso['ciclo'] ?? 1;
              $progresoPorCiclo[$ciclo][] = $curso;
            }
            ksort($progresoPorCiclo);
            ?>

            <?php foreach ($progresoPorCiclo as $ciclo => $cursos): ?>
              <h3 class="ciclo-heading">Ciclo <?= $ciclo ?></h3>
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Nombre del Curso</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cursos as $c): ?>
                    <tr>
                      <td><?= htmlspecialchars($c['codigo']) ?></td>
                      <td><?= htmlspecialchars($c['nombre']) ?></td>
                      <td>
                        <?php if ($c['estado'] === 'Cumplido'): ?>
                          <span class="status-badge status-success">Cumplido</span>
                        <?php else: ?>
                          <span class="status-badge status-pending">Pendiente</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endforeach; ?>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location='?step=3';">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              Continuar
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

        <?php elseif ($step === 5): ?>
          <div class="form-section">
            <h2 class="section-title">Crear Contraseña</h2>
            <p class="section-subtitle">Define una contraseña segura para acceder al sistema</p>

            <div class="form-group">
              <label class="form-label" for="new_password">Nueva Contraseña</label>
              <div class="input-wrapper">
                <input type="password" id="new_password" name="new_password" class="form-input" required
                  placeholder="Ingresa una contraseña segura">
                <button type="button" class="password-toggle" data-target="new_password">
                  <svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <div class="form-hint">
                ⚠ Mínimo 8 caracteres y al menos 1 carácter especial
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="confirm_password">Confirmar Contraseña</label>
              <div class="input-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required
                  placeholder="Confirma tu contraseña">
                <button type="button" class="password-toggle" data-target="confirm_password">
                  <svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location='?step=4';">
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              Completar Registro
              <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>


  <!-- Modal de Términos y Condiciones -->
  <div class="modal" id="modalTC">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Términos y Condiciones</h3>
      </div>
      <div class="modal-body">
        <p>Al registrarte en nuestro sistema, aceptas los siguientes términos:</p>
        <ul style="margin: 1rem 0; padding-left: 1.5rem; line-height: 1.8;">
          <li>Mantener la confidencialidad y seguridad de sus credenciales de acceso.</li>
          <li>Autorizar el uso de sus datos académicos exclusivamente para el funcionamiento y mejora del sistema.</li>
          <li>El sistema realiza una extracción automatizada de información académica únicamente para facilitar el
            llenado de solicitudes y procesos internos, sin exponer, compartir ni vulnerar datos sensibles o
            contraseñas.</li>
          <li>Autorizo la generación de una firma digital para la validación y autenticación de mis solicitudes dentro
            del sistema.</li>
        </ul>
        <p>El uso indebido del sistema o el incumplimiento de estos términos puede resultar en la suspensión o
          cancelación de su cuenta.</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="acceptTC" class="btn btn-primary">
          Acepto los Términos
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <script>
    // Toggle de contraseñas
    document.querySelectorAll('.password-toggle').forEach(toggle => {
      toggle.addEventListener('click', function () {
        const targetId = this.dataset.target;
        const input = document.getElementById(targetId);
        const eyeIcon = this.querySelector('.eye-icon');

        if (input.type === 'password') {
          input.type = 'text';
          eyeIcon.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
        `;
        } else {
          input.type = 'password';
          eyeIcon.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        `;
        }
      });
    });

    // Manejo del modal de términos y condiciones
    const tcCheck = document.getElementById('tc_check');
    const btnNext1 = document.getElementById('btnNext1');
    const modalTC = document.getElementById('modalTC');
    const acceptTC = document.getElementById('acceptTC');
    const hiddenTC = document.getElementById('tc_accepted');
    const viewTCLink = document.getElementById('viewTC');

    if (viewTCLink) {
      viewTCLink.addEventListener('click', function (e) {
        e.preventDefault();
        modalTC.classList.add('active');
      });
    }

    if (tcCheck) {
      tcCheck.addEventListener('change', function () {
        if (this.checked) {
          modalTC.classList.add('active');
        } else {
          btnNext1.disabled = true;
          hiddenTC.value = '';
        }
      });
    }

    if (acceptTC) {
      acceptTC.addEventListener('click', function () {
        hiddenTC.value = '1';
        tcCheck.checked = true;
        btnNext1.disabled = false;
        modalTC.classList.remove('active');
      });
    }

    // Cerrar modal al hacer clic fuera
    modalTC.addEventListener('click', function (e) {
      if (e.target === modalTC) {
        modalTC.classList.remove('active');
        if (tcCheck && !hiddenTC.value) {
          tcCheck.checked = false;
        }
      }
    });

    // Validación del formulario
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
      signupForm.addEventListener('submit', function (e) {
        <?php if ($step === 5): ?>
          const newPassword = document.getElementById('new_password').value;
          const confirmPassword = document.getElementById('confirm_password').value;
          const specialCharRegex = /[!@#$%^&*_-]/;

          if (newPassword.length < 6 || !specialCharRegex.test(newPassword)) {
            e.preventDefault();
            showError('La contraseña debe tener mínimo 6 caracteres y al menos un carácter especial.');
            return;
          }

          if (newPassword !== confirmPassword) {
            e.preventDefault();
            showError('Las contraseñas no coinciden.');
            return;
          }
        <?php elseif ($step === 1): ?>
          if (btnNext1.disabled) {
            e.preventDefault();
            showError('Debes aceptar los términos y condiciones para continuar.');
            return;
          }
        <?php endif; ?>
      });
    }

    // Función para mostrar errores
    function showError(message) {
      // Remover errores existentes
      const existingError = document.querySelector('.error-message');
      if (existingError) {
        existingError.remove();
      }

      // Crear nuevo mensaje de error
      const errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.innerHTML = `
      <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      ${message}
    `;

      // Insertar al inicio del formulario
      const formContainer = document.querySelector('.form-container form');
      const firstChild = formContainer.firstElementChild.nextElementSibling;
      formContainer.insertBefore(errorDiv, firstChild);

      // Scroll suave hacia el error
      errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

      // Auto-remove después de 5 segundos
      setTimeout(() => {
        if (errorDiv.parentNode) {
          errorDiv.remove();
        }
      }, 5000);
    }

    // Mejoras de UX
    document.addEventListener('DOMContentLoaded', function () {
      // Auto-focus en el primer campo visible
      const firstInput = document.querySelector('.form-input:not([type="hidden"])');
      if (firstInput) {
        firstInput.focus();
      }

      // Efectos de hover en las filas de tabla
      const tableRows = document.querySelectorAll('.data-table tbody tr');
      tableRows.forEach(row => {
        row.style.transition = 'background-color 0.2s ease';
      });

      // Animación de entrada
      const container = document.querySelector('.container');
      if (container) {
        container.style.opacity = '0';
        container.style.transform = 'translateY(20px)';
        setTimeout(() => {
          container.style.transition = 'all 0.5s ease-out';
          container.style.opacity = '1';
          container.style.transform = 'translateY(0)';
        }, 100);
      }
    });

    // Prevenir envío múltiple del formulario
    let formSubmitted = false;
    if (signupForm) {
      signupForm.addEventListener('submit', function (e) {
        // 1) Si la validación falló, no hacemos nada
        if (e.defaultPrevented) {
          return;
        }
        // 2) Prevenir doble clic / doble envío
        if (formSubmitted) {
          e.preventDefault();
          return false;
        }
        formSubmitted = true;

        // 3) Deshabilitamos el botón y ponemos "Procesando..."
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.setAttribute('data-original-html', submitBtn.innerHTML);
          submitBtn.innerHTML = `
        <svg class="btn-icon animate-spin" fill="none" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
          <path fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            class="opacity-75"></path>
        </svg>
        Procesando...
      `;
        }
      });
    }

  </script>
</body>

</html>