<?php

namespace App\Core\Interfaces;

interface RepositoryInterface
{
    public function get();

    public function send(array $params = []): void;
}
