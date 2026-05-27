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
                    'username' => $admin['username']
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
        $allResults = $resultModel->getLatest(100); // Get all results

        $response->render('admin/results', [
            'results' => $allResults,
            'title' => 'Todos los Resultados - Administrador'
        ]);
    }
}
