<?php

if (!function_exists('url')) {
    function url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        return $protocol . $host . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset($path = '') {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = sanitize($value);
            }
            return $sanitized;
        }
        return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = \App\Core\Session::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('old')) {
    function old($key, $default = '') {
        $oldInputs = \App\Core\Session::get('old_inputs');
        return $oldInputs[$key] ?? $default;
    }
}

if (!function_exists('save_old_input')) {
    function save_old_input($data) {
        \App\Core\Session::set('old_inputs', $data);
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input() {
        \App\Core\Session::remove('old_inputs');
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        static $settings = null;
        if ($settings === null) {
            try {
                $db = \App\Core\Database::getConnection();
                $stmt = $db->query("SELECT clave, valor FROM configuracion");
                $settings = [];
                while ($row = $stmt->fetch()) {
                    $settings[$row['clave']] = $row['valor'];
                }
            } catch (\Exception $e) {
                $settings = [];
            }
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('getTimeAgo')) {
    function getTimeAgo($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $w = floor($diff->d / 7);
        $d = $diff->d - ($w * 7);

        $parts = array(
            'y' => $diff->y,
            'm' => $diff->m,
            'w' => $w,
            'd' => $d,
            'h' => $diff->h,
            'i' => $diff->i,
            's' => $diff->s,
        );

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        foreach ($string as $k => &$v) {
            if ($parts[$k]) {
                $v = $parts[$k] . ' ' . $v . ($parts[$k] > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? 'Hace ' . implode(', ', $string) : 'Hace un momento';
    }
}
