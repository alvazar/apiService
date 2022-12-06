<?php
namespace App\Core;

use App\Core\Request;

class Route
{
    private static $getCB = [];
    private static $postCB = [];

    public static function run($cb, array $params = [])
    {
        if (is_string($cb) && preg_match("/\./u", $cb) === 1) {
            [$cl, $mt] = explode('.', $cb);

            return (new $cl($params))->$mt();
        }

        return $cb($params);
    }

    public static function get(string $queryTrigger, $cb, array $params = []): void
    {
        $triggers = explode(',', $queryTrigger);

        foreach ($triggers as $trigger) {
            $trigger = trim($trigger);
            self::$getCB[$trigger] = [$cb, $params];
        }
    }

    public static function post(string $queryTrigger, $cb, array $params = []): void
    {
        $triggers = explode(',', $queryTrigger);

        foreach ($triggers as $trigger) {
            $trigger = trim($trigger);
            self::$postCB[$trigger] = [$cb, $params];
        }
    }

    public static function start(Request $request): void
    {
        $lst = [];

        if ($request->type() === 'GET') {
            $lst = self::$getCB;
        } elseif ($request->type() === 'POST') {
            $lst = self::$postCB;
        }

        foreach ($lst as $queryTrigger => $item) {

            if (!$request->hasParam($queryTrigger)) {
                continue;
            }

            self::run(
                $item[0],
                $item[1] + $request->getVars($queryTrigger) + $request->getData()
            );

            break;
        }
    }
}
