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

    private function findSegments($route, $segmentsRoute, $segmentsUrl) {
        if (count($segmentsRoute) !== count($segmentsUrl)) return null;

        foreach ($segmentsRoute as $i => $segment) {
            if (strpos($segment, ':') === 0) {
                $key = substr($segment, 1);
                $route['params'][$key] = $segmentsUrl[$i];
            } else {
                if ($segment !== $segmentsUrl[$i]) return null;
            }
        }

        return $route;
    }

    private function findRoute($req) {
        $pathname = strtok($req['url'], '?');
        $segmentsUrl = explode('/', $pathname);

        foreach ($this->routes as $route) {
            $newRoute = $route;
            $newRoute['params'] = [];
            $segmentsRoute = explode('/', $route['route']);
            $segments = $this->findSegments($newRoute, $segmentsRoute, $segmentsUrl);
            if ($segments) return $newRoute;
        }

        return null;
    }

    private function getAgent($req) {
        return $req['headers']['User-Agent'] ?? null;
    }

    private function getCookies($req) {
        if (!isset($req['headers']['Cookie'])) return null;

        $cookies = [];
        foreach (explode('; ', $req['headers']['Cookie']) as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $cookies[$key] = $value;
        }

        return $cookies;
    }

    private function getParams($req) {
        $query = [];
        
        $urlQuery = parse_url($req['url'], PHP_URL_QUERY);
        if (!$urlQuery) return null;

        parse_str($urlQuery, $query);
        return $query;
    }

    private function getBody($req) {
        /* TODO*/
    }

    private function getIp($req) {
        return $req['headers']['X-Forwarded-For'] ?? $req['remote_addr'];
    }

    public function request($req, $res) {
        $pathname = strtok($req['url'], '?');
        $route = $this->findRoute($req);
        $cookies = $this->getCookies($req);
        $params = $this->getParams($req);
        $body = $req['method'] === 'POST' ? $this->getBody($req) : [];
        $agent = $this->getAgent($req);
        $ip = $this->getIp($req);

        $state = [
            '_state' => [
                'pathname' => $pathname,
                'cookie'   => $cookies,
                'route'    => $route,
                'agent'    => $agent,
                'ip'       => $ip,
            ],
        ];
        $ctx = ['params' => array_merge($state, $params, $body)];

        if (!$route || !isset($route['controller'])) {
            $res->status(404)->send(['code' => 404, 'error' => 'Not found.']);
            return;
        }

        $response = call_user_func($route['controller'], $ctx);
        if (!$response) {
            $res->status(204)->send(['code' => 204, 'error' => 'Empty response.']);
            return;
        }

        $code = $response['code'] ?? 200;
        $headers = $response['headers'] ?? ['Content-Type' => 'application/json'];
        $res->status($code)->headers($headers)->send($response);
    }
    
}
