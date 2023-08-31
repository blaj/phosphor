<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Http\Exception;

class NotFoundException extends \RuntimeException
{
    protected $message = '404 Not Found';
}