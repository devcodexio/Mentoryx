<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Admin {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function login($username, $password) {
        $admin = $this->findByUsername($username);
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, username, nombre, foto, created_at FROM admins WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateProfile($id, $nombre, $foto = null) {
        if ($foto !== null) {
            $stmt = $this->db->prepare("UPDATE admins SET nombre = :nombre, foto = :foto WHERE id = :id");
            return $stmt->execute(['nombre' => $nombre, 'foto' => $foto, 'id' => $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE admins SET nombre = :nombre WHERE id = :id");
            return $stmt->execute(['nombre' => $nombre, 'id' => $id]);
        }
    }

    public function updatePassword($id, $password) {
        $stmt = $this->db->prepare("UPDATE admins SET password = :password WHERE id = :id");
        return $stmt->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    }
}
