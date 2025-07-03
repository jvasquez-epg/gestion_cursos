<?php
// admin/views/usuarios_form.php
// Variables esperadas en este view:
//   $usuario         (array|null)  → datos del usuario al editar
//   $rol             (string)      → 'administrador'|'administrativo'|'estudiante'
//   $errores         (array)       → lista de strings con errores de validación
//   $permisos        (array)       → lista de todos los permisos ['id'=>..,'nombre'=>..]
//   $usuarioPermisos (array)       → lista de IDs de permisos asignados a este usuario
//   $BASE_URL        (string)

$isEdit = isset($usuario) && isset($usuario['id']);
$rolCapital = ucfirst($rol);
$val = function ($key, $def = '') use ($usuario) {
    return $usuario[$key] ?? $def;
};

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? "Editar $rolCapital" : "Crear $rolCapital" ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <style>
        .form-box {
            background: #fff;
            max-width: 540px;
            margin: 2.5rem auto;
            border-radius: 12px;
            padding: 2.5rem 2.5rem 1.5rem 2.5rem;
            box-shadow: 0 3px 16px #2222;
        }

        .form-box h2 {
            margin-bottom: 1.4rem;
            color: #2c3659;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1.06rem;
            background: #f7fafd;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1973ad;
            box-shadow: 0 0 0 3px rgba(25, 115, 173, 0.1);
        }

        .form-group input.error {
            border-color: #dc3545;
            background: #fef7f7;
        }

        .form-actions {
            margin-top: 1.8rem;
            display: flex;
            gap: 1.4rem;
        }

        .btn {
            padding: .7em 1.8em;
            font-size: 1.02rem;
            border-radius: 4px;
            border: none;
            background: #1973ad;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-cancel {
            background: #888;
        }

        .btn:hover:not(:disabled) {
            background: #114c75;
            transform: translateY(-1px);
        }

        .btn-cancel:hover:not(:disabled) {
            background: #666;
        }

        .form-errors {
            background: #ffeaea;
            color: #d63384;
            border: 1px solid #f5c2c7;
            padding: 1em 1.3em;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .form-errors ul {
            margin: 0;
            padding-left: 1.2em;
        }

        .field-error {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 4px;
            display: block;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading .spinner {
            display: inline-block;
        }

        .usuario-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            padding: 0.8em;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #1565c0;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../components/header_main.php'; ?>
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <?php include __DIR__ . '/../../components/header_user.php'; ?>

    <main class="dashboard-main">
        <div class="form-box">
            <h2><?= $isEdit ? "Editar $rolCapital" : "Crear $rolCapital" ?></h2>

            <?php if (!empty($errores)): ?>
                <div class="form-errors">
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul>
                        <?php foreach ($errores as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (in_array($rol, ['administrador', 'administrativo'])): ?>
                <div class="usuario-info">
                    <strong>Nota:</strong> Para <?= $rolCapital ?>s, el nombre de usuario será automáticamente el DNI.
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off" id="userForm" novalidate
                action="<?= BASE_URL ?>admin/usuarios.php?action=<?= $isEdit ? 'update' : 'store' ?><?= $isEdit ? '&id=' . $usuario['id'] : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="rol" value="<?= htmlspecialchars($rol) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombres">Nombres <span style="color: red;">*</span></label>
                    <input type="text" id="nombres" name="nombres" required
                        value="<?= htmlspecialchars($val('nombres')) ?>" maxlength="100" pattern="[A-Za-zÀ-ÿ\s]+"
                        title="Solo se permiten letras y espacios">
                    <span class="field-error" id="nombres-error"></span>
                </div>

                <div class="form-group">
                    <label for="apellido_paterno">Apellido paterno <span style="color: red;">*</span></label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" required
                        value="<?= htmlspecialchars($val('apellido_paterno')) ?>" maxlength="50"
                        pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios">
                    <span class="field-error" id="apellido_paterno-error"></span>
                </div>

                <div class="form-group">
                    <label for="apellido_materno">Apellido materno <span style="color: red;">*</span></label>
                    <input type="text" id="apellido_materno" name="apellido_materno" required
                        value="<?= htmlspecialchars($val('apellido_materno')) ?>" maxlength="50"
                        pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios">
                    <span class="field-error" id="apellido_materno-error"></span>
                </div>

                <div class="form-group">
                    <label for="dni">DNI <span style="color: red;">*</span></label>
                    <input type="text" id="dni" name="dni" required value="<?= htmlspecialchars($val('dni')) ?>"
                        maxlength="8" pattern="\d{8}" title="Debe tener exactamente 8 dígitos" inputmode="numeric">
                    <span class="field-error" id="dni-error"></span>
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico <span style="color: red;">*</span></label>
                    <input type="email" id="correo" name="correo" required
                        value="<?= htmlspecialchars($val('correo')) ?>" maxlength="100">
                    <span class="field-error" id="correo-error"></span>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($val('telefono')) ?>"
                        maxlength="9" pattern="\d{9}" title="Debe tener exactamente 9 dígitos" inputmode="numeric">
                    <span class="field-error" id="telefono-error"></span>
                </div>

                <?php if ($rol === 'estudiante'): ?>
                    <div class="form-group">
                        <label for="usuario">Nombre de usuario <span style="color: red;">*</span></label>
                        <input type="text" id="usuario" name="usuario" required
                            value="<?= htmlspecialchars($val('usuario')) ?>" maxlength="50" pattern="[a-zA-Z0-9_-]+"
                            title="Solo letras, números, guiones y guiones bajos">
                        <span class="field-error" id="usuario-error"></span>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="contraseña">
                        <?= $isEdit ? 'Nueva contraseña' : 'Contraseña' ?>
                        <?= $isEdit ? '' : '<span style="color: red;">*</span>' ?>
                    </label>
                    <input type="password" id="contraseña" name="contraseña" <?= $isEdit ? '' : 'required' ?>
                        minlength="6" maxlength="255">
                    <?php if ($isEdit): ?>
                        <small style="color: #666;">Deja en blanco si no deseas cambiar la contraseña</small>
                    <?php endif; ?>
                    <span class="field-error" id="contraseña-error"></span>
                </div>

                <!-- ——— SECCIÓN: Permisos ——— -->
                <div class="form-group">
                    <label>Permisos</label>
                    <?php if (empty($permisos)): ?>
                        <p>No hay permisos definidos en el sistema.</p>
                    <?php else: ?>
                        <div class="permiso-list">
                            <?php foreach ($permisos as $perm): ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="permisos[]" value="<?= $perm['id'] ?>"
                                            <?= in_array($perm['id'], $usuarioPermisos ?? []) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($perm['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit" id="submitBtn">
                        <span class="spinner"></span>
                        <?= $isEdit ? 'Guardar Cambios' : 'Crear Usuario' ?>
                    </button>
                    <a class="btn btn-cancel"
                        href="<?= BASE_URL ?>admin/usuarios.php?action=list&rol=<?= urlencode($rol) ?>">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('userForm');
            const submitBtn = document.getElementById('submitBtn');
            const inputs = form.querySelectorAll('input[required]');

            // Validación en tiempo real
            inputs.forEach(input => {
                input.addEventListener('blur', function () {
                    validateField(this);
                });

                input.addEventListener('input', function () {
                    clearFieldError(this);
                });
            });

            // Validación del formulario
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                let isValid = true;

                // Limpiar errores anteriores
                clearAllErrors();

                // Validar todos los campos
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });

                // Validaciones específicas
                if (!validateDNI()) isValid = false;
                if (!validateEmail()) isValid = false;
                if (!validatePhone()) isValid = false;
                if (!validatePassword()) isValid = false;

                if (isValid) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    // Enviar formulario
                    this.submit();
                }
            });

            function validateField(field) {
                const value = field.value.trim();
                const fieldName = field.name;
                let isValid = true;

                if (field.required && !value) {
                    showFieldError(field, 'Este campo es obligatorio');
                    isValid = false;
                } else if (value && field.pattern && !new RegExp(field.pattern).test(value)) {
                    showFieldError(field, field.title || 'Formato no válido');
                    isValid = false;
                }

                return isValid;
            }

            function validateDNI() {
                const dniInput = document.getElementById('dni');
                const dni = dniInput.value.trim();

                if (!/^\d{8}$/.test(dni)) {
                    showFieldError(dniInput, 'El DNI debe tener exactamente 8 dígitos');
                    return false;
                }

                return true;
            }

            function validateEmail() {
                const emailInput = document.getElementById('correo');
                const email = emailInput.value.trim();

                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showFieldError(emailInput, 'El formato del correo no es válido');
                    return false;
                }

                return true;
            }

            function validatePhone() {
                const phoneInput = document.getElementById('telefono');
                const phone = phoneInput.value.trim();

                if (phone && !/^\d{9}$/.test(phone)) {
                    showFieldError(phoneInput, 'El teléfono debe tener exactamente 9 dígitos');
                    return false;
                }

                return true;
            }

            function validatePassword() {
                const passwordInput = document.getElementById('contraseña');
                const password = passwordInput.value;
                const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

                if (!isEdit && !password) {
                    showFieldError(passwordInput, 'La contraseña es obligatoria');
                    return false;
                }

                if (password && password.length < 6) {
                    showFieldError(passwordInput, 'La contraseña debe tener al menos 6 caracteres');
                    return false;
                }

                return true;
            }

            function showFieldError(field, message) {
                field.classList.add('error');
                const errorElement = document.getElementById(field.name + '-error');
                if (errorElement) {
                    errorElement.textContent = message;
                }
            }

            function clearFieldError(field) {
                field.classList.remove('error');
                const errorElement = document.getElementById(field.name + '-error');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            function clearAllErrors() {
                const errorElements = document.querySelectorAll('.field-error');
                errorElements.forEach(element => {
                    element.textContent = '';
                });

                const inputElements = document.querySelectorAll('input.error');
                inputElements.forEach(element => {
                    element.classList.remove('error');
                });
            }

            // Solo números para DNI y teléfono
            ['dni', 'telefono'].forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.addEventListener('input', function (e) {
                        this.value = this.value.replace(/\D/g, '');
                    });
                }
            });

            // Solo letras para nombres y apellidos
            ['nombres', 'apellido_paterno', 'apellido_materno'].forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.addEventListener('input', function (e) {
                        this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '');
                    });
                }
            });
        });
    </script>
    <script src="<?= BASE_URL ?>assets/js/mobile-menu.js"></script>
</body>

</html>