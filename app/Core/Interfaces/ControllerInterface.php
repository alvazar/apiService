<?php

namespace App\Core\Interfaces;

interface ControllerInterface
{
    public function send(array $params): ResponseInterface;
}
