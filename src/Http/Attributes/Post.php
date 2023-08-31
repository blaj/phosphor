<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Http\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Post extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath, 'POST');
    }
}