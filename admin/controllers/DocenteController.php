<?php
// admin/controllers/DocenteController.php

require_once __DIR__ . '/../models/DocenteModel.php';

class DocenteController {
    private DocenteModel $model;

    public function __construct(PDO $pdo) {
        $this->model = new DocenteModel($pdo);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // Lista de docentes
    public function index() {
        $docentes = $this->model->getAll();
        require __DIR__ . '/../views/docentes_list.php';
    }

    // Mostrar formulario de creación
    public function create() {
        $docente = null;
        $formAction = BASE_URL . 'admin/docentes.php?action=store';
        $title = 'Registrar Docente';
        require __DIR__ . '/../views/docentes_form.php';
    }

    // Mostrar formulario de edición
    public function edit(int $id) {
        $docente = $this->model->getById($id);
        $formAction = BASE_URL . 'admin/docentes.php?action=store';
        $title = 'Editar Docente';
        require __DIR__ . '/../views/docentes_form.php';
    }

    // Guardar nuevo o actualizar
    public function store(array $data) {
        try {
            if (!empty($data['id'])) {
                $this->model->actualizar($data);
                $_SESSION['success'] = 'Docente actualizado correctamente.';
            } else {
                $this->model->crear($data);
                $_SESSION['success'] = 'Docente registrado correctamente.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage(); // ← Aquí capturas el error
        }

        header('Location: ' . BASE_URL . 'admin/docentes.php');
        exit;
    }


    // Eliminar docente
    public function delete(int $id) {
        $this->model->eliminar($id);
        $_SESSION['success'] = 'Docente eliminado correctamente.';
        header('Location: ' . BASE_URL . 'admin/docentes.php');
        exit;
    }
}
