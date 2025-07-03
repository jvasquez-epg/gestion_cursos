<?php
// admin/controllers/PublicarController.php

require_once __DIR__ . '/../models/AsignacionModel.php';
require_once __DIR__ . '/../models/PeriodoModel.php';

class PublicarController {
    private PDO $pdo;
    private AsignacionModel $model;
    private PeriodoModel $perModel;

    public function __construct(PDO $pdo) {
        $this->pdo      = $pdo;
        $this->model    = new AsignacionModel($pdo);
        $this->perModel = new PeriodoModel($pdo);
    }

    /**
     * Prepara los tres conjuntos de cursos para publicación
     * @return array [asignados, sinDocente, insuficientes]
     */
    public function publicar(): array {
        $periodo = $this->perModel->getActivo();
        if (!$periodo) {
            return [[],[],[]];
        }
        $pid = (int)$periodo['id'];
        $min = (int)$periodo['minimo_solicitudes'];

        // Todos los cursos con al menos 1 solicitud
        $todos = $this->model->getCursosParaAsignar($pid, 1);

        $asignados    = [];
        $sinDocente   = [];
        $insuficientes = [];

        foreach ($todos as $c) {
            if (!empty($c['docente_id'])) {
                $asignados[] = $c;
            } elseif ($c['total_solicitudes'] >= $min) {
                // casos no deberían estar aquí porque AsignacionModel filtra por >=min
                $asignados[] = $c;
            } elseif ($c['total_solicitudes'] > 0) {
                $insuficientes[] = array_merge($c, ['minimo' => $min]);
            } else {
                // cursos sin solicitudes se ignoran
            }
            // también detectar los sin docente pero con solicitudes >=min
            if (empty($c['docente_id']) && $c['total_solicitudes'] >= $min) {
                $sinDocente[] = $c;
            }
        }

        return [$asignados, $sinDocente, $insuficientes];
    }
}
