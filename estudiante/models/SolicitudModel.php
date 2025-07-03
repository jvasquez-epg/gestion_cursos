<?php
// estudiante/models/SolicitudModel.php
declare(strict_types=1);

/**
 * Gestiona solicitudes del estudiante:
 *   – CRUD de solicitudes
 *   – Resumen por periodo
 *   – Descarga de PDFs y ZIP
 */
class SolicitudModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*─────────────────────────────
      Búsqueda simple por periodo
    ─────────────────────────────*/
    public function byEstudiante(int $est, int $per): array
    {
        $stmt = $this->pdo->prepare("
            SELECT  s.id,
                    s.curso_id,
                    c.codigo,
                    c.nombre,
                    c.ciclo,
                    c.creditos,
                    s.fecha_solicitud
            FROM solicitudes s
            JOIN cursos c ON c.id = s.curso_id
            WHERE s.estudiante_id = ? AND s.periodo_id = ?
            ORDER BY s.fecha_solicitud DESC
        ");
        $stmt->execute([$est, $per]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*─────────────────────────────
      Obtener PDF puntual
    ─────────────────────────────*/
    public function getDocumento(int $solId, int $est): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT documento
            FROM solicitudes
            WHERE id = ? AND estudiante_id = ?
            LIMIT 1
        ");
        $stmt->execute([$solId, $est]);
        $bin = $stmt->fetchColumn();
        return $bin ?: null;
    }

    /*─────────────────────────────
      Documentos de un periodo
    ─────────────────────────────*/
    public function documentosPorPeriodo(int $est, int $per): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.codigo, s.documento
            FROM solicitudes s
            JOIN cursos c ON c.id = s.curso_id
            WHERE s.estudiante_id = ? AND s.periodo_id = ?
        ");
        $stmt->execute([$est, $per]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*─────────────────────────────
      Resumen → historial por periodo
    ─────────────────────────────*/
    public function resumenPorPeriodo(int $est): array
    {
        $stmt = $this->pdo->prepare("
            SELECT  p.id            AS periodo_id,
                    CONCAT(p.anio,'-',p.periodo) AS periodo_label,
                    COUNT(*)        AS total
            FROM solicitudes s
            JOIN periodos p ON p.id = s.periodo_id
            WHERE s.estudiante_id = ?
            GROUP BY p.id, p.anio, p.periodo
            ORDER BY p.anio DESC, p.periodo DESC
        ");
        $stmt->execute([$est]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*─────────────────────────────
      Crear nueva solicitud
    ─────────────────────────────*/
    public function crear(
        int $est, int $curso, int $per, int $maxCursos, int $maxCred
    ): void
    {
        $this->pdo->beginTransaction();
        try {
            /* Duplicado */
            $stmt = $this->pdo->prepare("
                SELECT id FROM solicitudes
                WHERE estudiante_id=? AND curso_id=? AND periodo_id=? LIMIT 1
            ");
            $stmt->execute([$est, $curso, $per]);
            if ($stmt->fetch()) throw new Exception('Duplicado.');

            /* Créditos curso */
            $stmt = $this->pdo->prepare("SELECT creditos FROM cursos WHERE id=?");
            $stmt->execute([$curso]);
            $credCur = (int) $stmt->fetchColumn();
            if (!$credCur) throw new Exception('Curso no encontrado.');

            /* Acumulado */
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) total, COALESCE(SUM(c.creditos),0) cred
                FROM solicitudes s
                JOIN cursos c ON c.id=s.curso_id
                WHERE s.estudiante_id=? AND s.periodo_id=?
            ");
            $stmt->execute([$est,$per]);
            $acc = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($acc['total'] + 1 > $maxCursos)    throw new Exception('Máx cursos.');
            if ($acc['cred']  + $credCur > $maxCred) throw new Exception('Máx créditos.');

            /* Inserta con documento vacío (se firmará después) */
            $stmt = $this->pdo->prepare("
                INSERT INTO solicitudes
                    (estudiante_id,curso_id,periodo_id,documento,fecha_solicitud)
                VALUES (?,?,?, '', NOW())
            ");
            $stmt->execute([$est,$curso,$per]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /*─────────────────────────────
      Elimina solicitud (con verif.)
    ─────────────────────────────*/
    public function eliminar(int $solId, int $est): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM solicitudes
            WHERE id=? AND estudiante_id=?
        ");
        $stmt->execute([$solId, $est]);
        if (!$stmt->rowCount()) {
            throw new Exception('No encontrada / no autorizada.');
        }
    }

  /*──────────────────────────────────────────────────────────
  Crea la solicitud con PDF firmado vía QR
        ──────────────────────────────────────────────────────────*/
        public function crearConDocumento(
            int $est, int $curso, int $per, int $maxCursos, int $maxCred
        ): void {
            $this->pdo->beginTransaction();
            try {
                /* A) Duplicado */
                $q = $this->pdo->prepare(
                    "SELECT id FROM solicitudes
                    WHERE estudiante_id=? AND curso_id=? AND periodo_id=? LIMIT 1"
                );
                $q->execute([$est, $curso, $per]);
                if ($q->fetch()) {
                    throw new Exception('El curso ya fue solicitado.');
                }

                /* B) Datos del curso */
                $q = $this->pdo->prepare(
                    "SELECT codigo, nombre, creditos FROM cursos WHERE id=? LIMIT 1"
                );
                $q->execute([$curso]);
                $cRow = $q->fetch(PDO::FETCH_ASSOC)
                    ?: throw new Exception('Curso no encontrado');
                $credCurso = (int)$cRow['creditos'];

                /* C) Acumulado y validaciones */
                $q = $this->pdo->prepare(
                    "SELECT COUNT(*) tot, COALESCE(SUM(c.creditos),0) cred
                    FROM solicitudes s
                    JOIN cursos c ON c.id=s.curso_id
                    WHERE s.estudiante_id=? AND s.periodo_id=?"
                );
                $q->execute([$est, $per]);
                ['tot' => $tot, 'cred' => $credAct] = $q->fetch(PDO::FETCH_ASSOC);

                if ($tot + 1 > $maxCursos)  throw new Exception('Máximo de cursos.');
                if ($credAct + $credCurso > $maxCred) throw new Exception('Máximo de créditos.');

                /* D) Datos estudiante */
                $q = $this->pdo->prepare(
                    "SELECT  u.nombres, u.apellido_paterno, u.apellido_materno,
                            u.dni, u.correo, u.telefono,
                            e.codigo_universitario, e.firma_hash
                    FROM  usuarios u
                    JOIN  estudiantes e ON e.id = u.id
                    WHERE  u.id=? LIMIT 1"
                );
                $q->execute([$est]);
                $u = $q->fetch(PDO::FETCH_ASSOC) ?: throw new Exception('Alumno no hallado');

                /* E) Generar PDF */
                require_once __DIR__ . '/../services/DocumentGenerator.php';
                $docGen = new DocumentGenerator();

                $pdfBin = $docGen->generar([
                    'CODIGO'     => $u['codigo_universitario'],
                    'NOMBRE'     => $u['nombres'].' '.$u['apellido_paterno'].' '.$u['apellido_materno'],
                    'DNI'        => $u['dni'],
                    'CORREO'     => $u['correo'],
                    'CELULAR'    => $u['telefono'] ?? '—',
                    'ASIGNATURA' => $cRow['nombre'],
                    'COD_CURSO'  => $cRow['codigo'],
                    'FECHA'      => date('d/m/Y'),
                    'HORA'       => date('H:i'),
                    'SEMESTRE'   => date('Y').'-'.$per,
                    'FIRMA_HASH' => $u['firma_hash']          // va dentro del QR
                ]);


                /* F) Insertar solicitud */
                $q = $this->pdo->prepare(
                    "INSERT INTO solicitudes
                        (estudiante_id,curso_id,periodo_id,documento,fecha_solicitud)
                    VALUES (?,?,?,?,NOW())"
                );
                $q->execute([$est, $curso, $per, $pdfBin]);

                $this->pdo->commit();
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

    public function filaConDocumento(int $solId, int $est): ?array
    {
        $q = $this->pdo->prepare("
            SELECT  s.documento,
                    e.codigo_universitario AS codigo_u,
                    c.codigo               AS curso_codigo,
                    c.nombre               AS curso_nombre
            FROM solicitudes s
            JOIN cursos      c ON c.id = s.curso_id
            JOIN estudiantes e ON e.id = s.estudiante_id
            WHERE s.id = ? AND s.estudiante_id = ?
            LIMIT 1
        ");
        $q->execute([$solId, $est]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

}
