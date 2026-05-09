<?php
// Main API Router
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/middleware/cors_middleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers/response.php';

// Parse request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Remove query string from URI
$request_uri = strtok($request_uri, '?');

// Remove base path from request URI
$request_path = str_replace($base_path, '', $request_uri);
if (empty($request_path) || $request_path === '/') {
    ApiResponse::error('API endpoint required', 400);
}

// Extract API version and path
if (strpos($request_path, '/api/v1/') === 0) {
    $request_path = str_replace('/api/v1/', '', $request_path);
    $version = 'v1';
} else {
    ApiResponse::error('Invalid API version', 400);
}

// Split path into segments
$segments = array_filter(explode('/', trim($request_path, '/')));
$segments = array_values($segments);

$method = $_SERVER['REQUEST_METHOD'];

// Routing logic
$route_found = false;
$route_file = null;

// Handle auth routes
if (isset($segments[0]) && $segments[0] === 'auth') {
    if (isset($segments[1]) && in_array($segments[1], ['login', 'register'])) {
        $route_file = __DIR__ . '/v1/auth/' . $segments[1] . '.php';
        $route_found = true;
    }
}

// Handle businesses routes
elseif (isset($segments[0]) && $segments[0] === 'businesses') {
    if (count($segments) === 1 && in_array($method, ['GET', 'POST'])) {
        $route_file = __DIR__ . '/v1/businesses/index.php';
        $route_found = true;
    } elseif (count($segments) === 2 && in_array($method, ['GET', 'PUT', 'DELETE'])) {
        $_GET['id'] = $segments[1];
        $route_file = __DIR__ . '/v1/businesses/single.php';
        $route_found = true;
    }
}

// Handle investments routes
elseif (isset($segments[0]) && $segments[0] === 'investments') {
    if (count($segments) === 1 && in_array($method, ['GET', 'POST'])) {
        $route_file = __DIR__ . '/v1/investments/index.php';
        $route_found = true;
    } elseif (count($segments) === 2 && in_array($method, ['GET', 'PUT', 'DELETE'])) {
        $_GET['id'] = $segments[1];
        $route_file = __DIR__ . '/v1/investments/single.php';
        $route_found = true;
    }
}

// Handle inventory routes
elseif (isset($segments[0]) && $segments[0] === 'inventory') {
    if (isset($segments[1]) && $segments[1] === 'low_stock') {
        $route_file = __DIR__ . '/v1/inventory/low_stock.php';
        $route_found = true;
    } elseif (count($segments) === 1 && in_array($method, ['GET', 'POST'])) {
        $route_file = __DIR__ . '/v1/inventory/index.php';
        $route_found = true;
    } elseif (count($segments) === 2 && in_array($method, ['GET', 'PUT', 'DELETE'])) {
        $_GET['id'] = $segments[1];
        $route_file = __DIR__ . '/v1/inventory/single.php';
        $route_found = true;
    }
}

// Handle sales routes
elseif (isset($segments[0]) && $segments[0] === 'sales') {
    if (count($segments) === 1 && in_array($method, ['GET', 'POST'])) {
        $route_file = __DIR__ . '/v1/sales/index.php';
        $route_found = true;
    } elseif (count($segments) === 2 && in_array($method, ['GET'])) {
        $_GET['id'] = $segments[1];
        $route_file = __DIR__ . '/v1/sales/single.php';
        $route_found = true;
    }
}

// Handle profits routes
elseif (isset($segments[0]) && $segments[0] === 'profits') {
    if (count($segments) === 1 && $method === 'GET') {
        $route_file = __DIR__ . '/v1/profits/index.php';
        $route_found = true;
    }
}

// Handle reports routes
elseif (isset($segments[0]) && $segments[0] === 'reports') {
    if (isset($segments[1]) && in_array($segments[1], ['sales', 'inventory', 'profit'])) {
        if ($method === 'GET') {
            $route_file = __DIR__ . '/v1/reports/' . $segments[1] . '.php';
            $route_found = true;
        }
    }
}

// If route found, include and execute
if ($route_found && file_exists($route_file)) {
    include $route_file;
} else {
    ApiResponse::error('Endpoint not found', 404);
}