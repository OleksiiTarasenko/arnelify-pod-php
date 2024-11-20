<?php
class Router
{
    private $routes = [];

    private function sortRoutes() {
        usort($this->routes, function ($a, $b) {
            $paramsLen = function ($segments) {
                return count(array_filter($segments, function ($segment) {
                    return strpos($segment, ':') === 0;
                }));
            };

            $segmentsA = explode('/', $a['route']);
            $segmentsB = explode('/', $b['route']);
            $paramsA = $paramsLen($segmentsA);
            $paramsB = $paramsLen($segmentsB);

            if ($paramsA !== $paramsB) return $paramsA - $paramsB;
            return count($segmentsA) - count($segmentsB);
        });
    }

    public function any($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => null, 'controller' => $controller];
        $this->sortRoutes();
    }

    public function get($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => 'GET', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function post($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => 'POST', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function put($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => 'PUT', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function patch($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => 'PATCH', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function delete($route, $controller) {
        $this->routes[] = ['route' => $route, 'method' => 'DELETE', 'controller' => $controller];
        $this->sortRoutes();
    }

}