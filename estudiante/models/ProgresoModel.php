<?php
// models/ProgresoModel.php

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
