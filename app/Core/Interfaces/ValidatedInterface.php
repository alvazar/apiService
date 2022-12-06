<?php

namespace App\Core\Interfaces;

interface ValidatedInterface
{
    public function validateRules(): array;
}
