<?php
namespace App\Core;

class Request
{
    private $rootDir;

    public function __construct(string $rootDir = '')
    {
        $this->rootDir = $rootDir;
    }

    public function type(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getData(): array
    {
        $type = $this->type();
        $data = [];

        if ($type === 'GET') {
            $data = $_GET;
        } elseif ($type === 'POST') {
            $data = $_POST;
        }

        return $data;
    }

    public function getQuery(): string
    {
        return preg_replace('/\?.+/', '', $this->rootDir . $_SERVER['REQUEST_URI']);
    }

    public function getVars(string $param): array
    {
        $query = $this->getQuery();
        $param = preg_quote($param, '/');
        $param = preg_replace("/\\\{(.+?)\\\}/u", "(?<\$1>[^&\/]+)", $param);
        preg_match('/^'.$param.'$/u', $query, $match);
        $match = !empty($match) ? $match : [];
        $queryVars = [];

        foreach ($match as $key => $value) {

            if (!is_numeric($key)) {
                $queryVars[$key] = $value;
            }

        }

        return $queryVars;
    }

    public function hasParam(string $param): bool
    {
        $query = $this->getQuery();
        $param = preg_quote($param, '/');
        $param = preg_replace("/\\\{(.+?)\\\}/u", "[^&\/]+", $param);

        return preg_match('/^'.$param.'$/u', $query) === 1;
    }
}
