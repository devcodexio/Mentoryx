<?php

namespace App\Core;

class Response {
    public function status(int $code) {
        http_response_code($code);
        return $this;
    }

    public function redirect(string $url) {
        header("Location: " . $url);
        exit;
    }

    public function json($data, int $code = 200) {
        $this->status($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function render(string $view, array $data = []) {
        // Extract variables to be used in the view template
        extract($data);

        $viewFile = dirname(dirname(__DIR__)) . "/app/views/{$view}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            $this->status(404);
            echo "View [{$view}] not found at: {$viewFile}";
        }
    }
}
