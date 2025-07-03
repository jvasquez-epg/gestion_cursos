<?php
// admin/views/solicitudes_detalle.php
// Variables esperadas:
//   $detalles (array) — resultado de SolicitudModel::getDetallePorCurso()

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
