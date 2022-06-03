<?php

namespace App\Route;

use App\Controllers;

final class Web
{
    public static function post(string $url, array $action) : void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SERVER['REQUEST_URI'] !== $url) {
            return;
        }

        [$class, $method] = $action;

        if ($class === null || ! class_exists($class) || $method === null || ! method_exists($class, $method)) {
            throw new \Exception('bad params in $action, needed exists class name and exists class method');
        }

        $controller = new $class();

        if (! $controller instanceof Controllers\ControllerInterface) {
            throw new \Exception('bad params in $action, class needed to be instance of ControllerInterface');
        }

        $controller->$method();
        exit;
    }

    public static function close() : void
    {
        http_response_code(404);
        exit;
    }
}
