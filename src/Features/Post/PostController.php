<?php

namespace App\Features\Post;

use App\Database\Database;
use App\Helpers\Response;
use App\Services\JwtService;

if ($_ENV['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

class PostController
{
    public function createPost(): void
    {
        $pdo = Database::connection();
        $uid = $this->auth();

        $data = json_decode(file_get_contents('php://input'), true);
        $content = trim($data['content'] ?? '');

        if (!$content) {
            Response::json(['error' => 'Konten postingan tidak boleh kosong'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$uid, $content]);

        Response::json(['message' => 'Postingan berhasil dibuat'], 201);
    }

    private function auth(): int
    {
        $token = JwtService::bearerToken();
        if (!$token) {
            Response::json(['error' => 'Silakan login terlebih dahulu'], 401);
        }

        $decoded = JwtService::verify($token);
        if (!$decoded) {
            Response::json(['error' => 'Sesi telah berakhir, silakan login ulang'], 401);
        }

        return $decoded->data->user_id;
    }

    public function feedPost(): void
    {
        $pdo = Database::connection();
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $stmt = $pdo->prepare("
            SELECT p.id, u.username, p.content, p.created_at
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $posts = $stmt->fetchAll();

        Response::json(['posts' => $posts], 200, ['Cache-Control' => 'public, max-age=60']);
    }

    public function detailPost($id = null): void
    {
        $pdo = Database::connection();
        $id = $id ?? (isset($_GET['id']) ? (int)$_GET['id'] : 0);
        if ($id <= 0) {
            Response::json(['error' => 'ID postingan tidak valid'], 400, ['Cache-Control' => 'public, max-age=60']);
        }

        $stmt = $pdo->prepare("
            SELECT p.id, p.content, p.created_at, p.updated_at,
                   u.username, u.id AS user_id
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            Response::json(['error' => 'Postingan tidak ditemukan'], 404, ['Cache-Control' => 'public, max-age=60']);
        }

        $owner = false;
        $token = JwtService::bearerToken();
        if ($token) {
            $decoded = JwtService::verify($token);
            if ($decoded && $decoded->data->user_id == $post['user_id']) {
                $owner = true;
            }
        }

        Response::json([
            'post' => [
                'id' => $post['id'],
                'user_id' => $post['user_id'],
                'username' => $post['username'],
                'content' => $post['content'],
                'is_owner' => $owner,
                'created_at' => $post['created_at'],
                'updated_at' => $post['updated_at']
            ]
        ], 200, ['Cache-Control' => 'public, max-age=60']);
    }

    public function updatePost($id = null): void
    {
        $pdo = Database::connection();
        $uid = $this->auth();

        if (!$id || $id <= 0) {
            Response::json(['error' => 'ID Postingan tidak valid'], 400);
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $content = trim($body['content'] ?? '');

        if (!$content) {
            Response::json(['error' => 'Konten postingan tidak boleh kososng'], 400);
        }

        $stmt = $pdo->prepare("SELECT id, user_id FROM posts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            Response::json(['error' => 'Postingan tidak ditemukan'], 404);
        }

        if ((int)$post['user_id'] !== $uid) {
            Response::json(['error' => 'Tidak punya akses untuk mengubah postingan ini'], 403);
        }

        $stmt = $pdo->prepare("UPDATE posts SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$content, $id]);

        Response::json(['message' => 'Postingan berhasil diperbarui'], 200);
    }

    public function deletePost($id = null): void
    {
        $pdo = Database::connection();
        $uid = $this->auth();

        if ($id <= 0) {
            Response::json(['error' => 'ID Postingan tidak valid'], 400);
        }

        $stmt = $pdo->prepare("SELECT id, user_id FROM posts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            Response::json(['error' => 'Postingan tidak ditemukan'], 404);
        }

        if ((int)$post['user_id'] !== $uid) {
            Response::json(['error' => 'Tidak punya akses untuk menghapus postingan ini'], 403);
        }

        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);

        Response::json(['message' => 'Postingan berhasil dihapus'], 200);
    }
}
