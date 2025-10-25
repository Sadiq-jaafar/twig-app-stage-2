<?php
namespace App;

class Controller {
    private \Twig\Environment $twig;
    private Auth $auth;
    private Storage $storage;

    public function __construct(\Twig\Environment $twig, Auth $auth, Storage $storage) {
        $this->twig = $twig;
        $this->auth = $auth;
        $this->storage = $storage;
    }

    public function landing(): void {
        if ($this->auth->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        echo $this->twig->render('landing.twig');
    }

    public function login(): void {
        if ($this->auth->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        echo $this->twig->render('auth/login.twig', [
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ]);
        unset($_SESSION['errors'], $_SESSION['old']);
    }

    public function handleLogin(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (!$email) {
            $errors['email'] = 'Email is required';
        }
        if (!$password) {
            $errors['password'] = 'Password is required';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

        try {
            $this->auth->login([
                'email' => $email,
                'password' => $password
            ]);
            $this->auth->setFlash('Login successful', 'success');
            header('Location: /dashboard');
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['form' => $e->getMessage()];
            $_SESSION['old'] = ['email' => $email];
            header('Location: /auth/login');
        }
        exit;
    }

    public function signup(): void {
        if ($this->auth->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        echo $this->twig->render('auth/signup.twig', [
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old'] ?? []
        ]);
        unset($_SESSION['errors'], $_SESSION['old']);
    }

    public function handleSignup(): void {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (!$name) {
            $errors['name'] = 'Name required';
        }
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email required';
        }
        if (!$password || strlen($password) < 6) {
            $errors['password'] = 'Password must be >= 6 chars';
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            header('Location: /auth/signup');
            exit;
        }

        try {
            $user = $this->auth->createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);
            $_SESSION['user'] = $user;
            $this->auth->setFlash('Account created — logged in', 'success');
            header('Location: /dashboard');
        } catch (\Exception $e) {
            $_SESSION['errors'] = ['form' => $e->getMessage()];
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            header('Location: /auth/signup');
        }
        exit;
    }

    public function handleLogout(): void {
        $this->auth->logout();
        $this->auth->setFlash('Logged out successfully');
        header('Location: /');
        exit;
    }

    public function requireAuth(): void {
        $this->auth->requireAuth();
    }

    public function dashboard(): void {
        $user = $this->auth->getCurrentUser();
        $stats = $this->storage->getTicketStats($user['id']);
        echo $this->twig->render('dashboard.twig', [
            'stats' => $stats
        ]);
    }

    public function tickets(): void {
        $user = $this->auth->getCurrentUser();
        $tickets = $this->storage->loadTickets($user['id']);
        echo $this->twig->render('tickets.twig', [
            'tickets' => $tickets
        ]);
    }

    public function handleTickets(): void {
        $user = $this->auth->getCurrentUser();
        $method = $_POST['_method'] ?? 'POST';
        
        try {
            switch ($method) {
                case 'POST':
                    $ticket = $this->storage->addTicket($user['id'], [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'status' => $_POST['status']
                    ]);
                    $this->auth->setFlash('Ticket created', 'success');
                    break;

                case 'PUT':
                    $ticket = $this->storage->updateTicket($user['id'], $_POST['ticketId'], [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'status' => $_POST['status']
                    ]);
                    $this->auth->setFlash('Ticket updated', 'success');
                    break;

                case 'DELETE':
                    $this->storage->deleteTicket($user['id'], $_POST['ticketId']);
                    $this->auth->setFlash('Ticket deleted', 'success');
                    break;
            }
        } catch (\Exception $e) {
            $this->auth->setFlash($e->getMessage(), 'error');
        }

        header('Location: /tickets');
        exit;
    }
}