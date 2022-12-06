<?php

namespace App\Core\Interfaces;

interface ServiceInterface
{
    public function run(array $params = []): array;
}
