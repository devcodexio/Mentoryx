<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;
use App\Models\Question;
use App\Models\Alternative;

class QuestionController {
    protected $questionModel;
    protected $categoryModel;
    protected $alternativeModel;

    public function __construct() {
        $this->questionModel = new Question();
        $this->categoryModel = new Category();
        $this->alternativeModel = new Alternative();
    }

    public function index(Request $request, Response $response) {
        $page = (int)$request->get('page', 1);
        $search = trim((string)$request->get('q', ''));
        $categoryId = $request->get('categoria_id');
        $limit = 8;

        $results = $this->questionModel->paginate($page, $limit, $search, $categoryId);
        $categories = $this->categoryModel->all();

        $response->render('admin/questions/index', [
            'questions' => $results['data'],
            'total' => $results['total'],
            'pages' => $results['pages'],
            'currentPage' => $results['current_page'],
            'search' => $search,
            'selectedCategoryId' => $categoryId,
            'categories' => $categories,
            'title' => 'Gestión de Preguntas'
        ]);
    }

    public function create(Request $request, Response $response) {
        $categories = $this->categoryModel->all();

        if ($request->getMethod() === 'POST') {
            $categoriaId = $request->post('categoria_id');
            $preguntaTxt = trim($request->post('pregunta'));
            $resolucion = trim($request->post('resolucion'));
            $altsRaw = $request->post('alternativas') ?: [];
            $correctaIndex = $request->post('correcta'); // 0-indexed index of correct alt

            // Validations
            if (empty($categoriaId) || empty($preguntaTxt)) {
                save_old_input($request->all());
                Session::setFlash('error', 'Por favor, complete todos los campos obligatorios.');
                $response->redirect('/admin/preguntas/crear');
            }

            if (count($altsRaw) < 4) {
                save_old_input($request->all());
                Session::setFlash('error', 'Debe ingresar al menos 4 alternativas.');
                $response->redirect('/admin/preguntas/crear');
            }

            if ($correctaIndex === null || !isset($altsRaw[$correctaIndex])) {
                save_old_input($request->all());
                Session::setFlash('error', 'Debe marcar cuál de las alternativas es la correcta.');
                $response->redirect('/admin/preguntas/crear');
            }

            // Image upload for pregunta
            $imageName = null;
            $imageFile = $request->file('imagen');
            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/';
            
            if ($imageFile) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'Formato de imagen inválido. Solo JPG, JPEG, PNG o GIF.');
                    $response->redirect('/admin/preguntas/crear');
                }
                
                // Max size 2MB
                if ($imageFile['size'] > 2 * 1024 * 1024) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'La imagen de la pregunta no debe superar los 2MB.');
                    $response->redirect('/admin/preguntas/crear');
                }

                $imageName = uniqid('img_', true) . '.' . $ext;
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($imageFile['tmp_name'], $uploadDir . $imageName);
            }

            // Image upload for resolucion
            $imageResName = null;
            $imageResFile = $request->file('imagen_resolucion');
            if ($imageResFile) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($imageResFile['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'Formato de imagen de resolución inválido.');
                    $response->redirect('/admin/preguntas/crear');
                }
                
                if ($imageResFile['size'] > 2 * 1024 * 1024) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'La imagen de resolución no debe superar los 2MB.');
                    $response->redirect('/admin/preguntas/crear');
                }

                $imageResName = uniqid('img_res_', true) . '.' . $ext;
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($imageResFile['tmp_name'], $uploadDir . $imageResName);
            }

            // Create question
            $puntaje = (float) ($request->post('puntaje') ?: 1);
            $questionId = $this->questionModel->create([
                'categoria_id' => $categoriaId,
                'pregunta' => $preguntaTxt,
                'imagen' => $imageName,
                'imagen_resolucion' => $imageResName,
                'resolucion' => $resolucion,
                'puntaje' => $puntaje
            ]);

            // Save alternatives
            $alternativesData = [];
            foreach ($altsRaw as $index => $altText) {
                if (trim($altText) !== '') {
                    $alternativesData[] = [
                        'alternativa' => trim($altText),
                        'es_correcta' => ($index == $correctaIndex)
                    ];
                }
            }

            $this->alternativeModel->saveAlternatives($questionId, $alternativesData);

            clear_old_input();
            Session::setFlash('success', 'Pregunta creada con éxito.');
            $response->redirect('/admin/preguntas');
        } else {
            $response->render('admin/questions/create', [
                'categories' => $categories,
                'title' => 'Crear Pregunta'
            ]);
        }
    }

    public function edit(Request $request, Response $response, $id) {
        $question = $this->questionModel->find($id);
        if (!$question) {
            Session::setFlash('error', 'Pregunta no encontrada.');
            $response->redirect('/admin/preguntas');
        }

        $categories = $this->categoryModel->all();
        $alternatives = $this->alternativeModel->findByQuestion($id);

        if ($request->getMethod() === 'POST') {
            $categoriaId = $request->post('categoria_id');
            $preguntaTxt = trim($request->post('pregunta'));
            $resolucion = trim($request->post('resolucion'));
            $altsRaw = $request->post('alternativas') ?: [];
            $correctaIndex = $request->post('correcta'); // index of the correct alternative

            // Validations
            if (empty($categoriaId) || empty($preguntaTxt)) {
                save_old_input($request->all());
                Session::setFlash('error', 'Por favor, complete todos los campos obligatorios.');
                $response->redirect("/admin/preguntas/editar/{$id}");
            }

            if (count($altsRaw) < 4) {
                save_old_input($request->all());
                Session::setFlash('error', 'Debe ingresar al menos 4 alternativas.');
                $response->redirect("/admin/preguntas/editar/{$id}");
            }

            if ($correctaIndex === null || !isset($altsRaw[$correctaIndex])) {
                save_old_input($request->all());
                Session::setFlash('error', 'Debe marcar cuál de las alternativas es la correcta.');
                $response->redirect("/admin/preguntas/editar/{$id}");
            }

            // Image upload handling
            $dataToUpdate = [
                'categoria_id' => $categoriaId,
                'pregunta' => $preguntaTxt,
                'resolucion' => $resolucion
            ];

            // If user checked remove_imagen
            if ($request->post('eliminar_imagen') == '1') {
                if (!empty($question['imagen'])) {
                    $oldImagePath = dirname(dirname(__DIR__)) . '/public/uploads/' . $question['imagen'];
                    if (file_exists($oldImagePath)) @unlink($oldImagePath);
                }
                $dataToUpdate['imagen'] = null;
            }

            // If user checked remove_imagen_resolucion
            if ($request->post('eliminar_imagen_resolucion') == '1') {
                if (!empty($question['imagen_resolucion'])) {
                    $oldImageResPath = dirname(dirname(__DIR__)) . '/public/uploads/' . $question['imagen_resolucion'];
                    if (file_exists($oldImageResPath)) @unlink($oldImageResPath);
                }
                $dataToUpdate['imagen_resolucion'] = null;
            }

            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/';

            $imageFile = $request->file('imagen');
            if ($imageFile) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'Formato de imagen inválido. Solo JPG, JPEG, PNG o GIF.');
                    $response->redirect("/admin/preguntas/editar/{$id}");
                }

                if ($imageFile['size'] > 2 * 1024 * 1024) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'La imagen no debe superar los 2MB.');
                    $response->redirect("/admin/preguntas/editar/{$id}");
                }

                // Delete old image if exists
                if (!empty($question['imagen'])) {
                    $oldImagePath = $uploadDir . $question['imagen'];
                    if (file_exists($oldImagePath)) @unlink($oldImagePath);
                }

                $imageName = uniqid('img_', true) . '.' . $ext;
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($imageFile['tmp_name'], $uploadDir . $imageName);
                $dataToUpdate['imagen'] = $imageName;
            }

            $imageResFile = $request->file('imagen_resolucion');
            if ($imageResFile) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($imageResFile['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'Formato de imagen de resolución inválido.');
                    $response->redirect("/admin/preguntas/editar/{$id}");
                }

                if ($imageResFile['size'] > 2 * 1024 * 1024) {
                    save_old_input($request->all());
                    Session::setFlash('error', 'La imagen de resolución no debe superar los 2MB.');
                    $response->redirect("/admin/preguntas/editar/{$id}");
                }

                // Delete old image if exists
                if (!empty($question['imagen_resolucion'])) {
                    $oldImageResPath = $uploadDir . $question['imagen_resolucion'];
                    if (file_exists($oldImageResPath)) @unlink($oldImageResPath);
                }

                $imageResName = uniqid('img_res_', true) . '.' . $ext;
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($imageResFile['tmp_name'], $uploadDir . $imageResName);
                $dataToUpdate['imagen_resolucion'] = $imageResName;
            }

            // Update question
            $puntaje = (float) ($request->post('puntaje') ?: 1);
            $dataToUpdate['puntaje'] = $puntaje;
            $this->questionModel->update($id, $dataToUpdate);

            // Re-save alternatives
            $alternativesData = [];
            foreach ($altsRaw as $index => $altText) {
                if (trim($altText) !== '') {
                    $alternativesData[] = [
                        'alternativa' => trim($altText),
                        'es_correcta' => ($index == $correctaIndex)
                    ];
                }
            }

            $this->alternativeModel->saveAlternatives($id, $alternativesData);

            clear_old_input();
            Session::setFlash('success', 'Pregunta actualizada con éxito.');
            $response->redirect('/admin/preguntas');
        } else {
            $response->render('admin/questions/edit', [
                'question' => $question,
                'categories' => $categories,
                'alternatives' => $alternatives,
                'title' => 'Editar Pregunta'
            ]);
        }
    }

    public function delete(Request $request, Response $response, $id) {
        $question = $this->questionModel->find($id);
        if (!$question) {
            return $response->json(['success' => false, 'message' => 'Pregunta no encontrada.'], 404);
        }

        $this->questionModel->delete($id);
        return $response->json(['success' => true, 'message' => 'Pregunta eliminada con éxito.']);
    }
}
