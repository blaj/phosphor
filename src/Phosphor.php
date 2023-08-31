<?php

declare(strict_types=1);

namespace Blaj\Phosphor;

use Blaj\Phosphor\Container\Container;
use Blaj\Phosphor\Http\Router;

class Phosphor
{
    private readonly Container $container;
    private readonly Router $router;

    private bool $booted = false;

    public function __construct()
    {
        $this->container = new Container();
        $this->router = new Router($this->container);
    }

    public function boot(array $controllers = []): self
    {
        $this->router->registerRoutesFromAttributes($controllers);
        $this->booted = true;

        return $this;
    }

    public function run(): self
    {
        if (!$this->booted) {
            $this->boot([]);
        }

        $this->router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

        return $this;
    }
}