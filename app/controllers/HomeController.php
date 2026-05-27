<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Category;

class HomeController {
    public function index(Request $request, Response $response) {
        $categoryModel = new Category();
        $categories = $categoryModel->all();
        
        $response->render('home', [
            'categories' => $categories,
            'title' => 'Simulador de Exámenes Online Profesional'
        ]);
    }
}
