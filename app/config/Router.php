<?php

namespace App\config;

/**
 * Advanced Router with Controller mapping and Resource support
 */
class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Map a Resource (RESTful routes)
     * e.g. products -> index, show, create, store, edit, update, destroy
     */
    public function resource($name, $controller)
    {
        $this->get("/$name", [$controller, 'index']);
        $this->get("/$name/create", [$controller, 'create']);
        $this->post("/$name", [$controller, 'store']);
        $this->get("/$name/(:any)", [$controller, 'show']);
        $this->get("/$name/(:any)/edit", [$controller, 'edit']);
        $this->post("/$name/(:any)/update", [$controller, 'update']);
        $this->post("/$name/(:any)/delete", [$controller, 'destroy']);
    }

    /**
     * Resolve the current request
     */
    public function resolve($uri, $method)
    {
        // Simple matching for now
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $path => $callback) {
                // Convert (:any) to regex
                $pattern = str_replace('(:any)', '([^/]+)', $path);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove first match

                    if (is_array($callback)) {
                        $controllerName = $callback[0];
                        $methodName = $callback[1];
                        $controller = new $controllerName();
                        return call_user_func_array([$controller, $methodName], $matches);
                    }

                    if (is_callable($callback)) {
                        return call_user_func_array($callback, $matches);
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
