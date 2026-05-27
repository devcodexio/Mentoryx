<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware {
    public function handle(Request $request, Response $response) {
        Session::start();
        $admin = Session::get('admin');
        $path = $request->getPath();

        if ($path === '/admin/login') {
            if ($admin) {
                $response->redirect('/admin/dashboard');
            }
        } else {
            if (!$admin) {
                // If it is an Ajax request, return JSON error instead of redirection
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    $response->json(['success' => false, 'message' => 'Sesión expirada.'], 401);
                }
                $response->redirect('/admin/login');
            }
        }
    }
}
