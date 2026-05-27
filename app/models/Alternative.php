<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Alternative {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByQuestion($questionId) {
        $stmt = $this->db->prepare("SELECT * FROM alternativas WHERE pregunta_id = :pregunta_id ORDER BY id ASC");
        $stmt->execute(['pregunta_id' => $questionId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO alternativas (pregunta_id, alternativa, es_correcta) 
                                    VALUES (:pregunta_id, :alternativa, :es_correcta)");
        return $stmt->execute([
            'pregunta_id' => $data['pregunta_id'],
            'alternativa' => $data['alternativa'],
            'es_correcta' => $data['es_correcta'] ? 1 : 0
        ]);
    }

    public function deleteByQuestion($questionId) {
        $stmt = $this->db->prepare("DELETE FROM alternativas WHERE pregunta_id = :pregunta_id");
        return $stmt->execute(['pregunta_id' => $questionId]);
    }

    public function saveAlternatives($questionId, array $alternatives) {
        $this->db->beginTransaction();
        try {
            // Delete old ones
            $this->deleteByQuestion($questionId);

            // Insert new ones
            foreach ($alternatives as $alt) {
                $this->create([
                    'pregunta_id' => $questionId,
                    'alternativa' => $alt['alternativa'],
                    'es_correcta' => isset($alt['es_correcta']) && $alt['es_correcta']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
