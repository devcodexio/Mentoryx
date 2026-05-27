<?php

namespace App\Core;

class Request {
    public function getMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Remove query string
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        
        // Remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = substr($uri, 0, -1);
        }

        // Get subfolder if applicable (when served with php -S, it's root)
        return $uri ?: '/';
    }

    public function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public function file($key) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE) {
            return $_FILES[$key];
        }
        return null;
    }

    public function all() {
        $body = [];
        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_DEFAULT);
            }
        }
        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_DEFAULT);
            }
        }
        return $body;
    }
}
