<?php
/**
 * Controlador para la gestión de asignaciones de docentes a cursos.
 * Implementa las operaciones de listado, detalle, creación, actualización y eliminación de asignaciones,
 * utilizando el modelo AsignacionModel y PeriodoModel bajo el patrón MVC.
 * Acceso restringido a usuarios con rol administrativo.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

 
// admin/controllers/AsignacionController.php

require_once __DIR__ . '/../models/AsignacionModel.php';
require_once __DIR__ . '/../models/PeriodoModel.php';

class AsignacionController {
    private PDO $pdo;
    private AsignacionModel $model;
    private PeriodoModel $perModel;

    public function __construct(PDO $pdo) {
        // Iniciar sesión si no está activa
        if (
            !isset($_SESSION['usuario_rol']) ||
            !in_array($_SESSION['usuario_rol'], ['administrador','administrativo'], true)
        ) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        $this->pdo      = $pdo;
        $this->model    = new AsignacionModel($pdo);
        $this->perModel = new PeriodoModel($pdo);
    }

    /**
     * Muestra cursos listos para asignar docentes
     */
    public function index() {
        $periodo = $this->perModel->getActivo();
        if (!$periodo) {
            $cursos = [];
        } else {
            $pid    = (int)$periodo['id'];
            $minReq = (int)$periodo['minimo_solicitudes'];
            $cursos = $this->model->getCursosParaAsignar($pid, $minReq);
        }
        include __DIR__ . '/../views/asignaciones.php';
    }

    /**
     * Partial AJAX: lista docentes y datos del curso para el modal
     */
    public function detalle(int $cursoId) {
        $periodo  = $this->perModel->getActivo();
        $docentes = [];
        $curso     = null;

        if ($periodo) {
            // Obtener todos los docentes
            $docentes = $this->model->getDocentes();

            // Buscar el curso en la lista
            $todos = $this->model->getCursosParaAsignar(
                (int)$periodo['id'],
                (int)$periodo['minimo_solicitudes']
            );
            foreach ($todos as $c) {
                if ((int)$c['curso_id'] === $cursoId) {
                    $curso = $c;
                    break;
                }
            }
        }

        include __DIR__ . '/../views/asignaciones_detalle.php';
    }

    /**
     * Guarda o actualiza la asignación enviada desde el formulario
     */
    public function store(array $data) {
        $cursoId    = (int)($data['curso_id'] ?? 0);
        $docenteId  = (int)($data['docente_id'] ?? 0);
        $asigId     = !empty($data['asignacion_id']) ? (int)$data['asignacion_id'] : null;

        if ($docenteId <= 0) {
            $_SESSION['error'] = 'Debes seleccionar un docente.';
        } else {
            if ($asigId) {
                $this->model->actualizarAsignacion($asigId, $docenteId);
                $_SESSION['success'] = 'Asignación actualizada correctamente.';
            } else {
                $periodo = $this->perModel->getActivo();
                $pid     = $periodo ? (int)$periodo['id'] : 0;
                $this->model->asignarDocente($cursoId, $pid, $docenteId);
                $_SESSION['success'] = 'Docente asignado correctamente.';
            }
        }

        header('Location: ' . BASE_URL . 'admin/asignaciones.php');
        exit;
    }

    /**
     * Elimina una asignación existente
     */
    public function delete(int $asignacionId) {
        $this->model->eliminarAsignacion($asignacionId);
        $_SESSION['success'] = 'Asignación eliminada correctamente.';
        header('Location: ' . BASE_URL . 'admin/asignaciones.php');
        exit;
    }
}
