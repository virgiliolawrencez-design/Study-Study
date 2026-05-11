<?php
namespace App\Core;

use App\Controllers\StudentController;

class Router
{
    private array $routes = [];

    public function add(string $method, string $uri, string $controller, string $function)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'function' => $function,

        ];

    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
        // Strip base path for subfolder deployment
        $base = '/Study-Study';
        if (strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '') $uri = '/';
        foreach ($this->routes as $route) {
            $pattern = str_replace(
                '{id}',
                '([0-9]+)',
                $route['uri'],
            );
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                require_once '../app/controllers/' .$route['controller'] . '.php';

                $controllerClass = 'App\\Controllers\\' .$route['controller'];
                $controller = new $controllerClass();
                $function = $route['function'];

                call_user_func_array([$controller, $function], $matches);
                return;

            }
        }

        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';

    }

}

?>