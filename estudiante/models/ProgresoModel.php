<?php
/*
 * Modelo ProgresoModel para obtener el estado académico
 * de los cursos de un estudiante.
 *
 * Método principal:
 * - obtenerPorEstudiante(int $estudianteId): array
 *   Retorna listado de cursos con código, nombre, créditos y estado
 *   ordenado por ciclo y código.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

class ProgresoModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve el listado de cursos con su estado para el estudiante
     *
     * @param int $estudianteId
     * @return array
     */
    public function obtenerPorEstudiante(int $estudianteId): array {
        $sql = "
            SELECT 
                c.codigo,
                c.nombre,
                c.creditos,
                pr.estado
            FROM progreso pr
            INNER JOIN cursos c ON pr.curso_id = c.id
            WHERE pr.estudiante_id = ?
            ORDER BY c.ciclo, c.codigo
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estudianteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
