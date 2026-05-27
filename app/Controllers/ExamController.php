<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Category;
use App\Models\Question;
use App\Models\Alternative;
use App\Models\Result;

class ExamController {
    public function start(Request $request, Response $response, $categoryId) {
        $categoryModel = new Category();
        $category = $categoryModel->find($categoryId);
        
        if (!$category) {
            Session::setFlash('error', 'La categoría especificada no existe.');
            $response->redirect('/');
        }

        $questionModel = new Question();
        $questions = $questionModel->findByCategory($categoryId);

        if (count($questions) === 0) {
            Session::setFlash('error', 'Esta categoría no tiene preguntas registradas.');
            $response->redirect('/');
        }

        $alternativeModel = new Alternative();
        foreach ($questions as &$q) {
            $q['alternativas'] = $alternativeModel->findByQuestion($q['id']);
        }

        $response->render('exam', [
            'category' => $category,
            'questions' => $questions,
            'title' => 'Examen: ' . $category['nombre']
        ]);
    }

    public function submit(Request $request, Response $response) {
        $categoryId = $request->post('categoria_id');
        $usuarioNombre = trim($request->post('usuario_nombre') ?: 'Estudiante Anónimo');
        $respuestas = $request->post('respuestas') ?: []; // array [pregunta_id => alternativa_id]

        $categoryModel = new Category();
        $category = $categoryModel->find($categoryId);
        if (!$category) {
            Session::setFlash('error', 'Categoría inválida.');
            $response->redirect('/');
        }

        $questionModel = new Question();
        $questions = $questionModel->findByCategory($categoryId);

        $alternativeModel = new Alternative();
        
        $correctas = 0;
        $incorrectas = 0;
        $totalPreguntas = count($questions);
        $puntaje = 0.0;

        // Process answers — sum puntaje for each correct answer
        foreach ($questions as $q) {
            $questionId = $q['id'];
            $selectedAltId = $respuestas[$questionId] ?? null;

            // Get correct alternative
            $alternatives = $alternativeModel->findByQuestion($questionId);
            $correctAltId = null;
            foreach ($alternatives as $alt) {
                if ($alt['es_correcta'] == 1) {
                    $correctAltId = $alt['id'];
                    break;
                }
            }

            if ($selectedAltId !== null && $selectedAltId == $correctAltId) {
                $correctas++;
                $puntaje += (float) ($q['puntaje'] ?? 1.0);
            } else {
                $incorrectas++;
            }
        }

        $puntaje = round($puntaje, 2);

        $resultModel = new Result();
        $resultId = $resultModel->create([
            'categoria_id' => $categoryId,
            'usuario_nombre' => $usuarioNombre,
            'puntaje' => $puntaje,
            'correctas' => $correctas,
            'incorrectas' => $incorrectas,
            'total_preguntas' => $totalPreguntas
        ]);

        // Save selection details in session for displaying the results details
        Session::set("result_{$resultId}_choices", $respuestas);

        $response->redirect("/examen/resultados/{$resultId}");
    }

    public function results(Request $request, Response $response, $resultId) {
        $resultModel = new Result();
        $result = $resultModel->findById($resultId);

        if (!$result) {
            Session::setFlash('error', 'El resultado especificado no existe.');
            $response->redirect('/');
            return;
        }

        $questionModel = new Question();
        $questions = $questionModel->findByCategory($result['categoria_id']);

        $alternativeModel = new Alternative();
        foreach ($questions as &$q) {
            $q['alternativas'] = $alternativeModel->findByQuestion($q['id']);
        }

        // Retrieve user choices from session
        $choices = Session::get("result_{$resultId}_choices") ?: [];

        $response->render('results', [
            'result' => $result,
            'questions' => $questions,
            'choices' => $choices,
            'title' => 'Resultados de Examen'
        ]);
    }
}
