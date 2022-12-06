<?php

namespace App\Core\Interfaces;

interface ResponseInterface
{
    public function setData(array $data = []): ResponseInterface;

    public function setSuccess(string $message = ''): ResponseInterface;

    public function setError(string $message = ''): ResponseInterface;

    public function getResponse(): array;

    public function getJson(): string;
}
