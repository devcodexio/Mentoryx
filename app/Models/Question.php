<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Question {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function paginate($page, $limit, $search = '', $categoryId = null) {
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "p.pregunta LIKE :search";
            $params['search'] = "%{$search}%";
        }

        if (!empty($categoryId)) {
            $where[] = "p.categoria_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        $whereSql = '';
        if (count($where) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $where);
        }

        // Count total
        $countQuery = "SELECT COUNT(*) as total FROM preguntas p {$whereSql}";
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch()['total'] ?? 0;
        $pages = ceil($total / $limit);

        // Fetch data
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM preguntas p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  {$whereSql} 
                  ORDER BY p.id DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->execute();
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'limit' => $limit
        ];
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.nombre as categoria_nombre 
                                    FROM preguntas p 
                                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                                    WHERE p.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM preguntas WHERE categoria_id = :category_id ORDER BY id ASC");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO preguntas (categoria_id, pregunta, imagen, imagen_resolucion, resolucion, puntaje) 
                                    VALUES (:categoria_id, :pregunta, :imagen, :imagen_resolucion, :resolucion, :puntaje)");
        $stmt->execute([
            'categoria_id' => $data['categoria_id'],
            'pregunta' => $data['pregunta'],
            'imagen' => $data['imagen'] ?? null,
            'imagen_resolucion' => $data['imagen_resolucion'] ?? null,
            'resolucion' => $data['resolucion'] ?? null,
            'puntaje' => $data['puntaje'] ?? 1.00
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $query = "UPDATE preguntas SET 
                  categoria_id = :categoria_id, 
                  pregunta = :pregunta, 
                  resolucion = :resolucion, 
                  puntaje = :puntaje, ";
        
        $params = [
            'id' => $id,
            'categoria_id' => $data['categoria_id'],
            'pregunta' => $data['pregunta'],
            'resolucion' => $data['resolucion'] ?? null,
            'puntaje' => $data['puntaje'] ?? 1.00
        ];

        if (array_key_exists('imagen', $data)) {
            $query .= "imagen = :imagen, ";
            $params['imagen'] = $data['imagen'];
        }

        if (array_key_exists('imagen_resolucion', $data)) {
            $query .= "imagen_resolucion = :imagen_resolucion, ";
            $params['imagen_resolucion'] = $data['imagen_resolucion'];
        }

        $query = rtrim($query, ', ') . " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        // Delete image first
        $question = $this->find($id);
        if ($question) {
            if (!empty($question['imagen'])) {
                $imagePath = dirname(dirname(__DIR__)) . '/public/uploads/' . $question['imagen'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            if (!empty($question['imagen_resolucion'])) {
                $imageResPath = dirname(dirname(__DIR__)) . '/public/uploads/' . $question['imagen_resolucion'];
                if (file_exists($imageResPath)) {
                    @unlink($imageResPath);
                }
            }
        }

        $stmt = $this->db->prepare("DELETE FROM preguntas WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM preguntas")->fetchColumn();
    }
}
