<?php
// admin/controllers/PeriodoController.php

require_once __DIR__ . '/../models/PeriodoModel.php';

class PeriodoController
{
    private PDO $pdo;
    private PeriodoModel $periodoModel;

    public function __construct(PDO $pdo)
    {
        // Asegurarnos de que la sesión está iniciada
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Sólo administradores pueden acceder
        if (
            !isset($_SESSION['usuario_rol']) ||
            !in_array($_SESSION['usuario_rol'], ['administrador','administrativo'], true)
        ) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        $this->pdo = $pdo;
        $this->periodoModel = new PeriodoModel($this->pdo);
    }

    /**
     * Muestra la lista de periodos: activo y su historial
     */
    public function index()
    {
        // Primero, verificar y actualizar el estado de los periodos según fecha
        $this->periodoModel->verificarYActualizarEstado();

        // Obtener periodo activo y sus estadísticas
        $periodoActivo = $this->periodoModel->getActivo();
        if ($periodoActivo) {
            $id = (int)$periodoActivo['id'];
            $periodoActivo['total_solicitudes'] = $this->periodoModel->countEnvios($id);
            $periodoActivo['cursos_asignados']  = $this->periodoModel->countAperturados($id);
            $periodoActivo['ultima_resolucion'] = $this->periodoModel->getResolucion($id);
        }

        // Obtener historial de periodos cerrados y sus estadísticas
        $historial = $this->periodoModel->getHistorial();
        foreach ($historial as &$p) {
            $pid = (int)$p['id'];
            $p['total_solicitudes'] = $this->periodoModel->countEnvios($pid);
            $p['cursos_asignados']  = $this->periodoModel->countAperturados($pid);
            $p['ultima_resolucion'] = $this->periodoModel->getResolucion($pid);
        }
        unset($p);

        // Mensajes flash
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        include __DIR__ . '/../views/periodos.php';
    }

    /**
     * Formulario para crear un nuevo periodo
     */
    public function create()
    {
        // Determinar el siguiente periodo disponible
        $last     = $this->periodoModel->getUltimo();
        $anio     = $last ? (int)$last['anio'] : (int)date('Y');
        $periodo  = $last ? (int)$last['periodo'] : 1;
        if ($periodo < 3) {
            $periodo++;
        } else {
            $anio++;
            $periodo = 1;
        }

        // Valores por defecto de fechas y límites
        $now = date('Y-m-d H:i:00');
        $periodData = [
            'anio'                       => $anio,
            'periodo'                    => $periodo,
            'inicio_envio_solicitudes'   => $now,
            'fin_envio_solicitudes'      => $now,
            'inicio_asignacion_docentes' => $now,
            'fin_asignacion_docentes'    => $now,
            'minimo_solicitudes'         => 15,
            'maximo_cursos'              => 3,
            'maximo_creditos'            => 12,
        ];

        // Mensajes flash
        $error   = $_SESSION['error']   ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        $formAction  = '?action=store';
        $buttonLabel = 'Registrar Periodo';
        include __DIR__ . '/../views/periodos_create.php';
    }

    /**
     * Guarda el nuevo periodo en base de datos
     */
    public function store(array $data)
    {
        // Validar que sea el siguiente periodo en secuencia
        $last         = $this->periodoModel->getUltimo();
        $expectedPer  = $last ? ($last['periodo'] < 3 ? $last['periodo'] + 1 : 1) : 1;
        $expectedYear = $last ? ($last['periodo'] < 3 ? $last['anio'] : $last['anio'] + 1) : (int)date('Y');

        if ((int)$data['periodo'] !== $expectedPer || (int)$data['anio'] !== $expectedYear) {
            $_SESSION['error'] = "Solo puedes crear el periodo siguiente: {$expectedYear}-{$expectedPer}.";
            header('Location: ' . BASE_URL . 'admin/periodos.php?action=create');
            exit;
        }

        // Validaciones de fechas, números y duplicados
        if ($msg = $this->validarFechas($data) ?? $this->validarNumeros($data) ?? $this->validarDuplicado($data)) {
            $_SESSION['error'] = $msg;
            header('Location: ' . BASE_URL . 'admin/periodos.php?action=create');
            exit;
        }

        // Normalizar formatos, cerrar periodo activo y crear el nuevo
        $this->normalizarFechas($data);
        $this->periodoModel->closeActivo();
        $this->periodoModel->create(
            anio:                $expectedYear,
            periodo:             $expectedPer,
            fEnvioInicio:        $data['inicio_envio_solicitudes'],
            fEnvioFin:           $data['fin_envio_solicitudes'],
            fAperturaInicio:     $data['inicio_asignacion_docentes'],
            fAperturaFin:        $data['fin_asignacion_docentes'],
            minSolicitudes:      (int)$data['minimo_solicitudes'],
            maxCursos:           (int)$data['maximo_cursos'],
            maxCreditos:         (int)$data['maximo_creditos']
        );

        $_SESSION['success'] = "Periodo {$expectedYear}-{$expectedPer} creado correctamente.";
        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    /**
     * Formulario para editar un periodo existente
     */
    public function edit(int $id)
    {
        $periodo = $this->periodoModel->getById($id);
        if (!$periodo) {
            header('Location: ' . BASE_URL . 'admin/periodos.php');
            exit;
        }

        $periodData  = $periodo;
        $formAction  = '?action=update&id=' . $id;
        $buttonLabel = 'Guardar Cambios';
        include __DIR__ . '/../views/periodos_create.php';
    }

    /**
     * Procesa la actualización de un periodo
     */
    public function update(array $data)
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // 1) Validar fechas y valores numéricos
        if ($msg = $this->validarFechas($data) ?? $this->validarNumeros($data)) {
            $_SESSION['error'] = $msg;
            header('Location: ' . BASE_URL . "admin/periodos.php?action=edit&id={$id}");
            exit;
        }

        // 2) Verificar si hay solicitudes que exceden los nuevos límites
        $maxCursos   = (int)$data['maximo_cursos'];
        $maxCreditos = (int)$data['maximo_creditos'];
        $confCount   = $this->periodoModel
                            ->countSolicitudesExcedentes($id, $maxCursos, $maxCreditos);

        if ($confCount > 0) {
            $_SESSION['error'] = "Hay {$confCount} solicitud(es) existentes que exceden los nuevos límites "
                               . "de cursos o créditos. Ajusta esas solicitudes antes de guardar.";
            header('Location: ' . BASE_URL . "admin/periodos.php?action=edit&id={$id}");
            exit;
        }

        // 3) Normalizar y actualizar
        $this->normalizarFechas($data);
        $this->periodoModel->update(
            id:                     $id,
            anio:                   (int)$data['anio'],
            periodo:                (int)$data['periodo'],
            fEnvioInicio:           $data['inicio_envio_solicitudes'],
            fEnvioFin:              $data['fin_envio_solicitudes'],
            fAperturaInicio:        $data['inicio_asignacion_docentes'],
            fAperturaFin:           $data['fin_asignacion_docentes'],
            minSolicitudes:         (int)$data['minimo_solicitudes'],
            maxCursos:              $maxCursos,
            maxCreditos:            $maxCreditos
        );

        $_SESSION['success'] = 'Periodo actualizado correctamente.';
        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    /**
     * Elimina un periodo (si no tiene solicitudes asociadas)
     */
    public function delete(int $id)
    {
        if ($this->periodoModel->hasSolicitudes($id)) {
            $_SESSION['error'] = 'No se puede eliminar: ya existen solicitudes en este periodo.';
        } else {
            $this->periodoModel->delete($id);
            $_SESSION['success'] = 'Periodo eliminado correctamente.';
        }

        header('Location: ' . BASE_URL . 'admin/periodos.php');
        exit;
    }

    // --- Métodos auxiliares ---

    /**
     * Convierte los inputs de tipo "datetime-local" a formato de MySQL (YYYY-MM-DD HH:MM:SS)
     */
    private function normalizarFechas(array &$data): void
    {
        foreach ([
            'inicio_envio_solicitudes',
            'fin_envio_solicitudes',
            'inicio_asignacion_docentes',
            'fin_asignacion_docentes'
        ] as $campo) {
            if (!empty($data[$campo])) {
                $data[$campo] = str_replace('T', ' ', $data[$campo]);
                if (strlen($data[$campo]) === 16) {
                    $data[$campo] .= ':00';
                }
            }
        }
    }

    /**
     * Valida la lógica de fechas
     */
    private function validarFechas(array $data): ?string
    {
        $iniEnv = strtotime($data['inicio_envio_solicitudes']);
        $finEnv = strtotime($data['fin_envio_solicitudes']);
        $iniApt = strtotime($data['inicio_asignacion_docentes']);
        $finApt = strtotime($data['fin_asignacion_docentes']);

        if ($iniEnv >= $finEnv) {
            return 'La fecha de fin de envío debe ser posterior al inicio.';
        }
        if ($finEnv > $iniApt) {
            return 'La asignación de docentes debe iniciar después del fin de envío.';
        }
        if ($iniApt > $finApt) {
            return 'La fecha de fin de asignación debe ser posterior al inicio.';
        }
        return null;
    }

    /**
     * Valida que los valores numéricos sean mayores que cero
     */
    private function validarNumeros(array $data): ?string
    {
        $minS  = (int)$data['minimo_solicitudes'];
        $maxC  = (int)$data['maximo_cursos'];
        $maxCr = (int)$data['maximo_creditos'];

        if ($minS <= 0 || $maxC <= 0 || $maxCr <= 0) {
            return 'Los valores deben ser mayores que cero.';
        }
        return null;
    }

    /**
     * Verifica si ya existe un periodo con año y número duplicado
     */
    private function validarDuplicado(array $data): ?string
    {
        if ($this->periodoModel->existePeriodo((int)$data['anio'], (int)$data['periodo'])) {
            return "Ya existe el periodo {$data['anio']}-{$data['periodo']}.";
        }
        return null;
    }
}
