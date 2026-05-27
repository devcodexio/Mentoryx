<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Category {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM categorias ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO categorias (nombre, descripcion, puntaje_maximo) VALUES (:nombre, :descripcion, :puntaje_maximo)");
        return $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'puntaje_maximo' => $data['puntaje_maximo'] ?? 20
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, puntaje_maximo = :puntaje_maximo WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'puntaje_maximo' => $data['puntaje_maximo'] ?? 20
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
