<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Http\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(public readonly string $routePath, public readonly string $method = 'GET')
    {
    }
}