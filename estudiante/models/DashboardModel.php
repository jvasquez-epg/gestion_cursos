<?php
// estudiante/models/DashboardModel.php

class DashboardModel
{
  private PDO $pdo;
  private int $estudianteId;
  private int $mallaId;

  public function __construct(PDO $pdo, int $estudianteId, int $mallaId)
  {
    $this->pdo = $pdo;
    $this->estudianteId = $estudianteId;
    $this->mallaId = $mallaId;
  }

  public function getPeriodoActivo(): ?array
  {
    // Opción A: dejar que MySQL compare con NOW()
    $sql = "
        SELECT *
        FROM periodos
        WHERE estado = 'activo'        -- o estado = 1 si es TINYINT
          AND NOW() BETWEEN 
              inicio_envio_solicitudes 
              AND fin_asignacion_docentes
        LIMIT 1
    ";
    $stmt = $this->pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public function getFase(array $periodo): string
  {
    // Ahora sí incluimos la hora
    $hoy = date('Y-m-d H:i:s');

    if (
      $hoy >= $periodo['inicio_envio_solicitudes']
      && $hoy <= $periodo['fin_envio_solicitudes']
    ) {
      return 'envio';
    }
    if (
      $hoy >= $periodo['inicio_asignacion_docentes']
      && $hoy <= $periodo['fin_asignacion_docentes']
    ) {
      return 'asignacion';
    }
    return 'finalizado';
  }

  public function countCursosMenu(): int
  {
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cursos WHERE malla_id = ?");
    $stmt->execute([$this->mallaId]);
    return (int) $stmt->fetchColumn();
  }

  public function getParamPeriodo(int $periodoId): array
  {
    $stmt = $this->pdo->prepare("
          SELECT minimo_solicitudes, maximo_cursos, maximo_creditos,
                 inicio_envio_solicitudes, fin_envio_solicitudes,
                 inicio_asignacion_docentes, fin_asignacion_docentes
          FROM periodos WHERE id = ?");
    $stmt->execute([$periodoId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getSolicitudesStats(int $periodoId): array
  {
    $stmt = $this->pdo->prepare("
          SELECT s.curso_id, c.creditos
          FROM solicitudes s
          JOIN cursos c ON c.id = s.curso_id
          WHERE s.estudiante_id = ? AND s.periodo_id = ?
        ");
    $stmt->execute([$this->estudianteId, $periodoId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($rows);
    $creditos = array_sum(array_column($rows, 'creditos'));
    return ['count' => $count, 'creditos' => $creditos, 'cursos' => array_column($rows, 'curso_id')];
  }

  public function getAsignaciones(int $periodoId): array
  {
    $stmt = $this->pdo->prepare("
          SELECT c.codigo, c.nombre,
                 CASE WHEN a.id IS NULL THEN 'pendiente' ELSE 'asignado' END AS estado,
                 IFNULL(d.nombres,'-') AS docente
          FROM solicitudes s
          LEFT JOIN asignaciones a 
            ON a.curso_id = s.curso_id AND a.periodo_id = s.periodo_id
          LEFT JOIN cursos c ON c.id = s.curso_id
          LEFT JOIN docentes d ON d.id = a.docente_id
          WHERE s.estudiante_id = ? AND s.periodo_id = ?
        ");
    $stmt->execute([$this->estudianteId, $periodoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getCursosAperturados(int $periodoId): array
  {
    $stmt = $this->pdo->prepare("
          SELECT c.codigo, c.nombre, d.nombres AS docente
          FROM asignaciones a
          JOIN cursos c ON c.id = a.curso_id
          JOIN docentes d ON d.id = a.docente_id
          WHERE a.periodo_id = ?
        ");
    $stmt->execute([$periodoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // al final de la clase DashboardModel

  /**
   * Devuelve el detalle de los cursos que el estudiante ha solicitado
   */
  public function getDetalleSolicitudes(int $periodoId): array
  {
    $sql = "
      SELECT c.codigo, c.nombre, c.creditos
      FROM solicitudes s
      JOIN cursos c ON c.id = s.curso_id
      WHERE s.estudiante_id = ? AND s.periodo_id = ?
      ORDER BY c.codigo
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$this->estudianteId, $periodoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getResumenFinalAsignaciones(int $periodoId): array
  {
    $sql = "
        SELECT 
            c.codigo,
            c.nombre,
            d.nombres AS docente,
            COUNT(s.id) AS total_solicitudes
        FROM asignaciones a
        JOIN cursos c ON c.id = a.curso_id
        JOIN docentes d ON d.id = a.docente_id
        LEFT JOIN solicitudes s 
            ON s.curso_id = a.curso_id AND s.periodo_id = a.periodo_id
        WHERE a.periodo_id = ?
        GROUP BY c.codigo, c.nombre, d.nombres
        ORDER BY total_solicitudes DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$periodoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


}
