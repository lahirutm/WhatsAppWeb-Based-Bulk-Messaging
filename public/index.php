<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Controllers/InstanceController.php';
require_once __DIR__ . '/../src/Controllers/MessageController.php';
require_once __DIR__ . '/../src/Controllers/BulkController.php';
require_once __DIR__ . '/../src/Controllers/HistoryController.php';
require_once __DIR__ . '/../src/Controllers/ApiSettingsController.php';
require_once __DIR__ . '/../src/Controllers/ApiController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple Router
if ($uri === '/' || $uri === '/login') {
    $controller = new AuthController();
    if ($method === 'POST') {
        $controller->login();
    } else {
        $controller->showLogin();
    }
} elseif ($uri === '/register') {
    $controller = new AuthController();
    if ($method === 'POST') {
        $controller->register();
    } else {
        $controller->showRegister();
    }
} elseif ($uri === '/logout') {
    $controller = new AuthController();
    $controller->logout();
} elseif ($uri === '/dashboard') {
    $controller = new DashboardController();
    $controller->index();
} elseif ($uri === '/link-account') {
    $controller = new InstanceController();
    $controller->showLink();

} elseif ($uri === '/disconnect') {
    $controller = new InstanceController();
    $controller->disconnect();

} elseif ($uri === '/send-message') {
    $controller = new MessageController();
    if ($method === 'POST') {
        $controller->send();
    } else {
        $controller->showSend();
    }
} elseif ($uri === '/bulk-send') {
    $controller = new BulkController();
    if ($method === 'POST') {
        $controller->processBulkSend();
    } else {
        $controller->showBulkSend();
    }
} elseif ($uri === '/batch-status') {
    $controller = new BulkController();
    $controller->showBatchStatus();
} elseif ($uri === '/history/individual') {
    $controller = new HistoryController();
    $controller->showIndividual();
} elseif ($uri === '/history/bulk') {
    $controller = new HistoryController();
    $controller->showBulk();
} elseif ($uri === '/api-settings') {
    $controller = new ApiSettingsController();
    $controller->index();
} elseif ($uri === '/api-settings/generate') {
    $controller = new ApiSettingsController();
    $controller->generateKey();
} elseif ($uri === '/api/send') {
    $controller = new ApiController();
    $controller->send();
} elseif ($uri === '/users') {
    $controller = new UserController();
    $controller->index();
} elseif ($uri === '/users/create') {
    $controller = new UserController();
    $controller->create();
} elseif ($uri === '/users/store' && $method === 'POST') {
    $controller = new UserController();
    $controller->store();
} elseif (preg_match('#^/users/toggle/(\d+)$#', $uri, $matches)) {
    $controller = new UserController();
    $controller->toggleStatus($matches[1]);
} elseif (preg_match('#^/users/password/(\d+)$#', $uri, $matches)) {
    $controller = new UserController();
    if ($method === 'POST') {
        $controller->updatePassword($matches[1]);
    } else {
        $controller->editPassword($matches[1]);
    }
} elseif (preg_match('#^/users/credits/(\d+)$#', $uri, $matches)) {
    $controller = new UserController();
    if ($method === 'POST') {
        $controller->updateCredits($matches[1]);
    } else {
        $controller->editCredits($matches[1]);
    }
} else {
    http_response_code(404);
    echo "404 Not Found";
}
