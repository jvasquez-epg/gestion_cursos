<?php
// estudiante/models/MallaModel.php
declare(strict_types=1);

class MallaModel
{
    private PDO $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    /**
     * Devuelve cursos de la malla agrupados por ciclo con sus prerrequisitos.
     * Si el curso es "PRACTICA PREPROFESIONAL", muestra "Aprobar 180 créditos".
     */
    public function cursosPorMalla(int $mallaId): array
    {
        $sql = "
            SELECT 
                c.id,
                c.ciclo,
                c.codigo,
                c.nombre,
                c.creditos,
                COALESCE(
                    GROUP_CONCAT(
                        DISTINCT prc.nombre 
                        ORDER BY prc.nombre SEPARATOR ', '
                    ),
                    '—'
                ) AS prerequisitos
            FROM cursos c
            LEFT JOIN prerrequisitos p 
                ON p.curso_id = c.id
            LEFT JOIN cursos prc        
                ON prc.id = p.prerrequisito_id
            WHERE c.malla_id = :malla
            GROUP BY c.id
            ORDER BY c.ciclo, c.codigo
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([':malla' => $mallaId]);

        $out = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            // Si es el curso "PRACTICA PREPROFESIONAL" (por código), mostrar requisito especial
            if (strtoupper(trim($row['codigo'])) === '131B10081') {
                $row['prerequisitos'] = 'Aprobar 180 créditos';
            }
            $out[$row['ciclo']][] = $row;
        }
        return $out;
    }
}
