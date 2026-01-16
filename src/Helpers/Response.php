<?php

namespace App\Helpers;

final class Response
{
    public static function json($data, $code, array $headers = []): never
    {
        http_response_code($code);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data);
        exit;
    }
}
