<?php
/**
 * Modelo de Docentes.
 * Permite la gestión de los datos de docentes, incluyendo operaciones de registro, consulta,
 * actualización y eliminación de registros en la base de datos institucional.
 *
 * Autor: ASI-GRUPO 5
 * Año: 2025
 */

 
// admin/models/DocenteModel.php

class DocenteModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM docentes ORDER BY apellido_paterno, apellido_materno");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM docentes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function crear(array $data): bool {
        try {
            $sql = "INSERT INTO docentes (nombres, apellido_paterno, apellido_materno, dni, tipo)
                    VALUES (:nombres, :apellido_paterno, :apellido_materno, :dni, :tipo)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombres'           => $data['nombres'],
                ':apellido_paterno' => $data['apellido_paterno'],
                ':apellido_materno' => $data['apellido_materno'],
                ':dni'              => $data['dni'],
                ':tipo'             => $data['tipo'],
            ]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new Exception('Ya existe un docente con ese DNI.');
            }
            throw $e;
        }
    }


    public function actualizar(array $data): void {
        $sql = "UPDATE docentes
                SET nombres = :nombres,
                    apellido_paterno = :ap_paterno,
                    apellido_materno = :ap_materno,
                    dni = :dni,
                    tipo = :tipo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'          => $data['id'],
            ':nombres'     => $data['nombres'],
            ':ap_paterno'  => $data['apellido_paterno'],
            ':ap_materno'  => $data['apellido_materno'],
            ':dni'         => $data['dni'],
            ':tipo'        => $data['tipo'],
        ]);
    }

    public function eliminar(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM docentes WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function existeDni(string $dni): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM docentes WHERE dni = ?");
        $stmt->execute([$dni]);
        return $stmt->fetchColumn() > 0;
    }
}
