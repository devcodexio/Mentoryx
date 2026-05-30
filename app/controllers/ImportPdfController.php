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
            
            $questionsData = [];

            // 1. Detectar si es el formato exportado por el sistema
            if (strpos($text, 'Respuestas Correctas y Resoluciones Detalladas') !== false || preg_match('/Pregunta \d+:/', $text)) {
                $parts = explode('Respuestas Correctas y Resoluciones Detalladas', $text);
                $questionsPart = $parts[0];
                $resolutionsPart = $parts[1] ?? '';

                // Extraer preguntas
                $qBlocks = preg_split('/Pregunta (\d+):/', $questionsPart, -1, PREG_SPLIT_DELIM_CAPTURE);
                for ($i = 1; $i < count($qBlocks); $i += 2) {
                    $qNum = trim($qBlocks[$i]);
                    $qContent = trim($qBlocks[$i+1]);
                    
                    $lines = explode("\n", $qContent);
                    $qText = "";
                    $alts = [];
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        if (preg_match('/^[a-fA-F][\.\)]\s+(.*)$/', $line, $m)) {
                            $alts[] = $m[1];
                        } else {
                            if (empty($alts) && stripos($line, 'AutoEvaluación') === false && stripos($line, 'Banco de Preguntas') === false && stripos($line, 'Total Preguntas:') === false && stripos($line, 'Fecha:') === false) {
                                $qText .= $line . "\n";
                            }
                        }
                    }
                    $questionsData[$qNum] = [
                        'pregunta' => trim($qText),
                        'alternativas' => $alts,
                        'respuestaCorrecta' => null,
                        'resolucion' => 'Importado desde PDF'
                    ];
                }

                // Extraer resoluciones
                if (!empty($resolutionsPart)) {
                    $rBlocks = preg_split('/Pregunta\s+(\d+)\s*-\s*Clave:/i', $resolutionsPart, -1, PREG_SPLIT_DELIM_CAPTURE);
                    for ($i = 1; $i < count($rBlocks); $i += 2) {
                        $qNum = trim($rBlocks[$i]);
                        $rContent = trim($rBlocks[$i+1]);
                        
                        $lines = explode("\n", $rContent);
                        $claveLine = trim($lines[0]);
                        $clave = null;
                        if (preg_match('/([a-fA-F])[\.\)]/', $claveLine, $m)) {
                            $clave = strtolower($m[1]);
                        }
                        
                        $resolucion = "";
                        $inRes = false;
                        for ($j = 0; $j < count($lines); $j++) {
                            $line = trim($lines[$j]);
                            if (preg_match('/Resolución Detallada:/i', $line)) {
                                $inRes = true;
                                $line = preg_replace('/Resolución Detallada:/i', '', $line);
                            }
                            if ($inRes && trim($line) !== '') {
                                $resolucion .= trim($line) . "\n";
                            }
                        }
                        
                        if (isset($questionsData[$qNum])) {
                            if ($clave) $questionsData[$qNum]['respuestaCorrecta'] = $clave;
                            if (trim($resolucion)) $questionsData[$qNum]['resolucion'] = trim($resolucion);
                        }
                    }
                }
            } else {
                // 2. Formato estándar o antiguo (ej. "1.", "2.")
                $blocks = preg_split('/(?<=\n|^)\s*\d+\.\s+/', "\n" . $text);
                foreach ($blocks as $idx => $block) {
                    $block = trim($block);
                    if (empty($block) || strlen($block) < 5) continue;
                    
                    $lines = explode("\n", $block);
                    $qText = "";
                    $alts = [];
                    $clave = null;
                    $resolucion = "";
                    $inRes = false;
                    
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        if ($inRes) {
                            $resolucion .= $line . "\n";
                        } elseif (preg_match('/^[a-eA-E][\.\)]\s+(.*)$/', $line, $matches)) {
                            $alts[] = $matches[1];
                        } elseif (preg_match('/^Resp(?:uesta)?:\s*([a-eA-E])/i', $line, $matches)) {
                            $clave = strtolower($matches[1]);
                        } elseif (preg_match('/^Resoluci[oó]n:\s*(.*)$/i', $line, $matches)) {
                            $inRes = true;
                            if (!empty($matches[1])) {
                                $resolucion .= $matches[1] . "\n";
                            }
                        } else {
                            if (empty($alts)) {
                                $qText .= $line . "\n";
                            }
                        }
                    }
                    if (empty(trim($resolucion))) $resolucion = "Importado desde PDF";
                    
                    if (!empty($qText)) {
                        $questionsData[] = [
                            'pregunta' => trim($qText),
                            'alternativas' => $alts,
                            'respuestaCorrecta' => $clave,
                            'resolucion' => trim($resolucion)
                        ];
                    }
                }
            }

            // Insertar en la base de datos
            foreach ($questionsData as $qData) {
                if (empty($qData['pregunta'])) continue;

                $qId = $this->questionModel->create([
                    'categoria_id' => $categoriaId,
                    'pregunta' => $qData['pregunta'],
                    'imagen' => null,
                    'resolucion' => $qData['resolucion'],
                    'puntaje' => 1
                ]);
                
                $alts = [];
                $letters = ['a','b','c','d','e','f'];
                $correctIndex = -1;
                $respuestaCorrecta = $qData['respuestaCorrecta'];
                $alternativesRaw = $qData['alternativas'];
                
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
                
                // Asegurar al menos 4 alternativas
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
