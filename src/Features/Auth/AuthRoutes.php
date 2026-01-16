<?php

namespace App\Features\Auth;

final class AuthRoutes
{
    public static function handle(string $path, string $method): bool
    {
        if ($path === '/register' && $method === 'POST') {
            (new AuthController())->register();
            return true;
        }

        if ($path === '/login' && $method === 'POST') {
            (new AuthController())->login();
            return true;
        }

        if ($path === '/logout' && $method === 'POST') {
            (new AuthController())->logout();
            return true;
        }

        return false;
    }
}
