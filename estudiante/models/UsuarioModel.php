<?php
// estudiante/models/UsuarioModel.php

class UsuarioModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    public function actualizarCorreoTelefono(int $id, string $correo, string $telefono): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET correo = ?, telefono = ? WHERE id = ?");
        return $stmt->execute([$correo, $telefono, $id]);
    }

    public function obtenerHashContraseña(int $id): ?string {
        $stmt = $this->pdo->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return $hash ?: null;
    }

    public function actualizarContraseña(int $id, string $nuevaHash): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
        return $stmt->execute([$nuevaHash, $id]);
    }
}
