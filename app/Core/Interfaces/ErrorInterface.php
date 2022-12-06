<?php

namespace App\Core\Interfaces;

interface ErrorInterface
{
    public function setError(string $error): self;

    public function getError(): string;

    public function hasError(): bool;
}
