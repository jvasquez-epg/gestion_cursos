<?php
// admin/models/ReportesModel.php

class ReportesModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarPeriodos(): array
    {
        $stmt = $this->pdo->query("SELECT id, CONCAT(anio, '-', periodo) AS label FROM periodos ORDER BY anio DESC, periodo DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimoPeriodoId(): ?int
    {
        $stmt = $this->pdo->query("SELECT id FROM periodos ORDER BY anio DESC, periodo DESC LIMIT 1");
        return $stmt->fetchColumn() ?: null;
    }
    public function getDatosReportes(int $periodoId): array
    {
        $reportes = [];

        // Siempre visibles
        $reportes[] = [
            'titulo' => 'Histórico de Solicitudes',
            'tipo' => 'historico',
            'ver_url' => "reportes.php?action=ver&tipo=historico&periodo_id=$periodoId",
            'descargar_url' => "reportes.php?action=descargar&tipo=historico&periodo_id=$periodoId",
            'tipo_reporte' => 'reporte',
            'hay_datos' => $this->hayHistorico($periodoId)
        ];

        $reportes[] = [
            'titulo' => 'Catálogo de Cursos',
            'tipo' => 'catalogo',
            'ver_url' => "reportes.php?action=ver&tipo=catalogo&periodo_id=$periodoId",
            'descargar_url' => "reportes.php?action=descargar&tipo=catalogo&periodo_id=$periodoId",
            'tipo_reporte' => 'reporte',
            'hay_datos' => $this->hayCatalogo($periodoId)
        ];

        // Verifica si finalizó
        $stmt = $this->pdo->prepare("SELECT fin_asignacion_docentes FROM periodos WHERE id = ? LIMIT 1");
        $stmt->execute([$periodoId]);
        $fin = $stmt->fetchColumn();

        $periodoFinalizado = $fin && strtotime($fin) < time();

        if ($periodoFinalizado) {
            $reportes[] = [
                'titulo' => 'Acta de Denegación de Cursos',
                'tipo' => 'denegacion',
                'ver_url' => "reportes.php?action=ver&tipo=denegacion&periodo_id=$periodoId",
                'descargar_url' => "reportes.php?action=descargar&tipo=denegacion&periodo_id=$periodoId",
                'tipo_reporte' => 'reporte',
                'hay_datos' => $this->hayDenegacion($periodoId)
            ];
            $reportes[] = [
                'titulo' => 'Acta de Apertura de Cursos',
                'tipo' => 'apertura',
                'ver_url' => "reportes.php?action=ver&tipo=apertura&periodo_id=$periodoId",
                'descargar_url' => "reportes.php?action=descargar&tipo=apertura&periodo_id=$periodoId",
                'tipo_reporte' => 'reporte',
                'hay_datos' => $this->hayApertura($periodoId)
            ];
            $reportes[] = [
                'titulo' => 'Resolución Final del Periodo',
                'tipo' => 'resolucion',
                'ver_url' => "reportes.php?action=ver&tipo=resolucion_final&periodo_id=$periodoId",
                'descargar_url' => "reportes.php?action=descargar&tipo=resolucion_final&periodo_id=$periodoId",
                'tipo_reporte' => 'resolucion',
                'hay_datos' => $this->hayResolucion($periodoId)
            ];
        }

        return $reportes;
    }
    public function hayDenegacion(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) FROM (
            SELECT s.curso_id
            FROM solicitudes s
            INNER JOIN cursos c ON c.id = s.curso_id
            WHERE s.periodo_id = ?
            GROUP BY s.curso_id
            HAVING COUNT(*) < (
                SELECT minimo_solicitudes FROM periodos WHERE id = ?
            )
        ) AS sub
    ");
        $stmt->execute([$periodoId, $periodoId]);
        return $stmt->fetchColumn() > 0;
    }


    public function hayApertura(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM asignaciones WHERE periodo_id = ?");
        $stmt->execute([$periodoId]);
        return $stmt->fetchColumn() > 0;
    }

    public function hayResolucion(int $periodoId): bool
    {
        $archivo = __DIR__ . "/../../uploads/resoluciones/resolucion_{$periodoId}.pdf";
        return file_exists($archivo);
    }

    public function hayHistorico(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM solicitudes WHERE periodo_id = ?");
        $stmt->execute([$periodoId]);
        return $stmt->fetchColumn() > 0;
    }

    public function hayCatalogo(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cursos");
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function periodoFinalizado(int $periodoId): bool
    {
        $stmt = $this->pdo->prepare("
        SELECT fin_asignacion_docentes 
        FROM periodos 
        WHERE id = ?
        LIMIT 1
    ");
        $stmt->execute([$periodoId]);
        $fechaFin = $stmt->fetchColumn();

        return $fechaFin && strtotime($fechaFin) < time();
    }


    public function getMinimoSolicitudes(int $periodoId): int
    {
        $stmt = $this->pdo->prepare("SELECT minimo_solicitudes FROM periodos WHERE id = ?");
        $stmt->execute([$periodoId]);
        $valor = $stmt->fetchColumn();

        return $valor !== false ? (int) $valor : 8; // valor por defecto si no hay resultado
    }

    public function getCursosDenegados(int $periodoId): array
    {
        try {
            // Obtener el mínimo configurado en el periodo
            $stmtMin = $this->pdo->prepare("SELECT minimo_solicitudes FROM periodos WHERE id = ?");
            $stmtMin->execute([$periodoId]);
            $minimo = (int) $stmtMin->fetchColumn();
            if (!$minimo) {
                $minimo = 8; // valor por defecto
            }

            $stmt = $this->pdo->prepare("
            SELECT 
                c.codigo AS codigo_curso,
                c.nombre AS nombre_curso,
                COUNT(s.id) AS total_solicitudes,
                CASE 
                    WHEN COUNT(s.id) < :minimo THEN 'Solicitudes insuficientes'
                    ELSE 'Falta de docente'
                END AS estado_denegacion
            FROM solicitudes s
            JOIN cursos c ON c.id = s.curso_id
            WHERE s.periodo_id = :periodoId
              AND c.id NOT IN (
                  SELECT curso_id FROM asignaciones WHERE periodo_id = :periodoId2
              )
            GROUP BY c.id, c.codigo, c.nombre
            ORDER BY c.codigo
        ");

            $stmt->bindValue(':minimo', $minimo, PDO::PARAM_INT);
            $stmt->bindValue(':periodoId', $periodoId, PDO::PARAM_INT);
            $stmt->bindValue(':periodoId2', $periodoId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            // Puedes loguear el error si deseas
            return []; // retorna array vacío si ocurre un error
        }
    }

    public function getPeriodoLabel(int $periodoId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT anio, periodo FROM periodos WHERE id = ? LIMIT 1");
        $stmt->execute([$periodoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function getActaApertura(int $periodoId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            c.codigo AS codigo_curso,
            c.nombre AS nombre_curso,
            COUNT(s.id) AS total_solicitudes,
            CONCAT(d.apellido_paterno, ' ', d.apellido_materno, ', ', d.nombres) AS docente,
            a.fecha_asignacion
        FROM asignaciones a
        JOIN cursos c ON c.id = a.curso_id
        JOIN docentes d ON d.id = a.docente_id
        LEFT JOIN solicitudes s ON s.curso_id = c.id AND s.periodo_id = a.periodo_id
        WHERE a.periodo_id = ?
        GROUP BY c.id, c.codigo, c.nombre, docente, a.fecha_asignacion
        ORDER BY total_solicitudes DESC
    ");
        $stmt->execute([$periodoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoricoSolicitudes(int $periodoId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            e.codigo_universitario AS codigo_estudiante,
            CONCAT(u.apellido_paterno, ' ', u.apellido_materno, ', ', u.nombres) AS estudiante,
            c.codigo AS codigo_curso,
            c.nombre AS curso,
            s.fecha_solicitud
        FROM solicitudes s
        JOIN estudiantes e ON e.id = s.estudiante_id
        JOIN usuarios u ON u.id = e.id
        JOIN cursos c ON c.id = s.curso_id
        WHERE s.periodo_id = ?
        ORDER BY estudiante, curso
    ");
        $stmt->execute([$periodoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // admin/models/ReportesModel.php

    public function getCatalogoCursos(int $periodoId): array
    {
        $stmt = $this->pdo->prepare("SELECT periodo FROM periodos WHERE id = ?");
        $stmt->execute([$periodoId]);
        $periodo = (int) $stmt->fetchColumn();

        $condicion = '';
        if ($periodo === 1) {
            $condicion = 'WHERE c.ciclo % 2 = 0'; // pares
        } elseif ($periodo === 2) {
            $condicion = 'WHERE c.ciclo % 2 = 1'; // impares
        }

        $sql = "
        SELECT c.codigo, c.nombre, c.ciclo, c.creditos
        FROM cursos c
        $condicion
        ORDER BY c.ciclo, c.nombre
    ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
