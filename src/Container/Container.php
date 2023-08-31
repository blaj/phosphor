<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Container;

use Blaj\Phosphor\Container\Exception\ContainerException;
use Blaj\Phosphor\Container\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    private array $services = [];

    public function get(string $id): object
    {
        if ($this->has($id)) {
            $service = $this->services[$id];

            if (is_callable($service)) {
                return $service($this);
            }

            $id = $service;
        }

        return $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function set(string $id, callable|string $service): self
    {
        $this->services[$id] = $service;

        return $this;
    }

    public function resolve(string $id): object
    {
        try {
            $reflectionsClass = new \ReflectionClass($id);
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$reflectionsClass->isInstantiable()) {
            throw new ContainerException(sprintf('Class %s is not instantiable', $id));
        }

        $constructor = $reflectionsClass->getConstructor();

        if ($constructor === null) {
            return new $id;
        }

        $parameters = $constructor->getParameters();

        if (count($parameters) === 0) {
            return new $id;
        }

        $dependencies = array_map(function (\ReflectionParameter $reflectionParameter) use ($id) {
            $name = $reflectionParameter->getName();
            $type = $reflectionParameter->getType();

            if ($type === null) {
                throw new ContainerException(sprintf('Failed to resolve class %s, because param %s is missing a type hint', $id, $name));
            }

            if ($type instanceof \ReflectionUnionType) {
                throw new ContainerException(sprintf('Failed to resolve class %s, because param %s is union type', $id, $name));
            }

            if (!$type instanceof \ReflectionNamedType) {
                throw new ContainerException(sprintf('Failed to resolve class %s, because param %s is invalid param', $id, $name));
            }

            if ($type->isBuiltin()) {
                throw new ContainerException(sprintf('Failed to resolve class %s, because param %s is simple type', $id, $name));
            }

            try {
                return $this->get($type->getName());
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                return null;
            }
        }, $parameters);

        try {
            return $reflectionsClass->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new ContainerException(sprintf('Failed to instantiate %s', $id), $e->getCode(), $e);
        }
    }
}