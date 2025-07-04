<?php
// admin/models/SolicitudModel.php

class SolicitudModel
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  /**
   * Resumen: curso + total de solicitudes en el periodo dado
   * Devuelve un array de:
   *  - id                 (int)
   *  - codigo             (string)
   *  - nombre             (string)
   *  - ciclo              (int)
   *  - total_solicitudes  (int)
   */
  public function getResumenPorCurso(int $idPeriodo): array
  {
    $sql = "
            SELECT
              c.id                AS id,
              c.codigo            AS codigo,
              c.nombre            AS nombre,
              c.ciclo             AS ciclo,
              COUNT(s.id)         AS total_solicitudes
            FROM cursos c
            LEFT JOIN solicitudes s
              ON s.curso_id   = c.id
             AND s.periodo_id = :pid
            GROUP BY c.id, c.codigo, c.nombre, c.ciclo
            HAVING COUNT(s.id) > 0
            ORDER BY total_solicitudes DESC
        ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':pid' => $idPeriodo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Detalle: lista de estudiantes que solicitaron un curso en un periodo
   */
  public function getDetallePorCurso(int $idPeriodo, int $idCurso): array
  {
    $sql = "
            SELECT
              s.id               AS id,
              u.usuario          AS usuario,
              u.dni              AS dni,
              u.nombres          AS nombres,
              u.apellido_paterno AS apellido_paterno,
              u.apellido_materno AS apellido_materno,
              s.fecha_solicitud  AS fecha_solicitud
            FROM solicitudes s
            JOIN usuarios u
              ON u.id = s.estudiante_id
            WHERE s.periodo_id = :pid
              AND s.curso_id   = :cid
            ORDER BY s.fecha_solicitud DESC
        ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':pid' => $idPeriodo,
      ':cid' => $idCurso
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function documentosAgrupadosPorCurso(int $periodoId): array
  {
    $sql = "
        SELECT 
            s.estudiante_id,
            u.usuario AS codigo_estudiante,
            c.codigo AS codigo_curso,
            c.nombre AS nombre_curso,
            s.documento
        FROM solicitudes s
        JOIN usuarios u ON s.estudiante_id = u.id
        JOIN cursos c ON s.curso_id = c.id
        WHERE s.periodo_id = ? AND s.documento IS NOT NULL
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$periodoId]);

    $agrupado = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $cursoCodigo = $row['codigo_curso'];
      $agrupado[$cursoCodigo][] = $row;
    }

    return $agrupado;
  }
}
