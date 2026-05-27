<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Result {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT r.*, c.nombre as categoria_nombre 
                                    FROM resultados r 
                                    LEFT JOIN categorias c ON r.categoria_id = c.id 
                                    WHERE r.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO resultados (categoria_id, usuario_nombre, puntaje, correctas, incorrectas, total_preguntas) 
                                    VALUES (:categoria_id, :usuario_nombre, :puntaje, :correctas, :incorrectas, :total_preguntas)");
        $stmt->execute([
            'categoria_id' => $data['categoria_id'],
            'usuario_nombre' => $data['usuario_nombre'],
            'puntaje' => $data['puntaje'],
            'correctas' => $data['correctas'],
            'incorrectas' => $data['incorrectas'],
            'total_preguntas' => $data['total_preguntas']
        ]);
        return $this->db->lastInsertId();
    }

    public function getLatest($limit = 5) {
        $stmt = $this->db->prepare("SELECT r.*, c.nombre as categoria_nombre 
                                    FROM resultados r 
                                    LEFT JOIN categorias c ON r.categoria_id = c.id 
                                    ORDER BY r.id DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Add created_at if not present (use current time for display)
        foreach ($results as &$result) {
            if (!isset($result['created_at'])) {
                $result['created_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $results;
    }

    public function getStats() {
        $stmt = $this->db->query("SELECT 
                                    COUNT(*) as total_intentos, 
                                    AVG(puntaje) as promedio_puntaje,
                                    SUM(correctas) as total_correctas,
                                    SUM(incorrectas) as total_incorrectas
                                  FROM resultados");
        return $stmt->fetch();
    }
}
