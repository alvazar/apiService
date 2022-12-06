<?php

namespace App\Core;

use App\Core\Interfaces\RepositoryInterface;
use App\Core\Traits\ErrorTrait;
use App\Core\Traits\ParamsTrait;
use App\Core\Interfaces\ErrorInterface;
use App\Core\Interfaces\ParamsInterface;

abstract class Repository implements RepositoryInterface, ErrorInterface, ParamsInterface
{
    use ErrorTrait, ParamsTrait;
}
