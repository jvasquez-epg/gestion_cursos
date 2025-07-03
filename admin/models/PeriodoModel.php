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
        $this->pdo->exec(
            "UPDATE periodos
             SET estado = 'cerrado'
             WHERE estado = 'activo'"
        );
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
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
               FROM periodos
              WHERE anio = ? AND periodo = ?"
        );
        $stmt->execute([$anio, $periodo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Devuelve el periodo marcado como 'activo'
     */
    public function getActivo(): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
               FROM periodos
              WHERE estado = 'activo'
              LIMIT 1"
        );
        $stmt->execute();
        $period = $stmt->fetch(PDO::FETCH_ASSOC);
        return $period ?: null;
    }

    /**
     * Devuelve todos los periodos (activo y cerrados), ordenados
     */
    public function getHistorial(): array
    {
        $stmt = $this->pdo->query(
            "SELECT *
               FROM periodos
              ORDER BY anio DESC, periodo DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un periodo por su ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
               FROM periodos
              WHERE id = ?"
        );
        $stmt->execute([$id]);
        $period = $stmt->fetch(PDO::FETCH_ASSOC);
        return $period ?: null;
    }

    /**
     * Devuelve el último periodo registrado
     */
    public function getUltimo(): ?array
    {
        $stmt = $this->pdo->query(
            "SELECT *
               FROM periodos
              ORDER BY id DESC
              LIMIT 1"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Actualiza un periodo (sin tocar su estado)
     */
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

    /**
     * Cuenta solicitudes enviadas en un periodo
     */
    public function countEnvios(int $idPeriodo): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
               FROM solicitudes
              WHERE periodo_id = ?"
        );
        $stmt->execute([$idPeriodo]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cuenta cursos asignados (aperturados) en un periodo
     */
    public function countAperturados(int $idPeriodo): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT curso_id)
               FROM asignaciones
              WHERE periodo_id = ?"
        );
        $stmt->execute([$idPeriodo]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene el último documento de resolución de un periodo
     */
    public function getResolucion(int $idPeriodo): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT documento
               FROM resoluciones
              WHERE periodo_id = ?
              ORDER BY fecha_resolucion DESC
              LIMIT 1"
        );
        $stmt->execute([$idPeriodo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['documento'] : null;
    }

    /**
     * Verifica si un periodo tiene al menos una solicitud
     */
    public function hasSolicitudes(int $idPeriodo): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
               FROM solicitudes
              WHERE periodo_id = ?"
        );
        $stmt->execute([$idPeriodo]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Elimina un periodo (solo si no tiene solicitudes)
     */
    public function delete(int $idPeriodo): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM periodos
              WHERE id = ?"
        );
        $stmt->execute([$idPeriodo]);
    }

    public function verificarYActualizarEstado(): void
    {
        $this->pdo->exec(
            "UPDATE periodos
               SET estado = 'cerrado'
             WHERE estado = 'activo'
               AND fin_asignacion_docentes < NOW()"
        );
    }

    /**
     * Devuelve el número de solicitudes que exceden los límites dados.
     */
    public function countSolicitudesExcedentes(int $periodoId, int $maxCursos, int $maxCreditos): int
    {
        $sql = "
        SELECT COUNT(*) AS cnt
        FROM (
            SELECT s.estudiante_id,
                   COUNT(*) AS cursos_solicitados,
                   SUM(c.creditos) AS creditos_solicitados
            FROM solicitudes s
            JOIN cursos c ON c.id = s.curso_id
            WHERE s.periodo_id = :pid
            GROUP BY s.estudiante_id
            HAVING cursos_solicitados > :maxC
               OR creditos_solicitados > :maxCr
        ) AS sub;
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pid' => $periodoId,
            ':maxC' => $maxCursos,
            ':maxCr' => $maxCreditos,
        ]);
        return (int) $stmt->fetchColumn();
    }

}
?>