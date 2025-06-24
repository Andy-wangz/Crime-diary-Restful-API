<?php
require_once 'AuditLogger.php';
require_once 'SuspectGateway.php';

class SuspectController {

    private AuditLogger $logger;

    public function __construct(private SuspectGateway $gateway) {
        // SuspectGateway::getConnection() must return a PDO instance
        $this->logger = new AuditLogger($this->gateway->getConnection());    }

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
            case "GET":
                // session_start();

                // START SESSSION HERE IF THE NEED ARISE
    
                // if (!isset($_SESSION['user_id'])) { // the ID is from AuthController
                //     http_response_code(401);
                //     echo json_encode(["error" => "Unauthorized"]);
                //     return;
                // }
    
                // $trackId = $_SESSION['user_id']; // used the id as track number
                session_start();
    $officer = $_SESSION['username'] ?? 'unknown';
    $scheme    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https://' : 'http://';
    $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Log reading the entire suspects collection
    $this->logger->log(
        'suspects',    // table name
        0,             // record_id = 0 for “list” or use NULL if your schema allows
        'read',
        $officer,
        "Viewed all suspects",
        $currentUrl
    );
    
                $suspect = $this->gateway->getAllSuspects(); // <-- new method
                echo json_encode($suspect);
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
    //IF NEED FOR SESSION DATA USE IT HERE
                // session_start();
                // if (!isset($_SESSION['user_id'])) {
                //     http_response_code(401);
                //     echo json_encode(["error" => "Unauthorized"]);
                //     return;
                // }
    
                // // $data['track_id'] = $_SESSION['user_id']; // Set the track_id for this report
                // $data['suspect_code'] = $_SESSION['user_id']; // Set the track_id for this report
    
                // $id = $this->gateway->createSuspect($data);
                $created = $this->gateway->createSuspect($data);
                $newId       = $created['id'];
                $suspect_code_audit_log = $created['suspect_code'];
                
                                // After creating a new suspect:
                $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                $this->logger->log(
                    'suspects',
                    $newId,
                    'create',
                    $_SESSION['username'] ?? 'unknown',
                    "Created new suspect ($suspect_code_audit_log)",
                    $currentUrl
                );


                // $suspect_code = $_SESSION['suspect_code'];
                // http_response_code(201);
                // echo json_encode([
                //     "message" => "A new suspect has been successfully profilled!",
                //     "suspect code" => $suspect_code
                // ]);

                if (isset($_SESSION['suspect_code'])) {
                    $suspect_code_message = $_SESSION['suspect_code']; 
                    http_response_code(201);
                echo json_encode([
                    "message" => "A new suspect has been successfully profilled!",
                    "suspect code" => $suspect_code_message
                ]);
    
                    unset($_SESSION['suspect_code']); // clear after use
                } else {

                    echo json_encode(["message" => "No suspect was recently profilled", 
                    "rows" => 0 ]);
                }


                break;
    
            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
        //REMINDER: DO ALL INPUT VALIDATION HERE
    public function getValidationErrors(array $data, bool $is_new):array {
        $errors = [];
        if ($is_new && empty($data["first_name"])) {
            $errors [] = "First name is required";
        }
        elseif ($is_new && empty($data["last_name"])) {
            $errors [] = "Last name is required";
        }
        elseif ($is_new && empty($data["date_of_birth"])) {
            $errors [] = "Date of birth is required";
        }
        elseif ($is_new && empty($data["address"])) {
            $errors [] = "Address is required";
        }
        // elseif ($is_new && empty($data["arrest_count"])) {
        //     $errors [] = "Arrest count is required";
        // }

        elseif ($is_new && empty($data["criminal_affiliation"])) {
            $errors [] = "Suspect crime affiliattion is required";
        }
        elseif ($is_new && empty($data["criminal_affiliation"])) {
            $errors [] = "Suspect crime affiliattion is required";
        }

        if(array_key_exists("nin", $data)){

            if (filter_var($data["nin"], FILTER_VALIDATE_INT) === false){
                $errors [] = "NIN must be an integer of 11 digits";
            }
        }
        return $errors;

    }
}