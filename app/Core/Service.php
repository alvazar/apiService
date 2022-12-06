<?php

namespace App\Core;

use App\Core\Interfaces\ServiceInterface;
use App\Core\Traits\ErrorTrait;
use App\Core\Interfaces\ErrorInterface;
use App\Core\Interfaces\ValidatedInterface;

abstract class Service implements ServiceInterface, ErrorInterface, ValidatedInterface
{
    use ErrorTrait;

    public function validateRules(): array
    {
        return [];
    }
}
