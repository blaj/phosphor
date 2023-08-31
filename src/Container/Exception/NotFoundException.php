<?php

declare(strict_types=1);

namespace Blaj\Phosphor\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{

}