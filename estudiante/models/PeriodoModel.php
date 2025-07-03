<?php
// estudiante/models/PeriodoModel.php
declare(strict_types=1);

/**
 * Modelo de periodos académicos
 *
 * Tablas implicadas:
 *   • periodos        (id, anio, periodo,
 *                      inicio_envio_solicitudes,
 *                      fin_envio_solicitudes,
 *                      inicio_asignacion_docentes,
 *                      fin_asignacion_docentes,
 *                      estado)
 *   • resoluciones    (periodo_id, documento BLOB, fecha_resolucion)
 */
class PeriodoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Asegurar zona horaria correcta
        date_default_timezone_set('America/Lima');
    }

    /**
     * Devuelve el periodo activo solo si AHORA está
     * entre inicio de envío y fin de apertura.
     */
    public function getActivo(): ?array
    {
        $sql = "
            SELECT *
              FROM periodos
             WHERE estado = 'activo'
               AND NOW() BETWEEN inicio_envio_solicitudes
                              AND fin_asignacion_docentes
             LIMIT 1
        ";
        $stmt = $this->pdo->query($sql);
        $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
        return $periodo ?: null;
    }

    /**
     * True si AHORA está entre inicio_envio_solicitudes y fin_envio_solicitudes.
     */
    public function enviosHabilitados(array $periodo): bool
    {
        $now    = new DateTimeImmutable('now');
        $inicio = new DateTimeImmutable($periodo['inicio_envio_solicitudes']);
        $fin    = new DateTimeImmutable($periodo['fin_envio_solicitudes']);
        return $now >= $inicio && $now <= $fin;
    }

    /**
     * True si AHORA está entre inicio_asignacion_docentes y fin_asignacion_docentes.
     */
    public function aperturaHabilitada(array $periodo): bool
    {
        $now    = new DateTimeImmutable('now');
        $inicio = new DateTimeImmutable($periodo['inicio_asignacion_docentes']);
        $fin    = new DateTimeImmutable($periodo['fin_asignacion_docentes']);
        return $now >= $inicio && $now <= $fin;
    }

    /**
     * Recupera el último documento de resolución (PDF o similar) si existe.
     */
    public function getResolucion(int $periodoId): ?string
    {
        $sql = "
            SELECT documento
              FROM resoluciones
             WHERE periodo_id = ?
          ORDER BY fecha_resolucion DESC
             LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$periodoId]);
        $bin = $stmt->fetchColumn();
        return $bin ?: null;
    }
}
