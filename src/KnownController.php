<?php


class KnownController {

    public function __construct(private KnownGateway $gateway){
    
    }
    //processrequest is use in the index.php for all the request, houses the following
        //processResourceRequest is for example /product/id
        //processCollectionRequest is for example /product
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
        
        $known = $this->gateway->getKnown($id);

        if (! $known) {
            http_response_code(404);

            echo json_encode(["message" => "Record not found"]);

            return;
        }

        switch($method) {
            case "GET":
            echo json_encode($known);
            break;

            case "PATCH":
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data, false);

            if (! empty($errors)) {

                http_response_code(422);
                echo json_encode(["error" => $errors]);
                break;

                
            }

            $rows = $this->gateway->updateKnown($known, $data);

            // http_response_code(201);
            echo json_encode([
                "message" => "Record $id updated",
                "rows" => $rows
            ]);
            break;

            case "DELETE":
                $rows = $this->gateway->deleteKnown($id);

                echo json_encode(["message" => "Record $id deleted", 
                "rows" => $rows]);
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
    
                if (!isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(["error" => "Unauthorized"]);
                    return;
                }
    
                $trackId = $_SESSION['user_id'];
    
                $reports = $this->gateway->getKnownByTrackId($trackId); // <-- new method
                echo json_encode($reports);
                break;
    
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
    
                $errors = $this->getValidationErrors($data, true);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["error" => $errors]);
                    break;
                }
    
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(["error" => "Unauthorized"]);
                    return;
                }
    
                $data['track_id'] = $_SESSION['user_id']; // Set the track_id for this report
    
                $id = $this->gateway->createKnown($data);
    
                http_response_code(201);
                echo json_encode([
                    "message" => "Report submitted successfully!",
                    "id" => $id
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
        if ($is_new && empty($data["name"])) {
            $errors [] = "name is required";
        }

        if(array_key_exists("size", $data)){

            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false){
                $errors [] = "Size must be an integer";
            }
        }
        return $errors;

    }
}