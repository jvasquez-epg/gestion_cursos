<?php
/*
 * admin/views/solicitudes_detalle.php
 * Vista parcial para mostrar el detalle de solicitudes por curso.
 * Variables esperadas:
 *   $detalles — Listado de estudiantes con sus datos (array), generado por SolicitudModel::getDetallePorCurso()
 * Muestra: nombre completo, DNI, usuario y fecha/hora de solicitud.
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */


if (empty($detalles)): ?>
  <p>No hay solicitudes.</p>
<?php else: ?>
  <table class="inner-table" id="inner-detalle">
    <thead>
      <tr>
        <th>Alumno</th>
        <th>DNI</th>
        <th>Usuario</th>
        <th>Fecha y hora</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($detalles as $d): ?>
      <tr>
        <td><?= htmlspecialchars(trim("{$d['nombres']} {$d['apellido_paterno']} {$d['apellido_materno']}")) ?></td>
        <td><?= htmlspecialchars($d['dni'] ?? '-') ?></td>
        <td><?= htmlspecialchars($d['usuario']) ?></td>
        <td><?= (new DateTime($d['fecha_solicitud']))->format('d/m/Y H:i') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
