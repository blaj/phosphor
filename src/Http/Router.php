<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Http;

use Blaj\Phosphor\Container\Container;
use Blaj\Phosphor\Http\Attributes\Route;
use Blaj\Phosphor\Http\Exception\RouteNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Router
{

    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function registerRoutesFromAttributes(array $controllers): self
    {
        foreach ($controllers as $controller) {
            $reflectionClass = new \ReflectionClass($controller);

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($reflectionMethod->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
                    /** @var Route $route */
                    $route = $reflectionAttribute->newInstance();

                    $this->register($route->method, $route->routePath, [$controller, $reflectionMethod->getName()]);
                }
            }
        }

        return $this;
    }

    public function register(string $method, string $routePath, callable|array $action): self
    {
        $this->routes[$method][$routePath] = $action;

        return $this;
    }

    public function get(string $routePath, callable|array $action): self
    {
        return $this->register('GET', $routePath, $action);
    }

    public function post(string $routePath, callable|array $action): self
    {
        return $this->register('POST', $routePath, $action);
    }

    public function resolve(string $requestUri, string $method): mixed
    {
        $route = explode('?', $requestUri)[0];
        $action = $this->routes[$method][$route] ?? null;

        if ($action === null) {
            throw new RouteNotFoundException();
        }

        if (is_callable($action)) {
            return call_user_func($action);
        }

        [$class, $function] = $action;

        if (!class_exists($class)) {
            throw new RouteNotFoundException();
        }

        try {
            $class = $this->container->get($class);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new RouteNotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        if (!method_exists($class, $function)) {
            throw new RouteNotFoundException();
        }

        return call_user_func_array([$class, $function], []);
    }
}