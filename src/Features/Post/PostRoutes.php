<?php

namespace App\Features\Post;

final class PostRoutes
{
    public static function handle(
        string $path,
        string $method,
        array  $segments
    ): bool
    {
        if ($path === '/posts' && $method === 'POST') {
            (new PostController())->createPost();
            return true;
        }

        if ($path === '/posts' && $method === 'GET') {
            (new PostController())->feedPost();
            return true;
        }

        if (
            isset($segments[0], $segments[1]) &&
            $segments[0] === 'posts' &&
            ctype_digit($segments[1]) &&
            $method === 'GET'
        ) {
            (new PostController())->detailPost((int)$segments[1]);
            return true;
        }

        if (
            isset($segments[0], $segments[1]) &&
            $segments[0] === 'posts' &&
            ctype_digit($segments[1]) &&
            $method === 'PUT'
        ) {
            (new PostController())->updatePost((int)$segments[1]);
            return true;
        }

        if (
            isset($segments[0], $segments[1]) &&
            $segments[0] === 'posts' &&
            ctype_digit($segments[1]) &&
            $method === 'DELETE'
        ) {
            (new PostController())->deletePost((int)$segments[1]);
            return true;
        }

        return false;
    }
}
