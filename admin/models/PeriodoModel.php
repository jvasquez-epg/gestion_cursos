<?php
// admin/models/PeriodoModel.php

class PeriodoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Marca como 'cerrado' el periodo que estuviera activo
     */
    public function closeActivo(): void
    {
        $this->pdo->exec("
            UPDATE periodos
               SET estado = 'cerrado'
             WHERE estado = 'activo'
        ");
    }

    /**
     * Crea un nuevo periodo y lo deja en estado 'activo'
     */
    public function create(
        int $anio,
        int $periodo,
        string $fEnvioInicio,
        string $fEnvioFin,
        string $fAperturaInicio,
        string $fAperturaFin,
        int $minSolicitudes,
        int $maxCursos,
        int $maxCreditos
    ): void {
        $sql = "
            INSERT INTO periodos (
                anio,
                periodo,
                inicio_envio_solicitudes,
                fin_envio_solicitudes,
                inicio_asignacion_docentes,
                fin_asignacion_docentes,
                minimo_solicitudes,
                maximo_cursos,
                maximo_creditos,
                estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $anio,
            $periodo,
            $fEnvioInicio,
            $fEnvioFin,
            $fAperturaInicio,
            $fAperturaFin,
            $minSolicitudes,
            $maxCursos,
            $maxCreditos,
        ]);
    }

    /**
     * Verifica si ya existe un periodo con el mismo año y número
     */
    public function existePeriodo(int $anio, int $periodo): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM periodos
             WHERE anio = ? AND periodo = ?
        ");
        $stmt->execute([$anio, $periodo]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getActivo(): ?array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM periodos
             WHERE estado = 'activo'
             LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getHistorial(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM periodos
             ORDER BY anio DESC, periodo DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM periodos
             WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getUltimo(): ?array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM periodos
             ORDER BY id DESC
             LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(
        int $id,
        int $anio,
        int $periodo,
        string $fEnvioInicio,
        string $fEnvioFin,
        string $fAperturaInicio,
        string $fAperturaFin,
        int $minSolicitudes,
        int $maxCursos,
        int $maxCreditos
    ): void {
        $sql = "
            UPDATE periodos SET
                anio                       = ?,
                periodo                    = ?,
                inicio_envio_solicitudes   = ?,
                fin_envio_solicitudes      = ?,
                inicio_asignacion_docentes = ?,
                fin_asignacion_docentes    = ?,
                minimo_solicitudes         = ?,
                maximo_cursos              = ?,
                maximo_creditos            = ?
             WHERE id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $anio,
            $periodo,
            $fEnvioInicio,
            $fEnvioFin,
            $fAperturaInicio,
            $fAperturaFin,
            $minSolicitudes,
            $maxCursos,
            $maxCreditos,
            $id,
        ]);
    }

    public function countEnvios(int $periodoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM solicitudes
             WHERE periodo_id = ?
        ");
        $stmt->execute([$periodoId]);
        return (int)$stmt->fetchColumn();
    }

    public function countAperturados(int $periodoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT curso_id) FROM asignaciones
             WHERE periodo_id = ?
        ");
        $stmt->execute([$periodoId]);
        return (int)$stmt->fetchColumn();
    }

    public function getResolucion(int $periodoId): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT documento FROM resoluciones
             WHERE periodo_id = ?
             ORDER BY fecha_resolucion DESC
             LIMIT 1
        ");
        $stmt->execute([$periodoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['documento'] : null;
    }

    public function hasSolicitudes(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM solicitudes
             WHERE periodo_id = ?
        ");
        $stmt->execute([$periodoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function delete(int $periodoId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM periodos WHERE id = ?
        ");
        $stmt->execute([$periodoId]);
    }

    /**
     * Cierra automáticamente los periodos activos cuyo fin de asignación ya pasó
     * y devuelve el array de IDs que se cerraron.
     */
    public function verificarYActualizarEstado(): array
    {
        // 1) IDs a cerrar
        $stmt = $this->pdo->query("
            SELECT id
              FROM periodos
             WHERE estado = 'activo'
               AND fin_asignacion_docentes < NOW()
        ");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($ids) {
            // 2) cerrar
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $upd = $this->pdo->prepare("
                UPDATE periodos
                   SET estado = 'cerrado'
                 WHERE id IN ($ph)
            ");
            $upd->execute($ids);
        }
        return array_map('intval', $ids);
    }

    public function countSolicitudesExcedentes(int $periodoId, int $maxCursos, int $maxCreditos): int
    {
        $sql = "
            SELECT COUNT(*) FROM (
                SELECT s.estudiante_id,
                       COUNT(*) AS cursos_solicitados,
                       SUM(c.creditos) AS creditos_solicitados
                  FROM solicitudes s
                  JOIN cursos c ON c.id = s.curso_id
                 WHERE s.periodo_id = :pid
                 GROUP BY s.estudiante_id
                HAVING cursos_solicitados > :maxC
                    OR creditos_solicitados > :maxCr
            ) AS sub
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pid'   => $periodoId,
            ':maxC'  => $maxCursos,
            ':maxCr' => $maxCreditos,
        ]);
        return (int)$stmt->fetchColumn();
    }

    /** 
     * Recupera el valor de mínimo de solicitudes para un periodo.
     */
    private function getMinimoSolicitudes(int $periodoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT minimo_solicitudes FROM periodos
             WHERE id = ?
        ");
        $stmt->execute([$periodoId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Cursos con docente asignado Y habilitados (>= mínimo solicitudes),
     * filtrados por ciclo (par/impar) según tipo de periodo.
     */
    public function getCursosAsignados(int $periodoId, int $cicloPeriodo): array
    {
        $minS = $this->getMinimoSolicitudes($periodoId);

        // Construir condición de ciclo
        $condicionCiclo = '';
        $parametros = [
            ':periodoId' => $periodoId,
            ':minS' => $minS
        ];

        // Si es 3, muestra todos los cursos que tengan solicitudes

        $sql = "
            SELECT
                c.codigo,
                c.nombre,
                CONCAT(d.nombres,' ',d.apellido_paterno,' ',d.apellido_materno) AS docente,
                COUNT(s.id) AS solicitudes
            FROM asignaciones a
            INNER JOIN cursos c    ON a.curso_id = c.id
            INNER JOIN docentes d  ON a.docente_id = d.id
            LEFT JOIN solicitudes s ON s.curso_id = c.id AND s.periodo_id = a.periodo_id
            WHERE a.periodo_id = :periodoId
            $condicionCiclo
            GROUP BY c.id, d.id, c.codigo, c.nombre, d.nombres, d.apellido_paterno, d.apellido_materno
            HAVING COUNT(s.id) >= :minS
            ORDER BY c.codigo
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cursos con solicitudes >= mínimo pero SIN docente asignado,
     * filtrados por ciclo.
     */
    public function getCursosSinDocente(int $periodoId, int $tipoPeriodo): array
    {
        $minS = $this->getMinimoSolicitudes($periodoId);
        // $par = $tipoPeriodo % 2; // This line is no longer strictly needed if the condition is removed

        $sql = "
        SELECT
            c.codigo,
            c.nombre,
            COUNT(s.id) AS solicitudes
        FROM cursos c
        LEFT JOIN asignaciones a
            ON a.curso_id = c.id AND a.periodo_id = :pid
        LEFT JOIN solicitudes s
            ON s.curso_id = c.id AND s.periodo_id = :pid2
        WHERE s.id IS NOT NULL
          AND a.id IS NULL
          -- AND (                 -- This entire block is now commented out/removed
          --       :tipo3 = 3
          --       OR MOD(c.ciclo,2) = :par
          -- )
        GROUP BY c.id, c.codigo, c.nombre -- Added c.codigo, c.nombre to GROUP BY for strict SQL_MODE
        HAVING solicitudes >= :minS
        ORDER BY c.codigo
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pid'   => $periodoId,
            ':pid2'  => $periodoId,
            // ':tipo3' => $tipoPeriodo, // No longer needed
            // ':par'   => $par,         // No longer needed
            ':minS'  => $minS,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cursos con solicitudes < mínimo, filtrados por ciclo.
     */
    public function getCursosSinSolicitudes(int $periodoId, int $tipoPeriodo): array
    {
        $minS = $this->getMinimoSolicitudes($periodoId);
        $par  = $tipoPeriodo % 2;

        $sql = "
        SELECT
            c.codigo,
            c.nombre,
            0 AS solicitudes
        FROM cursos c
        WHERE c.id NOT IN (
                SELECT curso_id FROM solicitudes WHERE periodo_id = :pid
            )
          AND (
                :tipo3 = 3
                OR MOD(c.ciclo,2) = :par
          )
        ORDER BY c.codigo
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pid'   => $periodoId,
            ':tipo3' => $tipoPeriodo,
            ':par'   => $par,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function saveResolucion(int $periodoId, string $filename): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO resoluciones (periodo_id, documento, fecha_resolucion)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$periodoId, $filename]);
    }

    public function getNextResolucionNumber(int $anio): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) + 1
              FROM resoluciones r
              JOIN periodos      p ON r.periodo_id = p.id
             WHERE p.anio = ?
        ");
        $stmt->execute([$anio]);
        return (int)$stmt->fetchColumn();
    }

    public function getPeriodoLabel(int $id): ?array
    {
        $sql = "SELECT id, anio, periodo, estado FROM periodos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
