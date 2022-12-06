<?php

namespace App\Core;

class Container
{
    protected static array $services = [];

    public static function add(string $name, string $cl): void
    {
        self::$services[$name] = $cl;
    }

    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$services);
    }

    public static function get(string $name): ?object
    {
        return self::has($name)
            ? new self::$services[$name]
            : null;
    }
}
