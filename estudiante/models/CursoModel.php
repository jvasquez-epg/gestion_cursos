<?php
/*
 * Modelo para obtener cursos disponibles para un estudiante en un periodo académico.
 * Filtra cursos pendientes cuyo prerrequisitos están cumplidos,
 * aplica filtro por paridad de ciclo (par/impar/todos),
 * y excluye cursos ya solicitados en el periodo actual.
 * La práctica preprofesional (código 131B10081) solo aparece
 * si el estudiante tiene al menos 180 créditos aprobados.
 * 
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */
declare(strict_types=1);

/**
 * Cursos PENDIENTES cuyos prerrequisitos están CUMPLIDOS,
 * filtrando paridad y descartando los ya solicitados
 * para el periodo activo.
 * 
 * PRACTICA PREPROFESIONAL solo aparece si el estudiante tiene 180 créditos aprobados.
 */
class CursoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int    $est  Estudiante
     * @param int    $per  Periodo activo
     * @param string $par  'par'|'impar'|'todos'
     */
    public function disponibles(
        int    $est,
        int    $per,
        string $par = 'todos'
    ): array {
        // Paridad dinámica
        $paridad = '';
        if ($par === 'par')   { $paridad = 'AND MOD(c.ciclo,2)=0'; }
        if ($par === 'impar') { $paridad = 'AND MOD(c.ciclo,2)=1'; }

        $sql = "
            SELECT c.*
            FROM   cursos c
            /* Estado PENDIENTE */
            JOIN   progreso pr
                   ON pr.curso_id      = c.id
                  AND pr.estudiante_id = ?
                  AND pr.estado        = 'Pendiente'
            WHERE  1=1 $paridad
              /* No solicitados en este periodo */
              AND NOT EXISTS (
                    SELECT 1
                    FROM   solicitudes s
                    WHERE  s.estudiante_id = ?
                      AND  s.periodo_id    = ?
                      AND  s.curso_id      = c.id
                  )
              /* Todos los prerrequisitos cumplidos */
              AND NOT EXISTS (
                    SELECT 1
                    FROM   prerrequisitos p
                    LEFT   JOIN progreso pr_ok
                           ON pr_ok.curso_id      = p.prerrequisito_id
                          AND pr_ok.estudiante_id = ?
                          AND pr_ok.estado        = 'Cumplido'
                    WHERE  p.curso_id = c.id
                      AND  pr_ok.id IS NULL
                  )
            ORDER BY c.ciclo, c.codigo;
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$est, $est, $per, $est]);
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ahora, filtro extra para PRACTICA PREPROFESIONAL según créditos aprobados
        // Consulta créditos aprobados solo si hay posibilidad de que salga el curso (optimización)
        $tienePractica = false;
        foreach ($cursos as $c) {
            if (strtoupper(trim($c['codigo'])) === '131B10081') {
                $tienePractica = true;
                break;
            }
        }

        if ($tienePractica) {
            $sqlCred = "
                SELECT SUM(c.creditos) AS creditos_aprobados
                FROM progreso p
                JOIN cursos c ON p.curso_id = c.id
                WHERE p.estudiante_id = :est
                  AND p.estado = 'Cumplido'
            ";
            $stCred = $this->pdo->prepare($sqlCred);
            $stCred->execute([':est' => $est]);
            $creditosAprobados = (int) $stCred->fetchColumn();
        } else {
            $creditosAprobados = 0; // Da igual
        }

        // Devuelve todos menos práctica preprofesional si no tiene 180 créditos aprobados
        $cursosFiltrados = array_filter($cursos, function($curso) use ($creditosAprobados) {
            if (strtoupper(trim($curso['codigo'])) === '131B10081') {
                return $creditosAprobados >= 180;
            }
            return true;
        });

        return array_values($cursosFiltrados);
    }
}
