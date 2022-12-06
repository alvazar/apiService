<?php

namespace App\Core\Traits;

trait ParamsTrait
{
    protected $params = [];

    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }
}
