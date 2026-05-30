<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Question;
use App\Models\PdfModel;
use App\Models\Result;

class AdminController {
    public function login(Request $request, Response $response) {
        if ($request->getMethod() === 'POST') {
            $username = trim($request->post('username'));
            $password = trim($request->post('password'));
            
            $adminModel = new Admin();
            $admin = $adminModel->login($username, $password);

            if ($admin) {
                Session::set('admin', [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nombre' => $admin['nombre'] ?? $admin['username'],
                    'foto' => $admin['foto'] ?? null
                ]);
                clear_old_input();
                $response->redirect('/admin/dashboard');
            } else {
                save_old_input($request->all());
                Session::setFlash('error', 'Usuario o contraseña incorrectos.');
                $response->redirect('/admin/login');
            }
        } else {
            $response->render('admin/login', [
                'title' => 'Acceso Panel Administrativo'
            ]);
        }
    }

    public function logout(Request $request, Response $response) {
        Session::remove('admin');
        Session::destroy();
        $response->redirect('/admin/login');
    }

    public function dashboard(Request $request, Response $response) {
        $categoryModel = new Category();
        $questionModel = new Question();
        $pdfModel = new PdfModel();
        $resultModel = new Result();

        $totalCategories = count($categoryModel->all());
        $totalQuestions = $questionModel->countAll();
        $totalPdfs = $pdfModel->countAll();
        $recentResults = $resultModel->getLatest(5);
        $stats = $resultModel->getStats();

        $response->render('admin/dashboard', [
            'totalCategories' => $totalCategories,
            'totalQuestions' => $totalQuestions,
            'totalPdfs' => $totalPdfs,
            'recentResults' => $recentResults,
            'stats' => $stats,
            'title' => 'Panel de Control - Administrador'
        ]);
    }

    public function results(Request $request, Response $response) {
        $resultModel = new Result();
        $allResults = $resultModel->getLatest(100);

        $response->render('admin/results', [
            'results' => $allResults,
            'title' => 'Todos los Resultados - Administrador'
        ]);
    }

    public function profile(Request $request, Response $response) {
        $adminId = Session::get('admin')['id'];
        $adminModel = new Admin();
        $admin = $adminModel->findById($adminId);

        $response->render('admin/profile', [
            'admin' => $admin,
            'title' => 'Mi Perfil'
        ]);
    }

    public function updateProfile(Request $request, Response $response) {
        $adminId = Session::get('admin')['id'];
        $adminModel = new Admin();
        
        $nombre = trim($request->post('nombre'));
        $password = trim($request->post('password'));
        
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filename = 'admin_' . $adminId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $foto = $filename;
                }
            }
        }
        
        $adminModel->updateProfile($adminId, $nombre, $foto);
        
        if (!empty($password)) {
            $adminModel->updatePassword($adminId, $password);
        }
        
        $adminData = Session::get('admin');
        $adminData['nombre'] = $nombre;
        if ($foto) $adminData['foto'] = $foto;
        Session::set('admin', $adminData);
        
        Session::setFlash('success', 'Perfil actualizado correctamente.');
        $response->redirect('/admin/perfil');
    }
}
