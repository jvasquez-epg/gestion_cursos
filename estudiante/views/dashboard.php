<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Académico - Sistema de Gestión</title>
    
    <!-- Estilos principales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/palette.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    
    <!-- Estilos personalizados del dashboard -->
    <style>
        <?= file_get_contents(__DIR__ . '/../../assets/css/dashboard_custom.css') ?>
        
        /* Estilos adicionales para mejorar la presentación */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .dashboard-header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }
        
        .period-info {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .status-envio { background: #e3f2fd; color: #1565c0; }
        .status-asignacion { background: #fff3e0; color: #ef6c00; }
        .status-finalizado { background: #e8f5e8; color: #2e7d32; }
        .status-pendiente { background: #fff8e1; color: #f57f17; }
        .status-asignado { background: #e8f5e8; color: #2e7d32; }
        .status-denegado { background: #ffebee; color: #c62828; }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card h3 {
            margin: 0 0 1rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .dashboard-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-card .description {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .countdown {
            font-size: 1.8rem;
            font-weight: 600;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }
        
        .stats-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: #f8fafc;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .course-code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #667eea;
        }
        
        .course-name {
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            font-size: 1.1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .alert {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: #f0f9ff;
            border-color: #0ea5e9;
            color: #0c4a6e;
        }
        
        .alert-warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-success {
            background: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }
        
        .alert h3 {
            margin: 0 0 1rem 0;
            font-size: 1.25rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Componentes del layout principal -->
    <?php include __DIR__ . '/../../components/header_main.php'; ?>
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <?php include __DIR__ . '/../../components/header_user.php'; ?>

    <!-- Contenido principal del dashboard -->
    <div class="container dashboard-main">
        <!-- Encabezado del dashboard -->
        <div class="dashboard-header">
            <h1>Dashboard Académico</h1>
            <div class="subtitle">Sistema de Gestión de Solicitudes de Cursos</div>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <?php if (!empty($error)): ?>
                <!-- Mensaje de error -->
                <div class="alert alert-warning">
                    <h3>⚠️ Información del Sistema</h3>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php else: ?>
                <!-- Información del período activo -->
                <div class="period-info">
                    <h3>📅 Período Académico <?= htmlspecialchars($periodo['anio']) ?>-<?= htmlspecialchars($periodo['periodo']) ?></h3>
                    <?php
                    $fase_labels = [
                        'envio' => 'Envío de Solicitudes', 
                        'asignacion' => 'Asignación de Cursos', 
                        'finalizado' => 'Período Finalizado'
                    ];
                    $fase_class = [
                        'envio' => 'status-envio', 
                        'asignacion' => 'status-asignacion', 
                        'finalizado' => 'status-finalizado'
                    ];
                    ?>
                    <span class="status-badge <?= $fase_class[$fase] ?? 'status-pendiente' ?>">
                        <?= $fase_labels[$fase] ?? 'Estado Desconocido' ?>
                    </span>
                </div>

                <?php if ($fase === 'envio'): ?>
                    <!-- Vista para fase de envío de solicitudes -->
                    <div class="cards-grid">
                        <div class="dashboard-card">
                            <h3>⏰ Tiempo Restante</h3>
                            <div class="countdown" id="countdown">Calculando...</div>
                            <div class="description">Para enviar solicitudes</div>
                        </div>

                        <div class="dashboard-card">
                            <h3>📋 Solicitudes Realizadas</h3>
                            <div class="value"><?= (int)($stats['count'] ?? 0) ?></div>
                            <div class="description">de <?= (int)($params['maximo_cursos'] ?? 0) ?> máximo permitido</div>
                        </div>

                        <div class="dashboard-card">
                            <h3>🎓 Créditos Solicitados</h3>
                            <div class="value"><?= (int)($stats['creditos'] ?? 0) ?></div>
                            <div class="description">de <?= (int)($params['maximo_creditos'] ?? 0) ?> máximo permitido</div>
                        </div>
                    </div>

                    <!-- Tabla de cursos solicitados (si existen) -->
                    <?php if (!empty($detalleCursos)): ?>
                        <div class="table-container">
                            <h3 style="padding: 1.5rem 1.5rem 0;">📚 Cursos Solicitados</h3>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del Curso</th>
                                        <th>Créditos</th>
                                        <th>Prioridad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalleCursos as $curso): ?>
                                        <tr>
                                            <td class="course-code"><?= htmlspecialchars($curso['codigo'] ?? '') ?></td>
                                            <td class="course-name"><?= htmlspecialchars($curso['nombre'] ?? '') ?></td>
                                            <td><?= (int)($curso['creditos'] ?? 0) ?></td>
                                            <td><?= (int)($curso['prioridad'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <?php elseif ($fase === 'asignacion'): ?>
                    <!-- Vista para fase de asignación -->
                    <?php
                    $total_solicitudes = (int)($stats['count'] ?? 0);
                    $minimo = (int)($params['minimo_solicitudes'] ?? 1);
                    $asignados = count(array_filter($asigs ?? [], fn($a) => ($a['estado'] ?? '') === 'asignado'));
                    $pendientes = count(array_filter($asigs ?? [], fn($a) => ($a['estado'] ?? '') === 'pendiente'));
                    $denegados = count(array_filter($asigs ?? [], fn($a) => ($a['estado'] ?? '') === 'denegado'));
                    
                    $estado_general = $total_solicitudes == 0 ? 'Sin solicitudes registradas' :
                        ($total_solicitudes < $minimo ? 'Solicitudes insuficientes' :
                            ($pendientes > 0 ? 'Asignación en proceso' : 'Proceso de asignación completado'));
                    ?>

                    <div class="stats-summary">
                        <h3>📊 Resumen de Asignaciones</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?= $asignados ?></div>
                                <div class="stat-label">✅ Asignados</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $pendientes ?></div>
                                <div class="stat-label">⏳ Pendientes</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $denegados ?></div>
                                <div class="stat-label">❌ Denegados</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $total_solicitudes ?></div>
                                <div class="stat-label">📋 Total Solicitudes</div>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 1rem;">
                            <span class="status-badge status-asignacion"><?= $estado_general ?></span>
                        </div>
                    </div>

                    <!-- Tabla de asignaciones -->
                    <?php if (!empty($asigs)): ?>
                        <div class="table-container">
                            <h3 style="padding: 1.5rem 1.5rem 0;">🎯 Estado de Asignaciones</h3>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del Curso</th>
                                        <th>Docente Asignado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asigs as $asignacion): ?>
                                        <tr>
                                            <td class="course-code"><?= htmlspecialchars($asignacion['codigo'] ?? '') ?></td>
                                            <td class="course-name"><?= htmlspecialchars($asignacion['nombre'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asignacion['docente'] ?? 'Sin asignar') ?></td>
                                            <td>
                                                <?php
                                                $estado_mostrar = $total_solicitudes < $minimo ? 'denegado' : ($asignacion['estado'] ?? 'pendiente');
                                                $texto_estado = $total_solicitudes < $minimo ? 'Denegado' : ucfirst($asignacion['estado'] ?? 'Pendiente');
                                                ?>
                                                <span class="status-badge status-<?= $estado_mostrar ?>">
                                                    <?= $texto_estado ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            📋 No hay asignaciones disponibles para mostrar.
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Vista para período finalizado -->
                    <?php if (!empty($mostrarResumenFinal) && !empty($resumenFinal)): ?>
                        <div class="alert alert-success">
                            <h3>✅ Resumen Final de Asignaciones</h3>
                            <p>Consulta el resultado final de todas las asignaciones del período.</p>
                            
                            <div class="table-container" style="margin-top: 1rem;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre del Curso</th>
                                            <th>Docente Asignado</th>
                                            <th>Solicitudes Recibidas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resumenFinal as $curso): ?>
                                            <tr>
                                                <td class="course-code"><?= htmlspecialchars($curso['codigo'] ?? '') ?></td>
                                                <td class="course-name"><?= htmlspecialchars($curso['nombre'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($curso['docente'] ?? 'Sin asignar') ?></td>
                                                <td><?= (int)($curso['total_solicitudes'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <h3>🏁 Período Finalizado</h3>
                            <p>El período académico actual ha finalizado. El próximo período comenzará pronto.</p>
                            
                            <?php if (!empty($params['proximo_inicio'])): ?>
                                <div style="margin-top: 1rem;">
                                    <strong>⏰ Próximo período inicia en:</strong>
                                    <div class="countdown" id="countdown" style="margin-top: 0.5rem;">Calculando...</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Script para countdown -->
    <?php if (in_array($fase, ['envio', 'finalizado']) && !empty($params)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const countdownElement = document.getElementById('countdown');
                if (!countdownElement) return;

                <?php if ($fase === 'envio' && !empty($params['fin_envio_solicitudes'])): ?>
                    const targetDate = "<?= $params['fin_envio_solicitudes'] ?>".replace(' ', 'T');
                <?php elseif ($fase === 'finalizado' && !empty($params['proximo_inicio'])): ?>
                    const targetDate = "<?= $params['proximo_inicio'] ?>".replace(' ', 'T');
                <?php endif; ?>

                if (!targetDate) {
                    countdownElement.textContent = "Fecha no disponible";
                    return;
                }

                function updateCountdown() {
                    try {
                        const now = new Date().getTime();
                        const target = new Date(targetDate).getTime();
                        const difference = target - now;

                        if (difference <= 0) {
                            countdownElement.textContent = "¡Plazo finalizado!";
                            countdownElement.style.color = "#dc2626";
                            return;
                        }

                        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((difference % (1000 * 60)) / 1000);

                        countdownElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    } catch (error) {
                        countdownElement.textContent = "Error en el cálculo";
                        console.error('Error en countdown:', error);
                    }
                }

                // Actualizar inmediatamente y luego cada segundo
                updateCountdown();
                setInterval(updateCountdown, 1000);
            });
        </script>
    <?php endif; ?>
</body>

</html>