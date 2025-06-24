<?php
declare(strict_types=1);

// require 'MetaController.php';

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
   
    // Suspect profile route
    $r->addRoute('GET',    '/crime_api/suspect',           'getAllSuspects');
    $r->addRoute('POST',   '/crime_api/suspect',           'createSuspect');
    $r->addRoute('GET',    '/crime_api/suspect/{id:\d+}',  'getSuspect');
    $r->addRoute('PATCH',  '/crime_api/suspect/{id:\d+}',  'updateSuspect');
    $r->addRoute('DELETE', '/crime_api/suspect/{id:\d+}',  'deleteSuspect');
    $r->addRoute('GET', '/crime_api/suspects/search', 'SearchSuspect');
    $r->addRoute('GET', '/crime_api/suspects', 'SearchSuspect');



    // Suspect Build Case route
    $r->addRoute('GET',    '/crime_api/cases',           'getAllCases');
    $r->addRoute('POST',   '/crime_api/case',           'createCase');
    // $r->addRoute('GET',    '/crime_api/suspect/{id:\d+}',  'getSuspect');
    // $r->addRoute('PATCH',  '/crime_api/suspect/{id:\d+}',  'updateSuspect');
    // $r->addRoute('DELETE', '/crime_api/suspect/{id:\d+}',  'deleteSuspect');

 // Request and Aprroval route
    $r->addRoute('POST', '/crime_api/access-request',  'createAccessRequest');
    $r->addRoute('GET', '/crime_api/access-requests', 'getAllRequests');
    $r->addRoute('PATCH', '/crime_api/access-request/{id:\d+}', 'updateRequestStatus');





//LATER RENAME THIS ENDPOINT SPECIFIC NAME LIKE knownReporterRegistration, knownReporterLogin, knownReporterLogout, knownReporterProfile
    
// Known Reporter Auth routes
    $r->addRoute('POST', '/crime_api/register[/]', 'register');
    $r->addRoute('POST', '/crime_api/login[/]', 'login');
    $r->addRoute('POST', '/crime_api/logout[/]', 'logout');
    $r->addRoute('GET', '/crime_api/profile[/]', 'profile'); // use this for known reporter dashboard
      
    
    // Officer Auth routes
    $r->addRoute('POST', '/crime_api/officers[/]', 'registerOfficer');
    $r->addRoute('GET', '/crime_api/officers', 'getAllOfficers');

    //Get dynamic Status: roles, stauses, agencies

    $r->addRoute('GET', '/crime_api/dropdowns/statuses', [DropdownController::class, 'getStatuses']);
    $r->addRoute('GET', '/crime_api/dropdowns/roles', [DropdownController::class, 'getRoles']);
    $r->addRoute('GET', '/crime_api/dropdowns/agencies', [DropdownController::class, 'getAgencies']);
    $r->addRoute('GET', '/crime_api/dropdowns/zones', [DropdownController::class, 'getZones']);
    $r->addRoute('GET', '/crime_api/dropdowns/states', [DropdownController::class, 'getStates']);
    $r->addRoute('GET', '/crime_api/dropdowns/lgas', [DropdownController::class, 'getLgas']);
    $r->addRoute('GET', '/crime_api/dropdowns/divisions', [DropdownController::class, 'getDivisions']);
    // $r->addRoute('GET', '/crime_api/meta/roles', 'getRoles');
    // $r->addRoute('GET', '/crime_api/meta/statuses', 'getStatuses');
    // $r->addRoute('GET', '/crime_api/meta/agencies', 'getAgencies');



    // $r->addRoute('POST', '/crime_api/login[/]', 'login');
    // $r->addRoute('POST', '/crime_api/logout[/]', 'logout');
    // $r->addRoute('GET', '/crime_api/profile[/]', 'profile'); // use this for known reporter dashboard

   
    // $r->addRoute('POST', '/crime_api/verify-otp', ['AuthController', 'verifyOTP']);
    // $r->addRoute('POST', '/crime_api/resend-otp', ['AuthController', 'resendOTP']);
    // $r->addRoute('POST', '/crime_api/send-reset-email', [AuthController::class, 'sendResetEmail']);
    

    $r->addRoute('POST', '/crime_api/send-reset-email', [AuthController::class, 'sendResetEmail']);
    $r->addRoute('POST', '/crime_api/verify-otp', [AuthController::class, 'verifyOTP']);
    $r->addRoute('POST', '/crime_api/reset-password', [AuthController::class, 'resetPassword']);
    $r->addRoute('POST', '/crime_api/resend-otp', [AuthController::class, 'resendOtp']);

    //Goe routes
    $r->addRoute('GET', '/crime_api/agencies', 'geo');
    $r->addRoute('GET', '/crime_api/zones', 'geo');
    $r->addRoute('GET', '/crime_api/states', 'geo');
    $r->addRoute('GET', '/crime_api/lgas', 'geo');
    $r->addRoute('GET', '/crime_api/divisions', 'geo');

    
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
$conn = $database->getConnection();
$dropdownController = new DropdownController($conn);

//GLOBAL CONNECTION
// $conn = $database->getConnection(); 

$knownGateway = new KnownGateway($database);
$knownController = new KnownController($knownGateway);

$anonymousGateway = new AnonymousGateway($database);
$anonymousController = new AnonymousController($anonymousGateway);

$suspectGateway = new SuspectGateway($database);
$suspectController = new SuspectController($suspectGateway);

$suspectSearchGateway = new SuspectSearchGateway($database);
$suspectSearchController = new SuspectSearchController($suspectSearchGateway);

$caseGateway = new CaseGateway($database);
$caseController = new CaseController($caseGateway);

$accessRequestGateway = new AccessRequestGateway($database);
$accessRequestController = new AccessRequestController($accessRequestGateway);


$userGateway = new UserGateway($database);
$authController = new AuthController($userGateway);

$officerGateway = new OfficerGateway($database);
$officerAuthController = new OfficerAuthController($officerGateway);

$geoGateway = new GeoGateway($database);
$geoController = new GeoController($geoGateway);

// $dropdownController = new DropdownController($database->getConnection());


// $metaController = new MetaController($pdo);


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

        // if (is_array($handler)) {
        //     [$className, $method] = $handler;
        //     $controller = new $className(new Database("localhost", "crime_db", "root", "")); // adjust if needed
        //     call_user_func([$controller, $method], ...array_values($vars));
        //     break;
        // }   

switch ($handler) {

        //Get all
    case 'getAllKnownUserReport':
        $knownController->processRequest('GET', null);
        break;

    case 'getAllAnonymous':
        $anonymousController->processRequest('GET', null);
        break;

    case 'getAllSuspects':
        $suspectController->processRequest('GET', null);
        break;

    case 'SearchSuspect':
        $suspectSearchController->processRequest('GET', null);
        break;

  
    case 'getAllCases':
        $accessRequestController->processRequest('GET', null);
        break;


    case 'getAllRequests':
        $accessRequestController->processRequest('GET', null);
        break;

    case 'getAllOfficers':
        $officerAuthController->getAllOfficers();
        break;



        case 'getRoles':
            $metaController->getRoles();
            break;
        
        case 'getStatuses':
            $metaController->getStatuses();
            break;
        
        case 'getAgencies':
            $metaController->getAgencies();
            
            break;
        
        case 'geo':
            $geoController->processRequest($httpMethod, null);
            break;
        
        //Post
    case 'createKnown':
        $knownController->processRequest('POST', null);
        break;

    case 'createAnonymous':
        $anonymousController->processRequest('POST', null);
        break;

    case 'createSuspect':
        $suspectController->processRequest('POST', null);
        break;

    case 'createCase':
        $caseController->processRequest('POST', null);
        break;

    case 'createAccessRequest':
        $accessRequestController->processRequest('POST', null);
        break;

    // case 'registerOfficer':
    //     $accessRequestController->processRequest('POST', null);
    //     break;


   
    
// //AuthController 
//     case [AuthController::class, 'sendResetEmail']:
//         $authController->sendResetEmail();
//         break;

//     case [AuthController::class, 'verifyOTP']:
//         $authController->verifyOTP();
//         break;
    
//     case [AuthController::class, 'resetPassword']:
//         $authController->resetPassword();
//         break;
    
//     case [AuthController::class, 'resendOtp']:
//         $authController->resendOtp();
//         break;
        
        //Get by id        
    case 'getKnown':
        $knownController->processRequest('GET', $vars['id']);
        break;

    case 'getAnonymous':
        $anonymousController->processRequest('GET', $vars['id']);
        break;

    case 'getSuspect':
        $suspectController->processRequest('GET', $vars['id']);
        break;

        // Update by id
    case 'updateKnown':
        $knownController->processRequest('PATCH', $vars['id']);
        break;

    case 'updateAnonymous':
        $anonymousController->processRequest('PATCH', $vars['id']);
        break;

    case 'updateSuspect':
        $suspectController->processRequest('PATCH', $vars['id']);
        break;

    case 'updateRequestStatus':
        $accessRequestController->processRequest('PATCH', $vars['id']);
        break;


        //Delete by id
    case 'deleteKnown':
        $knownController->processRequest('DELETE', $vars['id']);
        break;

    case 'deleteAnonymous':
        $anonymousController->processRequest('DELETE', $vars['id']);
        break;

    case 'deleteSuspect':
        $suspectController->processRequest('DELETE', $vars['id']);
        break;

        //Registration

    case 'register':
        $authController->register();
        break;

    case 'registerOfficer':
        $officerAuthController->registerOfficer();
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
        
    //AuthController 
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



    // case [DropdownController::class, 'getStatuses']:
    //     $controller->getStatuses();
    //     break;
    // case [DropdownController::class, 'getRoles']:
    //     $controller->getRoles();
    //     break;
           
    // case [DropdownController::class, 'getAgencies']:
    //     $controller->getAgencies();
    //     break;
    // case [DropdownController::class, 'getZones']:
    //     $controller->getZones();
    //     break;
           
    // case [DropdownController::class, 'getStates']:
    //     $controller->getStates();
    //     break;
    // case [DropdownController::class, 'getLgas']:
    //     $controller->getLgas();
    //     break;
    // case [DropdownController::class, 'getDivisions']:
    //     $controller->getDivisions();
    //     break;


    
    // case [DropdownController::class, 'getStatuses']:
    //     $dropdownController->getStatuses();
    //     break;
    
    // case [DropdownController::class, 'getRoles']:
    //     $dropdownController->getRoles();
    //     break;
    
    // case [DropdownController::class, 'getAgencies']:
    //     $dropdownController->getAgencies();
    //     break;
    
    // case [DropdownController::class, 'getZones']:
    //     $dropdownController->getZones();
    //     break;
    
    // case [DropdownController::class, 'getStates']:
    //     $dropdownController->getStates();
    //     break;
    
    // case [DropdownController::class, 'getLgas']:
    //     $dropdownController->getLgas();
    //     break;
    
    // case [DropdownController::class, 'getDivisions']:
    //     $dropdownController->getDivisions();
    //     break;
    
    // DropdownController routes
case [DropdownController::class, 'getStatuses']:
    $dropdownController->getStatuses();
    break;

case [DropdownController::class, 'getRoles']:
    $dropdownController->getRoles();
    break;

case [DropdownController::class, 'getAgencies']:
    $dropdownController->getAgencies();
    break;

case [DropdownController::class, 'getZones']:
    $dropdownController->getZones();
    break;

case [DropdownController::class, 'getStates']:
    $dropdownController->getStates();
    break;

case [DropdownController::class, 'getLgas']:
    $dropdownController->getLgas();
    break;

case [DropdownController::class, 'getDivisions']:
    $dropdownController->getDivisions();
    break;

        

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown route']);
        break;
}
break;
}
