<?php
// estudiante/controllers/ProgresoController.php

require_once __DIR__ . '/../models/ProgresoModel.php';

class ProgresoController {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function dashboard() {
        session_start();

        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'estudiante') {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $estudianteId = $_SESSION['usuario_id'];

        $model = new ProgresoModel($this->pdo);
        $progreso = $model->obtenerPorEstudiante($estudianteId);

        include __DIR__ . '/../views/progreso_dashboard.php';
    }
}
