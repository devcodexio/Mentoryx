<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PdfModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all() {
        $stmt = $this->db->query("SELECT p.*, c.nombre as categoria_nombre 
                                  FROM pdfs p 
                                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                                  ORDER BY p.id DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM pdfs WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM pdfs WHERE categoria_id = :categoria_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(['categoria_id' => $categoryId]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO pdfs (categoria_id, archivo_pdf) VALUES (:categoria_id, :archivo_pdf)");
        return $stmt->execute([
            'categoria_id' => $data['categoria_id'],
            'archivo_pdf' => $data['archivo_pdf']
        ]);
    }

    public function delete($id) {
        $pdf = $this->find($id);
        if ($pdf && !empty($pdf['archivo_pdf'])) {
            $pdfPath = dirname(dirname(__DIR__)) . '/public/uploads/pdfs/' . $pdf['archivo_pdf'];
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM pdfs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM pdfs")->fetchColumn();
    }
}
