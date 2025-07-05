<?php
/**
 * Modelo de Asignaciones.
 * Permite la gestión de la relación entre cursos y docentes asignados en cada periodo académico,
 * contemplando operaciones de consulta, registro, actualización y eliminación de asignaciones.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

 
// admin/models/AsignacionModel.php

class AsignacionModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene cursos que alcanzaron al menos el mínimo de solicitudes para asignar docentes.
     * Usa parámetros separados para evitar conflictos de placeholders.
     *
     * @param int $periodoId
     * @param int $minSolicitudes
     * @return array
     */
    public function getCursosParaAsignar(int $periodoId, int $minSolicitudes): array {
        $sql = <<<'SQL'
SELECT
  c.id                   AS curso_id,
  c.codigo               AS codigo,
  c.nombre               AS nombre,
  c.ciclo                AS ciclo,
  COUNT(s.id)            AS total_solicitudes,
  a.id                   AS asignacion_id,
  a.docente_id           AS docente_id,
  CONCAT(d.nombres,' ',d.apellido_paterno,' ',d.apellido_materno) AS docente_nombre
FROM cursos c
LEFT JOIN solicitudes s
  ON s.curso_id   = c.id
 AND s.periodo_id = :pid_s
LEFT JOIN asignaciones a
  ON a.curso_id   = c.id
 AND a.periodo_id = :pid_a
LEFT JOIN docentes d
  ON d.id = a.docente_id
GROUP BY c.id, a.id, d.id
HAVING COUNT(s.id) >= :min_s
ORDER BY total_solicitudes DESC, c.codigo
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pid_s' => $periodoId,
            ':pid_a' => $periodoId,
            ':min_s' => $minSolicitudes,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve todos los docentes.
     *
     * @return array
     */
    public function getDocentes(): array {
        $sql = "
SELECT
  id,
  nombres,
  apellido_paterno,
  apellido_materno,
  dni
FROM docentes
ORDER BY apellido_paterno, nombres
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta nueva asignación de docente a curso en un periodo.
     *
     * @param int $cursoId
     * @param int $periodoId
     * @param int $docenteId
     */
    public function asignarDocente(int $cursoId, int $periodoId, int $docenteId): void {
        $sql = "INSERT INTO asignaciones (curso_id, periodo_id, docente_id) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cursoId, $periodoId, $docenteId]);
    }

    /**
     * Actualiza asignación existente.
     *
     * @param int $asignacionId
     * @param int $docenteId
     */
    public function actualizarAsignacion(int $asignacionId, int $docenteId): void {
        $sql = "UPDATE asignaciones SET docente_id = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$docenteId, $asignacionId]);
    }

    /**
     * Elimina una asignación por su ID.
     *
     * @param int $asignacionId
     */
    public function eliminarAsignacion(int $asignacionId): void {
        $sql = "DELETE FROM asignaciones WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asignacionId]);
    }
}
