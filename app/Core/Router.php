<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function add($method, $uri, $controller) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller
        ];
    }

    public function dispatch($uri, $method) {
        $uri = trim(explode('?', $uri)[0], '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            $routeUri = trim($route['uri'], '/');
            if ($routeUri === '') $routeUri = '/';

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri);
            $pattern = '#^' . $pattern . '/?(?P<extra>.*)$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Support Closure as controller
                if (is_callable($route['controller'])) {
                    return call_user_func($route['controller']);
                }
                
                // Support Controller@Action format
                $controllerAction = explode('@', $route['controller']);
                $controllerName = "App\\Controllers\\" . $controllerAction[0];
                $action = $controllerAction[1];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $action)) {
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        // If it's a sub-resource for a page
                        if ($route['controller'] === 'PageController@view' && !empty($params['extra'])) {
                            return $controller->serveAsset($params['slug'], $params['extra']);
                        }
                        // Use array_values to pass parameters by position for PHP 8 compatibility
                        $paramValues = array_values($params);
                        return $controller->$action(...$paramValues);
                    }
                }
            }
        }

        $this->abort();
    }

    protected function abort($code = 404) {
        http_response_code($code);
        echo "404 Not Found";
        exit;
    }
}
