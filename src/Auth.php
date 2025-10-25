<?php
namespace App;

class Auth {
    private string $usersFile;
    private const SESSION_DURATION = 21600; // 6 hours in seconds

    public function __construct() {
        $this->usersFile = __DIR__ . '/../data/users.json';
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, '[]');
        }
    }

    private function getUsers(): array {
        return json_decode(file_get_contents($this->usersFile), true) ?? [];
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
            throw new \Exception('Password must be >= 6 chars');
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

        // Don't return password hash
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
        session_destroy();
    }

    public function isAuthenticated(): bool {
        return isset($_SESSION['user']) && $_SESSION['expires'] > time();
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
}