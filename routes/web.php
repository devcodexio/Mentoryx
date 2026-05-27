<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;

$router = new Router();

// --- PUBLIC ROUTES ---
$router->get('/', 'HomeController@index');
$router->get('/examen/:id', 'ExamController@start');
$router->post('/examen/finalizar', 'ExamController@submit');
$router->get('/examen/resultados/:id', 'ExamController@results');
$router->get('/categoria/pdf/descargar', 'PdfController@downloadPublic');

// --- ADMIN AUTH ROUTES ---
$router->get('/admin/login', 'AdminController@login', [AuthMiddleware::class]);
$router->post('/admin/login', 'AdminController@login');
$router->get('/admin/logout', 'AdminController@logout');

// --- ADMIN PROTECTED ROUTES ---
$router->get('/admin', 'AdminController@dashboard', [AuthMiddleware::class]);
$router->get('/admin/dashboard', 'AdminController@dashboard', [AuthMiddleware::class]);
$router->get('/admin/resultados', 'AdminController@results', [AuthMiddleware::class]);

// Admin - Categories CRUD
$router->get('/admin/categorias', 'CategoryController@index', [AuthMiddleware::class]);
$router->get('/admin/categorias/crear', 'CategoryController@create', [AuthMiddleware::class]);
$router->post('/admin/categorias/crear', 'CategoryController@create', [AuthMiddleware::class]);
$router->get('/admin/categorias/editar/:id', 'CategoryController@edit', [AuthMiddleware::class]);
$router->post('/admin/categorias/editar/:id', 'CategoryController@edit', [AuthMiddleware::class]);
$router->post('/admin/categorias/eliminar/:id', 'CategoryController@delete', [AuthMiddleware::class]);

// Admin - Questions CRUD
$router->get('/admin/preguntas', 'QuestionController@index', [AuthMiddleware::class]);
$router->get('/admin/preguntas/crear', 'QuestionController@create', [AuthMiddleware::class]);
$router->post('/admin/preguntas/crear', 'QuestionController@create', [AuthMiddleware::class]);
$router->get('/admin/preguntas/editar/:id', 'QuestionController@edit', [AuthMiddleware::class]);
$router->post('/admin/preguntas/editar/:id', 'QuestionController@edit', [AuthMiddleware::class]);
$router->post('/admin/preguntas/eliminar/:id', 'QuestionController@delete', [AuthMiddleware::class]);

// Admin - Import PDF
$router->get('/admin/importar-pdf', 'ImportPdfController@index', [AuthMiddleware::class]);
$router->post('/admin/importar-pdf', 'ImportPdfController@store', [AuthMiddleware::class]);

// Admin - PDFs Management
$router->get('/admin/pdfs', 'PdfController@index', [AuthMiddleware::class]);
$router->post('/admin/pdfs/generar', 'PdfController@generateAdmin', [AuthMiddleware::class]);
$router->get('/admin/pdfs/descargar/:id', 'PdfController@downloadSaved', [AuthMiddleware::class]);
$router->post('/admin/pdfs/eliminar/:id', 'PdfController@delete', [AuthMiddleware::class]);
$router->post('/admin/pdfs/bulk-descargar', 'PdfController@bulkDownload', [AuthMiddleware::class]);

// Admin - Settings
$router->get('/admin/configuracion', 'SettingController@index', [AuthMiddleware::class]);
$router->post('/admin/configuracion', 'SettingController@update', [AuthMiddleware::class]);

return $router;
