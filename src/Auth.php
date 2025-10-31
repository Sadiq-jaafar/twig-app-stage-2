<?php
namespace App;

class Auth {
    private string $usersFile;
    private const SESSION_DURATION = 21600; // 6 hours in seconds

    public function __construct() {
        $this->usersFile = __DIR__ . '/../data/users.json';

        // Ensure data directory exists and is writable
        $dataDir = dirname($this->usersFile);
        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        // Ensure users file exists
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, '[]');
        }

        // Make sure file is writable
        if (!is_writable($this->usersFile)) {
            chmod($this->usersFile, 0666);
        }

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getUsers(): array {
        $content = @file_get_contents($this->usersFile);
        return $content ? (json_decode($content, true) ?? []) : [];
    }

    private function saveUsers(array $users): void {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function createUser(array $userData): array {
        $users = $this->getUsers();

        // Validate email
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Valid email required');
        }

        // Check if email exists
        if (array_filter($users, fn($u) => $u['email'] === $userData['email'])) {
            throw new \Exception('Email already registered');
        }

        // Validate password
        if (empty($userData['password']) || strlen($userData['password']) < 6) {
            throw new \Exception('Password must be at least 6 characters long');
        }

        $user = [
            'id' => 'u_' . bin2hex(random_bytes(8)),
            'name' => $userData['name'] ?: explode('@', $userData['email'])[0],
            'email' => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'createdAt' => time()
        ];

        $users[] = $user;
        $this->saveUsers($users);

        unset($user['password']);
        return $user;
    }

    public function login(array $credentials): array {
        $users = $this->getUsers();
        $user = array_filter($users, fn($u) => $u['email'] === $credentials['email']);

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        $user = reset($user);

        if (!password_verify($credentials['password'], $user['password'])) {
            throw new \Exception('Invalid credentials');
        }

        // Create session
        unset($user['password']);
        $_SESSION['user'] = $user;
        $_SESSION['expires'] = time() + self::SESSION_DURATION;

        return $user;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public function isAuthenticated(): bool {
        if (!isset($_SESSION['user'])) {
            return false;
        }

        if (!isset($_SESSION['expires']) || $_SESSION['expires'] <= time()) {
            unset($_SESSION['user'], $_SESSION['expires']);
            return false;
        }

        return true;
    }

    public function requireAuth(): void {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash'] = ['message' => 'Please log in first', 'type' => 'error'];
            header('Location: /auth/login');
            exit;
        }
    }

    public function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function setFlash(string $message, string $type = 'info'): void {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }

    public function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
