<?php
/**
 * Controlador de Solicitudes del sistema de gestión académica.
 * Permite la visualización de resúmenes y detalles de solicitudes por curso
 * durante el periodo académico activo, integrando datos relevantes para la gestión administrativa.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

// admin/controllers/SolicitudController.php

require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/PeriodoModel.php';

class SolicitudController
{
    private PDO $pdo;
    private SolicitudModel $solModel;
    private PeriodoModel $perModel;

    public function __construct(PDO $pdo)
    {
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (
            !isset($_SESSION['usuario_rol']) ||
            !in_array($_SESSION['usuario_rol'], ['administrador', 'administrativo'], true)
        ) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        $this->pdo = $pdo;
        $this->solModel = new SolicitudModel($pdo);
        $this->perModel = new PeriodoModel($pdo);
    }

    /**
     * Muestra resumen de solicitudes por curso para el periodo activo
     * Variables pasadas a la vista:
     *   $periodo (array|null)
     *   $minReq  (int)
     *   $cursos  (array)
     */
    public function index()
    {
        $periodo = $this->perModel->getActivo();
        if (!$periodo) {
            $cursos = [];
            $minReq = 0;
        } else {
            $pid = (int) $periodo['id'];
            $cursos = $this->solModel->getResumenPorCurso($pid);
            $minReq = (int) $periodo['minimo_solicitudes'];
        }

        include __DIR__ . '/../views/solicitudes.php';
    }

    /**
     * Muestra detalle de solicitudes para un curso específico (ajax o partial)
     * Variables pasadas al partial:
     *   $detalles (array)
     */
    public function detalle(int $cursoId)
    {
        $periodo = $this->perModel->getActivo();
        if (!$periodo) {
            $detalles = [];
        } else {
            $pid = (int) $periodo['id'];
            $detalles = $this->solModel->getDetallePorCurso($pid, $cursoId);
        }

        include __DIR__ . '/../views/solicitudes_detalle.php';
    }
}
