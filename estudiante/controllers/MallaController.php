<?php
// estudiante/controllers/MallaController.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../models/MallaModel.php';

class MallaController
{
    private PDO        $pdo;
    private MallaModel $mallaModel;

    public function __construct(PDO $pdo)
    {
        session_start();
        if (
            !isset($_SESSION['usuario_rol']) ||
            $_SESSION['usuario_rol'] !== 'estudiante'
        ) {
            header('Location: '.BASE_URL.'login.php');
            exit;
        }

        $this->pdo         = $pdo;
        $this->mallaModel  = new MallaModel($pdo);
    }

    /** Muestra la malla curricular agrupada por ciclo */
    public function dashboard(): void
    {
        $est   = (int) $_SESSION['usuario_id'];

        /* ①  Detectar la malla del estudiante */
        $stmt = $this->pdo->prepare(
            "SELECT malla_id
             FROM   estudiantes
             WHERE  id = ? LIMIT 1"
        );
        $stmt->execute([$est]);
        $mallaId = (int) $stmt->fetchColumn();

        if (!$mallaId) {
            echo 'Malla no asignada.'; return;
        }

        /* ②  Traer cursos agrupados */
        $ciclos = $this->mallaModel->cursosPorMalla($mallaId);
        /* ③  Pasar a la vista */
        include __DIR__ . '/../views/malla_dashboard.php';
    }
}