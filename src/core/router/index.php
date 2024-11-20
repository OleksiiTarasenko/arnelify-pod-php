<?php
class Router
{
    private $routes = [];

    public function any($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => null, 'controller' => $controller];
        $this->sortRoutes();
    }

    public function get($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => 'GET', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function post($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => 'POST', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function put($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => 'PUT', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function patch($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => 'PATCH', 'controller' => $controller];
        $this->sortRoutes();
    }

    public function delete($route, $controller)
    {
        $this->routes[] = ['route' => $route, 'method' => 'DELETE', 'controller' => $controller];
        $this->sortRoutes();
    }

}