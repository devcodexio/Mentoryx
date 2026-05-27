<?php

// Enable error reporting for local development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload composer dependencies
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load global helper functions
require_once dirname(__DIR__) . '/app/helpers/helpers.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

// Start session
Session::start();

// Initialize Request & Response
$request = new Request();
$response = new Response();

// Load Routes and Dispatch
$router = require_once dirname(__DIR__) . '/routes/web.php';
$router->dispatch($request, $response);
