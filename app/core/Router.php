<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($route, $handler, $middleware = []) {
        $this->add('GET', $route, $handler, $middleware);
    }

    public function post($route, $handler, $middleware = []) {
        $this->add('POST', $route, $handler, $middleware);
    }

    public function add($method, $route, $handler, $middleware = []) {
        // Convert route to regex
        // E.g., /admin/preguntas/editar/:id -> ~^/admin/preguntas/editar/([^/]+)$~
        $pattern = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $route);
        $pattern = '~^' . $pattern . '$~';

        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch(Request $request, Response $response) {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches); // Remove first match which is full path

                // Run Middlewares
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $middleware->handle($request, $response);
                }

                // Execute Handler
                $handler = $route['handler'];
                if (is_string($handler)) {
                    $parts = explode('@', $handler);
                    $controllerName = "\\App\\Controllers\\" . $parts[0];
                    $action = $parts[1];

                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        if (method_exists($controller, $action)) {
                            return call_user_func_array([$controller, $action], array_merge([$request, $response], $matches));
                        }
                    }
                    
                    $response->status(500);
                    echo "Action [{$action}] on controller [{$controllerName}] not found.";
                    return;
                } elseif (is_callable($handler)) {
                    return call_user_func_array($handler, array_merge([$request, $response], $matches));
                }
            }
        }

        // Route not found
        $response->status(404);
        $response->render('errors/404');
    }
}
