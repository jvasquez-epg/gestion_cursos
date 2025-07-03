<?php
set_time_limit(300);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo   = escapeshellarg($_POST['codigo']);
    $password = escapeshellarg($_POST['password']);

    $pythonExe = 'C:\xampp\htdocs\gestion_cursos\sigau\venv\Scripts\python.exe';
    $script    = 'C:\xampp\htdocs\gestion_cursos\sigau\main.py';
    $command   = "$pythonExe $script $codigo $password";

    // shell_exec() devuelve JSON limpio
    $output = shell_exec($command);

    $resultado = json_decode($output, true);
    if ($resultado === null) {
        echo '<strong>⚠️ Error al decodificar JSON:</strong> ' . json_last_error_msg();
        echo '<pre>' . htmlspecialchars($output) . '</pre>';
        exit;
    }

    if (isset($resultado['error'])) {
        echo '<strong>⚠️ ' . htmlspecialchars($resultado['error']) . '</strong>';
    } else {
        echo '<h3>📄 Perfil del Estudiante</h3><ul>';
        foreach ($resultado['perfil'] as $campo => $valor) {
            $label = ucfirst(str_replace('_', ' ', $campo));
            echo "<li><strong>$label:</strong> " . htmlspecialchars($valor) . '</li>';
        }
        echo '</ul>';
    }

    if (isset($resultado['malla'])) {
        echo '<h3>📚 Malla Curricular</h3><table border="1" cellpadding="3"><tr><th>Ciclo</th><th>Código</th><th>Nombre</th><th>Créditos</th><th>Prerrequisitos</th></tr>';
        foreach ($resultado['malla'] as $curso) {
            $prereqs = [];
            foreach ($curso['prerrequisitos'] as $pre) {
                $prereqs[] = htmlspecialchars($pre['nombre']);
            }
            echo '<tr>';
            echo '<td>' . htmlspecialchars($curso['ciclo']) . '</td>';
            echo '<td>' . htmlspecialchars($curso['codigo']) . '</td>';
            echo '<td>' . htmlspecialchars($curso['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($curso['creditos']) . '</td>';
            echo '<td>' . implode(', ', $prereqs) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    if (isset($resultado['progreso'])) {
        echo '<h3>✅ Progreso</h3><table border="1" cellpadding="3"><tr><th>Código</th><th>Nombre</th><th>Créditos</th><th>Estado</th></tr>';
        foreach ($resultado['progreso'] as $prog) {
            echo '<td>' . htmlspecialchars($prog['codigo']) . '</td>';
            echo '<td>' . htmlspecialchars($prog['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($prog['creditos']) . '</td>';
            $estado = $prog['estado'] === 'Cumplido' ? '<span style="color:green;font-weight:bold;">Cumplido</span>' : '<span style="color:red;font-weight:bold;">Pendiente</span>';
            echo '<td>' . $estado . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

?>

<form method="POST" style="margin-top:1em">
    <input type="text"   name="codigo"   placeholder="Código" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Ver Perfil</button>
</form>