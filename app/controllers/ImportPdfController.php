<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;
use App\Models\Question;
use App\Models\Alternative;
use Smalot\PdfParser\Parser;

class ImportPdfController {
    protected $categoryModel;
    protected $questionModel;
    protected $alternativeModel;

    public function __construct() {
        $this->categoryModel = new Category();
        $this->questionModel = new Question();
        $this->alternativeModel = new Alternative();
    }

    public function index(Request $request, Response $response) {
        $categories = $this->categoryModel->all();

        $response->render('admin/import_pdf', [
            'categories' => $categories,
            'title' => 'Importar Preguntas PDF'
        ]);
    }

    public function store(Request $request, Response $response) {
        $categoriaId = $request->post('categoria_id');
        $pdfFile = $request->file('pdf_file');

        if (empty($categoriaId) || empty($pdfFile) || $pdfFile['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Por favor seleccione una categoría y un archivo PDF válido.');
            $response->redirect('/admin/importar-pdf');
        }

        $ext = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            Session::setFlash('error', 'El archivo debe ser un PDF.');
            $response->redirect('/admin/importar-pdf');
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfFile['tmp_name']);
            $text = $pdf->getText();

            // Lógica de extracción de texto
            $questionsCount = 0;
            
            // Reemplazar saltos de línea extraños y unificar
            $text = str_replace(["\r\n", "\r"], "\n", $text);
            
            // Dividir por números de pregunta (ej. "1.", "2.", "120.")
            $blocks = preg_split('/(?<=\n|^)\s*\d+\.\s+/', "\n" . $text);

            foreach ($blocks as $block) {
                $block = trim($block);
                if (empty($block) || strlen($block) < 5) continue;
                
                $lines = explode("\n", $block);
                $questionText = "";
                $alternativesRaw = [];
                $respuestaCorrecta = null;
                $resolucion = "Importado desde PDF";
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // Buscar alternativas a), b), c), d) o A., B., C.
                    if (preg_match('/^[a-eA-E][\.\)]\s+(.*)$/', $line, $matches)) {
                        $alternativesRaw[] = $matches[1];
                    } elseif (preg_match('/^Resp(?:uesta)?:\s*([a-eA-E])/i', $line, $matches)) {
                        $respuestaCorrecta = strtolower($matches[1]);
                    } elseif (preg_match('/^Resoluci[oó]n:\s*(.*)$/i', $line, $matches)) {
                        $resolucion = $matches[1];
                    } else {
                        if (empty($alternativesRaw)) {
                            $questionText .= $line . "\n";
                        }
                    }
                }
                
                $questionText = trim($questionText);
                
                if (!empty($questionText)) {
                    $qId = $this->questionModel->create([
                        'categoria_id' => $categoriaId,
                        'pregunta' => $questionText,
                        'imagen' => null,
                        'resolucion' => $resolucion,
                        'puntaje' => 1
                    ]);
                    
                    $alts = [];
                    $letters = ['a','b','c','d','e'];
                    $correctIndex = -1;
                    
                    foreach ($alternativesRaw as $idx => $altTxt) {
                        $isCorrect = false;
                        if ($respuestaCorrecta && isset($letters[$idx]) && $letters[$idx] === $respuestaCorrecta) {
                            $isCorrect = true;
                            $correctIndex = $idx;
                        }
                        $alts[] = [
                            'alternativa' => trim($altTxt),
                            'es_correcta' => $isCorrect ? 1 : 0
                        ];
                    }
                    
                    if ($correctIndex === -1 && count($alts) > 0) {
                         $alts[0]['es_correcta'] = 1;
                    }
                    
                    // Asegurar al menos 4 alternativas si no se extrajeron bien
                    if (empty($alts)) {
                         $alts = [
                             ['alternativa' => 'Verdadero', 'es_correcta' => 1],
                             ['alternativa' => 'Falso', 'es_correcta' => 0],
                             ['alternativa' => 'Opción C', 'es_correcta' => 0],
                             ['alternativa' => 'Opción D', 'es_correcta' => 0],
                         ];
                    } elseif (count($alts) < 4) {
                        while(count($alts) < 4) {
                            $alts[] = ['alternativa' => 'Opción extra', 'es_correcta' => 0];
                        }
                    }

                    $this->alternativeModel->saveAlternatives($qId, $alts);
                    $questionsCount++;
                }
            }

            if ($questionsCount > 0) {
                Session::setFlash('success', "Se importaron $questionsCount preguntas exitosamente desde el PDF.");
            } else {
                Session::setFlash('error', "No se pudieron detectar preguntas en el formato esperado.");
            }

            $response->redirect('/admin/preguntas');

        } catch (\Exception $e) {
            Session::setFlash('error', 'Error al procesar el PDF: ' . $e->getMessage());
            $response->redirect('/admin/importar-pdf');
        }
    }
}
