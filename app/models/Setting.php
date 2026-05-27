<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Setting {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all() {
        $stmt = $this->db->query("SELECT clave, valor FROM configuracion");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['clave']] = $row['valor'];
        }
        return $settings;
    }

    public function update($clave, $valor) {
        $stmt = $this->db->prepare("
            INSERT INTO configuracion (clave, valor) 
            VALUES (:clave, :valor) 
            ON DUPLICATE KEY UPDATE valor = :valor_update
        ");
        return $stmt->execute([
            'clave' => $clave,
            'valor' => $valor,
            'valor_update' => $valor
        ]);
    }
}
