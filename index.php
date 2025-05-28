<?php
declare(strict_types=1);

// Error reporting (for development)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true"); // <-- This enables cookies!
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Respond to preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload classes
require __DIR__ . "/vendor/autoload.php"; // FastRoute via Composer
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

// Error handlers
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

header("Content-Type: application/json; charset=UTF-8");

// Create router
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
     // Known reporter routes
    $r->addRoute('GET',    '/crime_api/known',           'getAllKnownUserReport');
    $r->addRoute('POST',   '/crime_api/known',           'createKnown');

    // $r->addRoute('GET',    '/crime_api/known/{track_id:\d+}',  'getKnownReports');

    $r->addRoute('GET',    '/crime_api/known/{id:\d+}',  'getKnown');
    $r->addRoute('PATCH',  '/crime_api/known/{id:\d+}',  'updateKnown');
    $r->addRoute('DELETE', '/crime_api/known/{id:\d+}',  'deleteKnown');


    // Anonymous reporter route
    $r->addRoute('GET',    '/crime_api/anonymous',           'getAllAnonymous');
    $r->addRoute('POST',   '/crime_api/anonymous',           'createAnonymous');
    $r->addRoute('GET',    '/crime_api/anonymous/{id:\d+}',  'getAnonymous');
    $r->addRoute('PATCH',  '/crime_api/anonymous/{id:\d+}',  'updateAnonymous');
    $r->addRoute('DELETE', '/crime_api/anonymous/{id:\d+}',  'deleteAnonymous');





      // Auth routes
    $r->addRoute('POST', '/crime_api/register[/]', 'register');
    $r->addRoute('POST', '/crime_api/login[/]', 'login');
    $r->addRoute('POST', '/crime_api/logout[/]', 'logout');
    $r->addRoute('GET', '/crime_api/profile[/]', 'profile'); // use this for known reporter dashboard

   
    // $r->addRoute('POST', '/crime_api/verify-otp', ['AuthController', 'verifyOTP']);
    // $r->addRoute('POST', '/crime_api/resend-otp', ['AuthController', 'resendOTP']);
    // $r->addRoute('POST', '/crime_api/send-reset-email', [AuthController::class, 'sendResetEmail']);
    

    $r->addRoute('POST', '/crime_api/send-reset-email', [AuthController::class, 'sendResetEmail']);
    $r->addRoute('POST', '/crime_api/verify-otp', [AuthController::class, 'verifyOTP']);
    $r->addRoute('POST', '/crime_api/reset-password', [AuthController::class, 'resetPassword']);
    $r->addRoute('POST', '/crime_api/resend-otp', [AuthController::class, 'resendOtp']);

    
    // $r->addRoute('POST', '/crime_api/send-reset-email', 'sendResetEmail');
    // $r->addRoute('POST', '/crime_api/verify-otp', 'verifyOTP');
    // $r->addRoute('POST', '/crime_api/resend-otp', 'resendOTP');

    // // --Add this for the final password reset form (after OTP is verified)
    // $r->addRoute('POST', '/crime_api/reset-password', 'resetPassword');

    });

// Extract request method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Dispatch route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// Common dependencies
$database = new Database("localhost", "crime_db", "root", "");
$knownGateway = new KnownGateway($database);
$knownController = new KnownController($knownGateway);

$anonymousGateway = new AnonymousGateway($database);
$anonymousController = new AnonymousController($anonymousGateway);


$userGateway = new UserGateway($database);
$authController = new AuthController($userGateway);

// Handle routes
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        

switch ($handler) {
    case 'getAllKnownUserReport':
        $knownController->processRequest('GET', null);
        break;

    case 'getAllAnonymous':
        $anonymousController->processRequest('GET', null);
        break;

    case 'createKnown':
        $knownController->processRequest('POST', null);
        break;

    case 'createAnonymous':
        $anonymousController->processRequest('POST', null);
        break;

    case [AuthController::class, 'sendResetEmail']:
        $authController->sendResetEmail();
        break;

    case [AuthController::class, 'verifyOTP']:
        $authController->verifyOTP();
        break;
    
    case [AuthController::class, 'resetPassword']:
        $authController->resetPassword();
        break;
    
    case [AuthController::class, 'resendOtp']:
        $authController->resendOtp();
        break;
        
            
    case 'getKnown':
        $knownController->processRequest('GET', $vars['id']);
        break;

    case 'getAnonymous':
        $anonymousController->processRequest('GET', $vars['id']);
        break;

    case 'updateKnown':
        $knownController->processRequest('PATCH', $vars['id']);
        break;

    case 'updateAnonymous':
        $anonymousController->processRequest('PATCH', $vars['id']);
        break;

    case 'deleteKnown':
        $knownController->processRequest('DELETE', $vars['id']);
        break;

    case 'deleteAnonymous':
        $anonymousController->processRequest('DELETE', $vars['id']);
        break;

    case 'register':
        $authController->register();
        break;

    case 'login':
        $authController->login();
        break;

    case 'logout':
        session_start();
        $_SESSION = [];
        session_destroy();
        echo json_encode(["message" => "Logged out"]);
        break;

    case 'profile':
        $authController->profile();
        break;
        
        
        

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown route']);
        break;
}
break;
}
