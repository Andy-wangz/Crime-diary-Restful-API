<?php
// require_once 'AuditLogger.php';
// require_once 'RolePermissionGateway.php';

// class RolePermissionController
// {
//     private AuditLogger $logger;

//     public function __construct(private RolePermissionGateway $gateway)
//     {
//         $this->logger = new AuditLogger($this->gateway->getConnection());
//     }

//     public function processRequest(string $method, ?string $id): void
//     {
//         if ($id) {
//             $this->processResourceRequest($method, $id);
//         } else {
//             $this->processCollectionRequest($method);
//         }
//     }

//     private function processResourceRequest(string $method, string $id): void
//     {
//         http_response_code(405);
//         echo json_encode(["error" => "Method not allowed on individual role/permission resource"]);
//     }

//     private function processCollectionRequest(string $method): void
//     {
//         session_start();
//         $officer = $_SESSION['username'] ?? 'unknown';
//         $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
//         $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

//         switch ($method) {
//             case "POST":
//                 $data = (array) json_decode(file_get_contents("php://input"), true);
//                 $errors = $this->getValidationErrors($data);

//                 if (!empty($errors)) {
//                     http_response_code(422);
//                     echo json_encode(["errors" => $errors]);
//                     return;
//                 }

//                 if (isset($data['name']) && isset($_GET['type'])) {
//                     $name = trim($data['name']);
//                     $type = $_GET['type'];

//                     if ($type === 'role') {
//                         $success = $this->gateway->addOfficerRole($name);
//                         $this->logger->log(
//                             'roles',
//                             0,
//                             'create',
//                             $officer,
//                             "Created new role ($name)",
//                             $currentUrl
//                         );
//                     } elseif ($type === 'permission') {
//                         $success = $this->gateway->addOfficerPermission($name);
//                         $this->logger->log(
//                             'permissions',
//                             0,
//                             'create',
//                             $officer,
//                             "Created new permission ($name)",
//                             $currentUrl
//                         );
//                     } else {
//                         http_response_code(400);
//                         echo json_encode(["error" => "Invalid type: must be 'role' or 'permission'"]);
//                         return;
//                     }

//                     if ($success) {
//                         http_response_code(201);
//                         echo json_encode(["message" => ucfirst($type) . " '$name' added successfully"]);
//                     } else {
//                         http_response_code(500);
//                         echo json_encode(["error" => "Failed to insert $type"]);
//                     }
//                 } else {
//                     http_response_code(400);
//                     echo json_encode(["error" => "Missing 'name' or 'type' parameter"]);
//                 }
//                 break;

//             default:
//                 http_response_code(405);
//                 header("Allow: POST");
//                 echo json_encode(["error" => "Method $method not allowed"]);
//                 break;
//         }
//     }

//     private function getValidationErrors(array $data): array
//     {
//         $errors = [];

//         if (empty($data['name'])) {
//             $errors[] = "Name is required";
//         } elseif (strlen($data['name']) < 3) {
//             $errors[] = "Name must be at least 3 characters";
//         }

//         return $errors;
//     }
// }



require_once 'AuditLogger.php';
require_once 'SuspectGateway.php';

class RolePermissionController {

    private AuditLogger $logger;

    public function __construct(private RolePermissionGateway $gateway) {
        // SuspectGateway::getConnection() must return a PDO instance
        $this->logger = new AuditLogger($this->gateway->getConnection());  
      }

    public function processRequest(string $method, ?string $id) {
        if ($id){
            $this->processResourceRequest($method, $id);

        }
        else {
            $this->processCollectionRequest($method);

        }

    }

    //NOTE: By ID, url with product/id for GET, PATCH(UPDATE) and DELETE
    private function processResourceRequest(string $method, string $id) {
        
        // $suspect = $this->gateway->getSuspect($id);
        

        // if (! $suspect) {
        //     http_response_code(404);

        //     echo json_encode(["message" => "Record not found"]);

        //     return;
        // }

        switch($method) {
            case "GET":
                // session_start();
                // $officer = $_SESSION['username'] ?? 'unknown';
                // $scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
                // // Log the “read by ID” action
                // $this->logger->log(
                //     'suspects',            // table name
                //     (int)$suspect['id'],   // record_id
                //     'read',                // action
                //     $officer,
                //     "Viewed suspect ({$suspect['suspect_code']})",
                //     $currentUrl
                // );
    
                // echo json_encode($suspect);
                // break;

            case "PATCH":
            // $data = (array) json_decode(file_get_contents("php://input"), true);

            // $errors = $this->getValidationErrors($data, false);

            // if (! empty($errors)) {

            //     http_response_code(422);
            //     echo json_encode(["error" => $errors]);
            //     break;

                
            // }

            // $rows = $this->gateway->updateSuspect($suspect, $data);
            // $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            // $updated_suspect_code_audit_log = $_SESSION['suspect_code_on_update'];

            // $officer = $_SESSION['username'] ?? 'unknown';
            // $this->logger->log(
            //     'suspects',
            //     (int)$id,
            //     'update',
            //     $_SESSION['username'] ?? 'unknown', // CHANGE THIS AFTER CREATING USERS
            //     "Updated suspect ($updated_suspect_code_audit_log) profile",
            //     $currentUrl
            // );
        


            // $suspect_code =  $_SESSION['suspect_code_on_update']; 

            // // http_response_code(201);
            // echo json_encode([
            //     "message" => "Suspect $suspect_code updated",
            //     "rows" => $rows
                
            // ]);

            // unset($_SESSION['suspect_code_on_update']);
            // break;

            // if (isset($_SESSION['suspect_code_on_update'])) {
            //     $suspect_code_message = $_SESSION['suspect_code_on_update']; 
            //     echo json_encode(["message" => "Suspect $suspect_code_message has been updated", 
            //     "rows" => $rows]);
            //     unset($_SESSION['suspect_code_on_update']); // clear after use
            // } else {

            //     echo json_encode(["message" => "No suspect was recently updated", 
            //     "rows" => 0 ]);
            // }

            break;

            

            case "DELETE":
                // // Perform deletion via the gateway
                // $rows = $this->gateway->deleteSuspect($id);
            
                // // Log the delete action (use session username or 'unknown' if not set)
                // // Build the full request URL (including host + path + query string)
                // $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                // $deleted_suspect_code_audit_log = $_SESSION['deleted_suspect_code'];
                

                // $officer = $_SESSION['username'] ?? 'unknown';// CHANGE THIS AFTER CREATING USERS
                // $this->logger->log(
                //     'suspects',
                //     (int)$id,
                //     'delete',
                //     $_SESSION['username'] ?? 'unknown',
                //     "Deleted suspect ($deleted_suspect_code_audit_log) profile",
                //     $currentUrl
                // );
            
                // // Now send back the JSON response using the deleted_suspect_code from session
                // if (isset($_SESSION['deleted_suspect_code'])) {
                //     $suspect_code_message = $_SESSION['deleted_suspect_code'];
                //     echo json_encode([
                //         "message" => "Suspect $suspect_code_message has been deleted",
                //         "rows"    => $rows
                //     ]);
            
                //     unset($_SESSION['deleted_suspect_code']); // clear after use
                // } else {
                //     echo json_encode([
                //         "message" => "No suspect was recently deleted",
                //         "rows"    => 0
                //     ]);
                // }
                break;
            
            default:
            http_response_code(405);
            header("Allowed: GET, PATCH, DELETE");

        }  

    }

    //NOTE: Get all and Post all, url with /known for GET and POST
    private function processCollectionRequest(string $method) {
        switch ($method) {
            case "GET":
                session_start();
                $officer = $_SESSION['username'] ?? 'unknown';
                $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
                $type = $_GET['type'] ?? 'role'; // default to 'role'
            
                if ($type === 'role') {
                    $this->logger->log(
                        'roles',
                        0,
                        'read',
                        $officer,
                        "Viewed all roles",
                        $currentUrl
                    );
            
                    echo json_encode(["roles" => $this->gateway->getAllRoles()]);
                } elseif ($type === 'permission') {
                    $this->logger->log(
                        'permissions',
                        0,
                        'read',
                        $officer,
                        "Viewed all permissions",
                        $currentUrl
                    );
            
                    echo json_encode(["permissions" => $this->gateway->getAllPermissions()]);
                } else {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid type parameter"]);
                }
            
                break;
            
        


    // case "GET":
    // session_start();
    // $officer = $_SESSION['username'] ?? 'unknown';
    // $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // // Input sanitization
    // $page   = max(1, (int) ($_GET['page'] ?? 1));
    // $limit  = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
    // $status = $_GET['status'] ?? null;
    // $search = $_GET['search'] ?? null;

    // // Logging
    // $this->logger->log(
    //     'cases',
    //     0,
    //     'read',
    //     $officer,
    //     "Viewed all cases" . 
    //         ($status ? " with status '$status'" : "") . 
    //         ($search ? " with search '$search'" : ""),
    //     $currentUrl
    // );

    
    // try {
    //     $cases = $this->gateway->getAllCases($page, $limit, $status, $search);
    //     $total = $this->gateway->getCaseCount($status, $search);

    //     echo json_encode([
    //         'cases' => $cases,
    //         'total' => $total,
    //         'page' => $page,
    //         'limit' => $limit
    //     ]);
    // } catch (PDOException $e) {
    //     http_response_code(500);
    //     echo json_encode([
    //         'error' => 'Failed to fetch case data',
    //         'details' => $e->getMessage()
    //     ]);

        
    // }

    // break;

    
        case "POST":
            // session_start();
            // $data = (array) json_decode(file_get_contents("php://input"), true);
            // $errors = $this->getValidationErrors($data, true);
            
            // if (!empty($errors)) {
            //     http_response_code(422);
            //     echo json_encode(["error" => $errors]);
            //     break;
            // }
            
            // $created = $this->gateway->addOfficerRole($data['name']);
            
            // if (!$created) {
            //     http_response_code(500);
            //     echo json_encode(["error" => "Failed to create role"]);
            //     break;
            // }
            
            // $roleId = $created['id'];
            // $roleName = $created['name'];
            
            // $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
            // $this->logger->log(
            //     'roles',
            //     $roleId,
            //     'create',
            //     $_SESSION['username'] ?? 'unknown',
            //     "Created new role ($roleName)",
            //     $currentUrl
            // );
            
            // http_response_code(201);
            // echo json_encode([
            //     "message" => "A new role has been successfully created",
            //     "role_name" => $roleName,
            // ]);
            // break;

            
                // case "POST":


                // session_start();
    
                // $data = (array) json_decode(file_get_contents("php://input"), true);
                // $errors = $this->getValidationErrors($data, true);
    
                // if (!empty($errors)) {
                //     http_response_code(422);
                //     echo json_encode(["error" => $errors]);
                //     break;
                // }
    
                // $type = $_GET['type'] ?? 'role'; // default to role
                // $created = null;
    
                // if ($type === 'role') {
                //     $created = $this->gateway->addOfficerRole($data['name']);
                // } elseif ($type === 'permission') {
                //     $created = $this->gateway->addOfficerPermission($data['name']);
                // } else {
                //     http_response_code(400);
                //     echo json_encode(["error" => "Invalid type specified (expected 'role' or 'permission')"]);
                //     break;
                // }
    
                // if (!$created) {
                //     http_response_code(500);
                //     echo json_encode(["error" => "Failed to create $type"]);
                //     break;
                // }
    
                // $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
                // $this->logger->log(
                //     $type === 'role' ? 'roles' : 'permissions',
                //     $created['id'],
                //     'create',
                //     $_SESSION['username'] ?? 'unknown',
                //     "Created new $type ({$created['name']})",
                //     $currentUrl
                // );
    
                // http_response_code(201);
                // echo json_encode([
                //     "message" => "A new $type has been successfully created",
                //     "name" => $created['name'],
                //     "id" => $created['id']
                // ]);
                // break;

                // case "POST":
                    session_start();
                
                    $type = $_GET['type'] ?? 'role'; // role, permission, or assign
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                
                    if ($type === 'assign') {
                        // Validate role_id and permission_id
                        if (!isset($data['role_id'], $data['permission_id'])) {
                            http_response_code(422);
                            echo json_encode(["error" => "role_id and permission_id are required"]);
                            break;
                        }
                
                        $assigned = $this->gateway->assignPermissionToRole(
                            (int)$data['role_id'],
                            (int)$data['permission_id']
                        );
                        
                
                        if (!$assigned) {
                            http_response_code(500);
                            echo json_encode(["error" => "Failed to assign permission to role"]);
                            break;
                        }
                
                        // Log the assignment
                        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                        $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                
                        $this->logger->log(
                            'role_permission',
                            0,
                            'assign',
                            $_SESSION['username'] ?? 'unknown',
                            "Assigned permission ID {$data['permission_id']} to role ID {$data['role_id']}",
                            $currentUrl
                        );
                
                        echo json_encode([
                            "message" => "Permission assigned to role successfully"
                        ]);
                        break;
                    }
                
                    // --- role or permission creation ---
                    $errors = $this->getValidationErrors($data, true);
                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["error" => $errors]);
                        break;
                    }
                
                    $created = null;
                    if ($type === 'role') {
                        $created = $this->gateway->addOfficerRole($data['name']);
                    } elseif ($type === 'permission') {
                        $created = $this->gateway->addOfficerPermission($data['name']);
                    } else {
                        http_response_code(400);
                        echo json_encode(["error" => "Invalid type specified (expected 'role', 'permission', or 'assign')"]);
                        break;
                    }
                
                    if (!$created) {
                        http_response_code(500);
                        echo json_encode(["error" => "Failed to create $type"]);
                        break;
                    }
                
                    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                    $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                
                    $this->logger->log(
                        $type === 'role' ? 'roles' : 'permissions',
                        $created['id'],
                        'create',
                        $_SESSION['username'] ?? 'unknown',
                        "Created new $type ({$created['name']})",
                        $currentUrl
                    );
                
                    http_response_code(201);
                    echo json_encode([
                        "message" => "A new $type has been successfully created",
                        "name" => $created['name'],
                        "id" => $created['id']
                    ]);
                    break;         

        default:
            http_response_code(405);
            header("Allow: GET, POST");
        }
    }
        //REMINDER: DO ALL INPUT VALIDATION HERE
    public function getValidationErrors(array $data, bool $is_new):array {
        $errors = [];
        if ($is_new && empty($data['name'])) {
            $errors[] = "Name is required";
        }
    
        if (isset($data['name']) && strlen(trim($data['name'])) < 3) {
            $errors[] = "Name must be at least 3 characters long";
              // }
        // elseif ($is_new && empty($data['charge'])) {
        //     $errors [] = "Charge is required";
        // }
        // elseif ($is_new && empty($data["category_of_crime"])) {
        //     $errors [] = "Category of crime is required";
        // }
        // elseif ($is_new && empty($data["suspect_statement"])) {
        //     $errors [] = "suspect statement is required";
        // }
        // // elseif ($is_new && empty($data["arrest_count"])) {
        // //     $errors [] = "Arrest count is required";
        // // }

        // elseif ($is_new && empty($data["date_of_interrogation"])) {
        //     $errors [] = "Date of interrogation is required";
        // }
        // elseif ($is_new && empty($data["date_of_report"])) {
        //     $errors [] = "Date of crime reportn is required";
        // }

        // elseif ($is_new && empty($data["date_of_crime"])) {
        //     $errors [] = "Suspect date of crime is required";
        // }
        // elseif ($is_new && empty($data["date_of_arrest"])) {
        //     $errors [] = "Suspect Date of arrest is required";
        // }

        // if(array_key_exists("suspect_id", $data)){

        //     if (filter_var($data['suspect_id'], FILTER_VALIDATE_INT) === false){
        //         $errors [] = "Suspect Id must be an integer";
        //     }
        }
        return $errors;

    }
}