<?php

namespace App\Features\Auth;

use App\Database\Database;
use App\Helpers\Response;
use App\Services\JwtService;

if ($_ENV['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

class AuthController
{
    public function register(): void
    {
        $pdo = Database::connection();
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$username || !$email || !$password) {
            Response::json(['error' => 'Silakan isi semua input terlebih dulu'], 400);
        }

        if (mb_strlen($username) > 50) {
            Response::json([
                'error' => 'Username maksimal 50 karakter'
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            Response::json([
                'error' => 'Username hanya boleh huruf, angka, dan underscore'
            ], 400);
        }

        if (mb_strlen($email) > 150) {
            Response::json(['error' => 'Email terlalu panjang (maks. 150 karakter)'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['error' => 'Format email tidak valid'], 400);
        }

        if (mb_strlen($password) < 6) {
            Response::json(['error' => 'Password terlalu pendek (min. 6 karakter)'], 400);
        }

        if (mb_strlen($password) > 72) {
            Response::json(['error' => 'Password terlalu panjang (maks. 72 karakter)'], 400);
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            Response::json(['error' => 'Username sudah terdaftar'], 409);
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            Response::json(['error' => 'Email sudah terdaftar'], 409);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
        $stmt->execute([$username, $email, $hash]);

        Response::json(['message' => 'Berhasil terdaftar'], 201);
    }

    public function login(): void
    {
        $pdo = Database::connection();
        $data = json_decode(file_get_contents('php://input'), true);
        $user = trim($data['identifier'] ?? '');
        $pass = $data['password'] ?? '';

        if (!$user || !$pass) {
            Response::json(['error' => 'Silakan isi semua input terlebih dulu'], 400);
        }

        $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$user, $user]);
        $row = $stmt->fetch();

        if (!$row) {
            Response::json(['error' => 'Username atau email salah'], 401);
        }

        if (!password_verify($pass, $row['password'])) {
            Response::json(['error' => 'Password salah'], 401);
        }

        $token = JwtService::generate([
            'user_id' => $row['id'],
            'username' => $row['username']
        ]);

        Response::json([
            'message' => 'Berhasil login',
            'token' => $token
        ], code: 200);
    }

    public function logout(): void
    {
        Response::json(['message' => 'Berhasil logout'], code: 200);
    }
}
