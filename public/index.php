<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\Controller;
use App\Auth;
use App\Storage;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
    'auto_reload' => true
]);

// Add global functions for authentication state
$twig->addFunction(new \Twig\TwigFunction('isAuthenticated', function() {
    return isset($_SESSION['user']);
}));
$twig->addGlobal('user', $_SESSION['user'] ?? null);

// Initialize dependencies
$auth = new Auth();
$storage = new Storage();
$controller = new Controller($twig, $auth, $storage);

// Simple router
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Flash messages
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$twig->addGlobal('flash', $flash);

// Routes
switch ($path) {
    case '/':
        $controller->landing();
        break;
    
    case '/auth/login':
        if ($method === 'POST') {
            $controller->handleLogin();
        } else {
            $controller->login();
        }
        break;
        
    case '/auth/signup':
        if ($method === 'POST') {
            $controller->handleSignup();
        } else {
            $controller->signup();
        }
        break;
        
    case '/auth/logout':
        $controller->handleLogout();
        break;
        
    case '/dashboard':
        $controller->requireAuth();
        $controller->dashboard();
        break;
        
    case '/tickets':
        $controller->requireAuth();
        if ($method === 'POST') {
            $controller->handleTickets();
        } else {
            $controller->tickets();
        }
        break;
        
    default:
        http_response_code(404);
        echo $twig->render('404.twig');
        break;
}