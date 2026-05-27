<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;

class CategoryController {
    protected $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    public function index(Request $request, Response $response) {
        $categories = $this->categoryModel->all();
        $response->render('admin/categories/index', [
            'categories' => $categories,
            'title' => 'Gestión de Categorías'
        ]);
    }

    public function create(Request $request, Response $response) {
        if ($request->getMethod() === 'POST') {
            $nombre = trim($request->post('nombre'));
            $descripcion = trim($request->post('descripcion'));
            $puntajeMaximo = (int) $request->post('puntaje_maximo') ?: 20;

            if (empty($nombre)) {
                save_old_input($request->all());
                Session::setFlash('error', 'El nombre de la categoría es requerido.');
                $response->redirect('/admin/categorias/crear');
            }

            $this->categoryModel->create([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'puntaje_maximo' => $puntajeMaximo
            ]);

            clear_old_input();
            Session::setFlash('success', 'Categoría creada con éxito.');
            $response->redirect('/admin/categorias');
        } else {
            $response->render('admin/categories/create', [
                'title' => 'Crear Categoría'
            ]);
        }
    }

    public function edit(Request $request, Response $response, $id) {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            Session::setFlash('error', 'Categoría no encontrada.');
            $response->redirect('/admin/categorias');
        }

        if ($request->getMethod() === 'POST') {
            $nombre = trim($request->post('nombre'));
            $descripcion = trim($request->post('descripcion'));
            $puntajeMaximo = (int) $request->post('puntaje_maximo') ?: 20;

            if (empty($nombre)) {
                save_old_input($request->all());
                Session::setFlash('error', 'El nombre de la categoría es requerido.');
                $response->redirect("/admin/categorias/editar/{$id}");
            }

            $this->categoryModel->update($id, [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'puntaje_maximo' => $puntajeMaximo
            ]);

            clear_old_input();
            Session::setFlash('success', 'Categoría actualizada con éxito.');
            $response->redirect('/admin/categorias');
        } else {
            $response->render('admin/categories/edit', [
                'category' => $category,
                'title' => 'Editar Categoría'
            ]);
        }
    }

    public function delete(Request $request, Response $response, $id) {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return $response->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
        }

        $this->categoryModel->delete($id);
        return $response->json(['success' => true, 'message' => 'Categoría eliminada con éxito.']);
    }
}
