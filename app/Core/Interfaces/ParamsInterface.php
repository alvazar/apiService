<?php

namespace App\Core\Interfaces;

interface ParamsInterface
{
    public function setParam(string $key, $value): self;

    public function getParam(string $key);
}
