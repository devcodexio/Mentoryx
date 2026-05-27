<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Category;
use App\Models\Question;
use App\Models\Alternative;
use App\Models\PdfModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfController {
    protected $pdfModel;
    protected $categoryModel;
    protected $questionModel;
    protected $alternativeModel;

    public function __construct() {
        $this->pdfModel = new PdfModel();
        $this->categoryModel = new Category();
        $this->questionModel = new Question();
        $this->alternativeModel = new Alternative();
    }

    // List generated PDFs in Admin Panel
    public function index(Request $request, Response $response) {
        $pdfs = $this->pdfModel->all();
        $categories = $this->categoryModel->all();

        $response->render('admin/pdfs/index', [
            'pdfs' => $pdfs,
            'categories' => $categories,
            'title' => 'PDFs Generados'
        ]);
    }

    // Dynamic generation from home page (on-the-fly download)
    public function downloadPublic(Request $request, Response $response) {
        $categoryId = $request->get('id');
        if (empty($categoryId)) {
            Session::setFlash('error', 'Categoría no especificada.');
            $response->redirect('/');
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category) {
            Session::setFlash('error', 'La categoría no existe.');
            $response->redirect('/');
        }

        $questions = $this->questionModel->findByCategory($categoryId);
        if (count($questions) === 0) {
            Session::setFlash('error', 'Esta categoría no tiene preguntas para exportar.');
            $response->redirect('/');
        }

        // Load alternatives & base64 images
        foreach ($questions as &$q) {
            $q['alternativas'] = $this->alternativeModel->findByQuestion($q['id']);
            $q['imagen_base64'] = $this->getImageBase64($q['imagen']);
            $q['imagen_resolucion_base64'] = $this->getImageBase64($q['imagen_resolucion'] ?? null);
        }

        // Generate HTML
        $html = $this->renderPdfHtml($category, $questions);

        // Dompdf configuration
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Examen_Preguntas_' . str_replace(' ', '_', $category['nombre']) . '.pdf';
        
        // Stream download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }

    // Generate & Save PDF from Admin Panel
    public function generateAdmin(Request $request, Response $response) {
        $categoryId = $request->post('categoria_id');
        if (empty($categoryId)) {
            Session::setFlash('error', 'Por favor seleccione una categoría.');
            $response->redirect('/admin/pdfs');
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category) {
            Session::setFlash('error', 'La categoría seleccionada no existe.');
            $response->redirect('/admin/pdfs');
        }

        $questions = $this->questionModel->findByCategory($categoryId);
        if (count($questions) === 0) {
            Session::setFlash('error', 'La categoría no tiene preguntas para generar el PDF.');
            $response->redirect('/admin/pdfs');
        }

        // Load alternatives & base64 images
        foreach ($questions as &$q) {
            $q['alternativas'] = $this->alternativeModel->findByQuestion($q['id']);
            $q['imagen_base64'] = $this->getImageBase64($q['imagen']);
            $q['imagen_resolucion_base64'] = $this->getImageBase64($q['imagen_resolucion'] ?? null);
        }

        // Generate HTML
        $html = $this->renderPdfHtml($category, $questions);

        // Dompdf setup
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        // Save PDF to public/uploads/pdfs/
        $pdfDir = dirname(dirname(__DIR__)) . '/public/uploads/pdfs/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        $safeCategoryName = preg_replace('/[^a-zA-Z0-9]/', '_', $category['nombre']);
        $filename = 'Examen_' . $safeCategoryName . '_' . time() . '.pdf';
        $filePath = $pdfDir . $filename;

        file_put_contents($filePath, $pdfOutput);

        // Record in Database
        $this->pdfModel->create([
            'categoria_id' => $categoryId,
            'archivo_pdf' => $filename
        ]);

        Session::setFlash('success', 'PDF generado y guardado correctamente.');
        
        // We'll redirect back and let them download it from the list
        $response->redirect('/admin/pdfs');
    }

    // Download an already saved PDF from admin
    public function downloadSaved(Request $request, Response $response, $id) {
        $pdf = $this->pdfModel->find($id);
        if (!$pdf) {
            Session::setFlash('error', 'El archivo PDF no existe.');
            $response->redirect('/admin/pdfs');
        }

        $filePath = dirname(dirname(__DIR__)) . '/public/uploads/pdfs/' . $pdf['archivo_pdf'];
        if (!file_exists($filePath)) {
            Session::setFlash('error', 'El archivo físico no se encuentra en el servidor.');
            $response->redirect('/admin/pdfs');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdf['archivo_pdf'] . '"');
        readfile($filePath);
        exit;
    }

    // Delete PDF
    public function delete(Request $request, Response $response, $id) {
        $pdf = $this->pdfModel->find($id);
        if (!$pdf) {
            return $response->json(['success' => false, 'message' => 'PDF no encontrado.'], 404);
        }

        $this->pdfModel->delete($id);
        return $response->json(['success' => true, 'message' => 'PDF eliminado con éxito.']);
    }

    // Helper to base64 encode local images for robust Dompdf rendering
    protected function getImageBase64($filename) {
        if (empty($filename)) return null;

        $path = dirname(dirname(__DIR__)) . '/public/uploads/' . $filename;
        if (file_exists($path)) {
            $data = file_get_contents($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    // Render HTML layout for PDF
    protected function renderPdfHtml($category, $questions) {
        // Obtenemos la fecha actual formateada
        $date = date('d/m/Y');
        
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #333333;
                    line-height: 1.5;
                    margin: 20px;
                }
                .header {
                    border-bottom: 2px solid #4f46e5;
                    padding-bottom: 15px;
                    margin-bottom: 30px;
                }
                .header table {
                    width: 100%;
                }
                .logo-text {
                    font-size: 24px;
                    font-weight: bold;
                    color: #4f46e5;
                }
                .title {
                    font-size: 20px;
                    font-weight: bold;
                    margin-top: 10px;
                    color: #1e1b4b;
                }
                .meta-info {
                    font-size: 12px;
                    color: #666666;
                    text-align: right;
                }
                .question-card {
                    margin-bottom: 25px;
                    page-break-inside: avoid;
                }
                .question-title {
                    font-size: 14px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #1e293b;
                }
                .question-img {
                    max-width: 300px;
                    max-height: 200px;
                    margin: 10px 0;
                    display: block;
                }
                .alternatives-list {
                    margin-left: 20px;
                    list-style-type: lower-alpha;
                }
                .alternative-item {
                    font-size: 12px;
                    margin-bottom: 5px;
                    color: #475569;
                }
                .page-break {
                    page-break-before: always;
                }
                .section-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #4f46e5;
                    border-bottom: 1px solid #e2e8f0;
                    padding-bottom: 5px;
                    margin-top: 30px;
                    margin-bottom: 20px;
                    page-break-after: avoid;
                }
                .resolution-card {
                    margin-bottom: 20px;
                    padding: 10px 15px;
                    background-color: #f8fafc;
                    border-left: 4px solid #10b981;
                    page-break-inside: avoid;
                }
                .resolution-header {
                    font-size: 12px;
                    font-weight: bold;
                    color: #0f766e;
                    margin-bottom: 5px;
                }
                .correct-answer-badge {
                    font-weight: bold;
                    color: #059669;
                }
                .resolution-text {
                    font-size: 11px;
                    color: #334155;
                    white-space: pre-line;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <table>
                    <tr>
                        <td>
                            <div class="logo-text">AutoEvaluación</div>
                            <div class="title">Banco de Preguntas: ' . htmlspecialchars($category['nombre']) . '</div>
                        </td>
                        <td class="meta-info">
                            <strong>Fecha:</strong> ' . $date . '<br>
                            <strong>Total Preguntas:</strong> ' . count($questions) . '<br>
                            <strong>Autor:</strong> Simulador Online
                        </td>
                    </tr>
                </table>
            </div>';

        // 1. List Questions & Alternatives
        $html .= '<div class="questions-section">';
        foreach ($questions as $index => $q) {
            $num = $index + 1;
            $html .= '<div class="question-card">';
            $html .= '<div class="question-title">Pregunta ' . $num . ': ' . htmlspecialchars($q['pregunta']) . '</div>';
            
            if ($q['imagen_base64']) {
                $html .= '<img class="question-img" src="' . $q['imagen_base64'] . '" alt="Imagen Pregunta">';
            }

            $html .= '<ol class="alternatives-list">';
            foreach ($q['alternativas'] as $alt) {
                $html .= '<li class="alternative-item">' . htmlspecialchars($alt['alternativa']) . '</li>';
            }
            $html .= '</ol>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // 2. Answers & Resolutions Section
        $html .= '<div class="page-break"></div>';
        $html .= '<div class="section-title">Respuestas Correctas y Resoluciones Detalladas</div>';

        foreach ($questions as $index => $q) {
            $num = $index + 1;
            
            // Get correct alternative label
            $correctLabel = "";
            $labels = ['a', 'b', 'c', 'd', 'e', 'f'];
            foreach ($q['alternativas'] as $altIndex => $alt) {
                if ($alt['es_correcta'] == 1) {
                    $correctLabel = ($labels[$altIndex] ?? '?') . ') ' . $alt['alternativa'];
                    break;
                }
            }

            $html .= '<div class="resolution-card">';
            $html .= '<div class="resolution-header">Pregunta ' . $num . ' - Clave: <span class="correct-answer-badge">' . htmlspecialchars($correctLabel) . '</span></div>';
            
            if (!empty($q['resolucion'])) {
                $html .= '<div class="resolution-text"><strong>Resolución Detallada:</strong><br>' . nl2br(htmlspecialchars($q['resolucion'])) . '</div>';
            } else {
                $html .= '<div class="resolution-text"><em>No se ha especificado una resolución detallada para esta pregunta.</em></div>';
            }
            if (!empty($q['imagen_resolucion_base64'])) {
                $html .= '<img class="question-img" src="' . $q['imagen_resolucion_base64'] . '" alt="Imagen Resolución">';
            }
            $html .= '</div>';
        }

        $html .= '
        </body>
        </html>';

        return $html;
    }

    // Bulk Download saved PDFs (combined into one)
    public function bulkDownload(Request $request, Response $response) {
        $pdfIds = $request->post('pdf_ids');
        if (empty($pdfIds) || !is_array($pdfIds)) {
            Session::setFlash('error', 'No se seleccionaron PDFs para descargar.');
            $response->redirect('/admin/pdfs');
        }

        $allData = [];
        $totalQuestions = 0;

        foreach ($pdfIds as $id) {
            $pdf = $this->pdfModel->find($id);
            if ($pdf) {
                $category = $this->categoryModel->find($pdf['categoria_id']);
                if ($category) {
                    $questions = $this->questionModel->findByCategory($category['id']);
                    if (count($questions) > 0) {
                        // Load alternatives & base64 images
                        foreach ($questions as &$q) {
                            $q['alternativas'] = $this->alternativeModel->findByQuestion($q['id']);
                            $q['imagen_base64'] = $this->getImageBase64($q['imagen']);
                            $q['imagen_resolucion_base64'] = $this->getImageBase64($q['imagen_resolucion'] ?? null);
                        }
                        $allData[] = [
                            'category' => $category,
                            'questions' => $questions
                        ];
                        $totalQuestions += count($questions);
                    }
                }
            }
        }

        if (empty($allData)) {
            Session::setFlash('error', 'No se pudo generar el PDF. Las categorías seleccionadas no tienen preguntas válidas.');
            $response->redirect('/admin/pdfs');
        }

        // Generate combined HTML
        $html = $this->renderBulkPdfHtml($allData, $totalQuestions);

        // Dompdf configuration
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Examen_Combinado_' . time() . '.pdf';
        
        // Stream download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }

    // Render HTML layout for Bulk PDF
    protected function renderBulkPdfHtml($allData, $totalQuestions) {
        $date = date('d/m/Y');
        
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; color: #333333; line-height: 1.5; margin: 20px; }
                .header { border-bottom: 2px solid #4f46e5; padding-bottom: 15px; margin-bottom: 30px; }
                .header table { width: 100%; }
                .logo-text { font-size: 24px; font-weight: bold; color: #4f46e5; }
                .title { font-size: 20px; font-weight: bold; margin-top: 10px; color: #1e1b4b; }
                .meta-info { font-size: 12px; color: #666666; text-align: right; }
                .question-card { margin-bottom: 25px; page-break-inside: avoid; }
                .question-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #1e293b; }
                .question-img { max-width: 300px; max-height: 200px; margin: 10px 0; display: block; }
                .alternatives-list { margin-left: 20px; list-style-type: lower-alpha; }
                .alternative-item { font-size: 12px; margin-bottom: 5px; color: #475569; }
                .page-break { page-break-before: always; }
                .section-title { font-size: 18px; font-weight: bold; color: #4f46e5; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 30px; margin-bottom: 20px; page-break-after: avoid; }
                .category-title { font-size: 16px; font-weight: bold; background: #e2e8f0; padding: 8px 12px; margin-bottom: 15px; border-radius: 4px; color: #0f172a; }
                .resolution-card { margin-bottom: 20px; padding: 10px 15px; background-color: #f8fafc; border-left: 4px solid #10b981; page-break-inside: avoid; }
                .resolution-header { font-size: 12px; font-weight: bold; color: #0f766e; margin-bottom: 5px; }
                .correct-answer-badge { font-weight: bold; color: #059669; }
                .resolution-text { font-size: 11px; color: #334155; white-space: pre-line; }
            </style>
        </head>
        <body>
            <div class="header">
                <table>
                    <tr>
                        <td>
                            <div class="logo-text">AutoEvaluación</div>
                            <div class="title">Banco de Preguntas Combinado (' . count($allData) . ' Exámenes)</div>
                        </td>
                        <td class="meta-info">
                            <strong>Fecha:</strong> ' . $date . '<br>
                            <strong>Total Preguntas:</strong> ' . $totalQuestions . '<br>
                            <strong>Autor:</strong> Simulador Online
                        </td>
                    </tr>
                </table>
            </div>';

        // 1. List Questions & Alternatives (group by category)
        $globalIndex = 1;
        foreach ($allData as $data) {
            $html .= '<div class="category-title">' . htmlspecialchars($data['category']['nombre']) . '</div>';
            $html .= '<div class="questions-section">';
            
            foreach ($data['questions'] as $q) {
                $html .= '<div class="question-card">';
                $html .= '<div class="question-title">Pregunta ' . $globalIndex . ': ' . htmlspecialchars($q['pregunta']) . '</div>';
                
                if ($q['imagen_base64']) {
                    $html .= '<img class="question-img" src="' . $q['imagen_base64'] . '" alt="Imagen Pregunta">';
                }

                $html .= '<ol class="alternatives-list">';
                foreach ($q['alternativas'] as $alt) {
                    $html .= '<li class="alternative-item">' . htmlspecialchars($alt['alternativa']) . '</li>';
                }
                $html .= '</ol>';
                $html .= '</div>';
                $globalIndex++;
            }
            $html .= '</div>';
        }

        // 2. Answers & Resolutions Section
        $html .= '<div class="page-break"></div>';
        $html .= '<div class="section-title">Respuestas Correctas y Resoluciones Detalladas</div>';

        $globalIndex = 1;
        foreach ($allData as $data) {
            $html .= '<div class="category-title">' . htmlspecialchars($data['category']['nombre']) . '</div>';
            
            foreach ($data['questions'] as $q) {
                // Get correct alternative label
                $correctLabel = "";
                $labels = ['a', 'b', 'c', 'd', 'e', 'f'];
                foreach ($q['alternativas'] as $altIndex => $alt) {
                    if ($alt['es_correcta'] == 1) {
                        $correctLabel = ($labels[$altIndex] ?? '?') . ') ' . $alt['alternativa'];
                        break;
                    }
                }

                $html .= '<div class="resolution-card">';
                $html .= '<div class="resolution-header">Pregunta ' . $globalIndex . ' - Clave: <span class="correct-answer-badge">' . htmlspecialchars($correctLabel) . '</span></div>';
                
                if (!empty($q['resolucion'])) {
                    $html .= '<div class="resolution-text"><strong>Resolución Detallada:</strong><br>' . nl2br(htmlspecialchars($q['resolucion'])) . '</div>';
                } else {
                    $html .= '<div class="resolution-text"><em>No se ha especificado una resolución detallada para esta pregunta.</em></div>';
                }
                if (!empty($q['imagen_resolucion_base64'])) {
                    $html .= '<img class="question-img" src="' . $q['imagen_resolucion_base64'] . '" alt="Imagen Resolución">';
                }
                $html .= '</div>';
                $globalIndex++;
            }
        }

        $html .= '
        </body>
        </html>';

        return $html;
    }
}
