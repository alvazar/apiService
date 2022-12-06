<?php
namespace App\Core\Interfaces;

interface SingletonInterface
{
    public static function getInstance(): ?object;
}
