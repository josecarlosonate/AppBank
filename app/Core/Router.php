<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->routes['GET'][$path] = [$handler, $middlewares];
    }

    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->routes['POST'][$path] = [$handler, $middlewares];
    }

    public function view(string $path, string $handler, array $middlewares = []): void
    {
        $this->routes['GET'][$path] = [$handler, $middlewares];
    }

    public function find(string $httpMethod, string $path): array|string|null
    {
        return $this->routes[$httpMethod][$path] ?? null;
    }

    public function dispatch(string $httpMethod, string $path)
    {
        $route = $this->find($httpMethod, $path);

        if ($route === null) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }

        [$handler, $middlewares] = $route;

        foreach ($middlewares as $middleware) {
            $middlewareInstance = $this->container->make($middleware);
            $middlewareInstance->handle();
        }

        if (is_string($handler)) {
            require __DIR__ . '/../views/' . $handler . '.php';
            return;
        }

        [$controllerClass, $controllerMethod] = $handler;

        $controllerInstance = $this->container->make($controllerClass);
        return $controllerInstance->$controllerMethod();
    }
}
