<?php

require_once 'AuditLogger.php';
require_once 'OfficerGateway.php';



class OfficerController {
    private AuditLogger $logger;

    public function __construct(private OfficerGateway $gateway) {
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
            
            $officerSummary = $this->gateway->getOfficer($id);     // For GET (summary)
            $officerFull    = $this->gateway->getOfficerId($id);   // For PATCH (full data)

    
            // if (! $officerSummary) {
            //     http_response_code(404);
    
            //     echo json_encode(["message" => "Officer not found"]);
    
            //     return;
            // }

            if (!$officerSummary || !$officerFull) {
                http_response_code(404);
                echo json_encode(["message" => "Officer not found"]);
                return;
            }
        
    
            switch($method) {
                case "GET":
                echo json_encode($officerSummary);
                break;
    
                case "PATCH":
                        $data = (array) json_decode(file_get_contents("php://input"), true);
                        $errors = $this->getValidationErrors($data, false);
                    
                        if (!empty($errors)) {
                            http_response_code(422);
                            echo json_encode(["error" => $errors]);
                            break;
                        }
                    
                        $type = $_GET['type'] ?? 'update';
                    
                        if ($type === 'assign-role') {
                            $roleId = $data['role_id'] ?? null;
                        
                            if (!$id || !$roleId) {
                                http_response_code(400);
                                echo json_encode(["error" => "Missing officer ID or role ID"]);
                                break;
                            }
                        
                            $success = $this->gateway->assignRoleToOfficer($id, $roleId);
                        
                            if ($success) {
                                echo json_encode(["message" => "Role assigned successfully"]);
                            } else {
                                http_response_code(500);
                                echo json_encode(["error" => "Failed to assign role"]);
                            }
                        
                            break;
                        }
                        
                     else {
                            // Normal update logic
                            $rows = $this->gateway->updateOfficer($officerFull, $data);
                            echo json_encode([
                                "message" => "Officer $id updated successfully",
                                "rows" => $rows
                            ]);
                        }
                        break;
                        
                case "DELETE":
                    $rows = $this->gateway->deleteOfficerById($id);
    
                    echo json_encode(["message" => "Officer $id deleted", 
                    "rows" => $rows]);
                    break;
    
                default:
                http_response_code(405);
                header("Allowed: GET, PATCH, DELETE");
    
            }  
    
        }
    
        //NOTE: Get all and Post all, url with /product for GET and POST
        private function processCollectionRequest(string $method) {
            switch ($method) {
                case "GET":
                    session_start();
    $officer = $_SESSION['username'] ?? 'unknown';

    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $this->logger->log(
        'officers',
        0,
        'read',
        $officer,
        "Fetched all officers for role assignment",
        $currentUrl
    );

    $officers = $this->gateway->listAllOfficers();

    echo json_encode(["officers" => $officers]);
    break;
                
                case "POST":
    
                    default:
                    http_response_code(405);
                    header("Allowed: GET, POST");
                        
    
            }
            
    
        }
        
        public function getValidationErrors(array $data, bool $is_new):array {
            $errors = [];
            if ($is_new && empty($data["first_name"])) {
                $errors [] = "first name is required";
            }
    
            if(array_key_exists("phone", $data)){
    
                if (filter_var($data["phone"], FILTER_VALIDATE_INT) === false){
                    $errors [] = "Phone must be an integer";
                }
            }
            return $errors;
    
        }

}


