<?php
require_once 'AuditLogger.php';
require_once 'SuspectGateway.php';

class CaseController {

    private AuditLogger $logger;

    public function __construct(private CaseGateway $gateway) {
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
        
        $suspect = $this->gateway->getSuspect($id);
        

        if (! $suspect) {
            http_response_code(404);

            echo json_encode(["message" => "Record not found"]);

            return;
        }

        switch($method) {
            case "GET":
                session_start();
                $officer = $_SESSION['username'] ?? 'unknown';
                $scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
                // Log the “read by ID” action
                $this->logger->log(
                    'suspects',            // table name
                    (int)$suspect['id'],   // record_id
                    'read',                // action
                    $officer,
                    "Viewed suspect ({$suspect['suspect_code']})",
                    $currentUrl
                );
    
                echo json_encode($suspect);
                break;

            case "PATCH":
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data, false);

            if (! empty($errors)) {

                http_response_code(422);
                echo json_encode(["error" => $errors]);
                break;

                
            }

            $rows = $this->gateway->updateSuspect($suspect, $data);
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $updated_suspect_code_audit_log = $_SESSION['suspect_code_on_update'];

            $officer = $_SESSION['username'] ?? 'unknown';
            $this->logger->log(
                'suspects',
                (int)$id,
                'update',
                $_SESSION['username'] ?? 'unknown', // CHANGE THIS AFTER CREATING USERS
                "Updated suspect ($updated_suspect_code_audit_log) profile",
                $currentUrl
            );
        


            // $suspect_code =  $_SESSION['suspect_code_on_update']; 

            // // http_response_code(201);
            // echo json_encode([
            //     "message" => "Suspect $suspect_code updated",
            //     "rows" => $rows
                
            // ]);

            // unset($_SESSION['suspect_code_on_update']);
            // break;

            if (isset($_SESSION['suspect_code_on_update'])) {
                $suspect_code_message = $_SESSION['suspect_code_on_update']; 
                echo json_encode(["message" => "Suspect $suspect_code_message has been updated", 
                "rows" => $rows]);
                unset($_SESSION['suspect_code_on_update']); // clear after use
            } else {

                echo json_encode(["message" => "No suspect was recently updated", 
                "rows" => 0 ]);
            }

            break;

            

            case "DELETE":
                // Perform deletion via the gateway
                $rows = $this->gateway->deleteSuspect($id);
            
                // Log the delete action (use session username or 'unknown' if not set)
                // Build the full request URL (including host + path + query string)
                $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                $deleted_suspect_code_audit_log = $_SESSION['deleted_suspect_code'];
                

                $officer = $_SESSION['username'] ?? 'unknown';// CHANGE THIS AFTER CREATING USERS
                $this->logger->log(
                    'suspects',
                    (int)$id,
                    'delete',
                    $_SESSION['username'] ?? 'unknown',
                    "Deleted suspect ($deleted_suspect_code_audit_log) profile",
                    $currentUrl
                );
            
                // Now send back the JSON response using the deleted_suspect_code from session
                if (isset($_SESSION['deleted_suspect_code'])) {
                    $suspect_code_message = $_SESSION['deleted_suspect_code'];
                    echo json_encode([
                        "message" => "Suspect $suspect_code_message has been deleted",
                        "rows"    => $rows
                    ]);
            
                    unset($_SESSION['deleted_suspect_code']); // clear after use
                } else {
                    echo json_encode([
                        "message" => "No suspect was recently deleted",
                        "rows"    => 0
                    ]);
                }
                break;
            
            default:
            http_response_code(405);
            header("Allowed: GET, PATCH, DELETE");

        }  

    }

    //NOTE: Get all and Post all, url with /known for GET and POST
    private function processCollectionRequest(string $method) {
        switch ($method) {
    //         case "GET":
    //             // session_start();
    //             session_start();
    // $officer = $_SESSION['username'] ?? 'unknown';
    // $scheme    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https://' : 'http://';
    // $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // // Log reading the entire case collection
    // $this->logger->log(
    //     'cases',    // table name
    //     0,             // record_id = 0 for “list” or use NULL if your schema allows
    //     'read',
    //     $officer,
    //     "Viewed all cases",
    //     $currentUrl
    // );
    //     $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    //     $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    //     $status = $_GET['status'] ?? null;
    //     $search = $_GET['search'] ?? null;
        
    //     $cases = $this->gateway->getAllCases($page, $limit, $status, $search);
    //     $total = $this->gateway->getCaseCount($status, $search);
        
    //     echo json_encode([
    //         'cases' => $cases,
    //         'total' => $total,
    //         'page' => $page,
    //         'limit' => $limit


    //     ]);
    //     break;    


    case "GET":
    session_start();
    $officer = $_SESSION['username'] ?? 'unknown';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Input sanitization
    $page   = max(1, (int) ($_GET['page'] ?? 1));
    $limit  = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
    $status = $_GET['status'] ?? null;
    $search = $_GET['search'] ?? null;

    // Logging
    $this->logger->log(
        'cases',
        0,
        'read',
        $officer,
        "Viewed all cases" . 
            ($status ? " with status '$status'" : "") . 
            ($search ? " with search '$search'" : ""),
        $currentUrl
    );

    
    try {
        $cases = $this->gateway->getAllCases($page, $limit, $status, $search);
        $total = $this->gateway->getCaseCount($status, $search);

        echo json_encode([
            'cases' => $cases,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch case data',
            'details' => $e->getMessage()
        ]);

        
    }

    break;

    
        case "POST":
            session_start();
            $data = (array) json_decode(file_get_contents("php://input"), true);
        
            $errors = $this->getValidationErrors($data, true);
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode(["error" => $errors]);
                break;
            }
        
            $created = $this->gateway->createCase($data);
            $newId = $created['id'];
            $new_suspect_Id = $created['suspect_id'];
            $case_ref = $created['case_ref_number'];
            $suspect_name = trim(($created['suspect_first'] ?? '') . ' ' . ($created['suspect_middle'] ?? '') . ' ' . ($created['suspect_last'] ?? ''));
            $suspect_code = $created['suspect_code'] ?? 'N/A';

        
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
            $this->logger->log(
                'cases',
                $newId,
                'create',
                $_SESSION['username'] ?? 'unknown',
                "Created new case ($case_ref)",
                $currentUrl
            );
        
            http_response_code(201);
            echo json_encode([
                "message" => "A new case has been successfully opened for suspect ($new_suspect_Id!)",
                "case_ref_number" => $case_ref,
                "suspect_code" => $suspect_code,
                "suspect_name" => $suspect_name,

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
        if ($is_new && empty($data["suspect_id"])) {
            $errors [] = "Suspect id is required to make a case";
        }
        elseif ($is_new && empty($data['charge'])) {
            $errors [] = "Charge is required";
        }
        elseif ($is_new && empty($data["category_of_crime"])) {
            $errors [] = "Category of crime is required";
        }
        elseif ($is_new && empty($data["suspect_statement"])) {
            $errors [] = "suspect statement is required";
        }
        // elseif ($is_new && empty($data["arrest_count"])) {
        //     $errors [] = "Arrest count is required";
        // }

        elseif ($is_new && empty($data["date_of_interrogation"])) {
            $errors [] = "Date of interrogation is required";
        }
        elseif ($is_new && empty($data["date_of_report"])) {
            $errors [] = "Date of crime reportn is required";
        }

        elseif ($is_new && empty($data["date_of_crime"])) {
            $errors [] = "Suspect date of crime is required";
        }
        elseif ($is_new && empty($data["date_of_arrest"])) {
            $errors [] = "Suspect Date of arrest is required";
        }

        if(array_key_exists("suspect_id", $data)){

            if (filter_var($data['suspect_id'], FILTER_VALIDATE_INT) === false){
                $errors [] = "Suspect Id must be an integer";
            }
        }
        return $errors;

    }
}