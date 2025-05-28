<?php


class AnonymousController {

    public function __construct(private AnonymousGateway $gateway){
    
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
        
        $anonymous = $this->gateway->getAnonymous($id);

        if (! $anonymous) {
            http_response_code(404);

            echo json_encode(["message" => "Record not found"]);

            return;
        }

        switch($method) {
            case "GET":
            echo json_encode($anonymous);
            break;

            case "PATCH":
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data, false);

            if (! empty($errors)) {

                http_response_code(422);
                echo json_encode(["error" => $errors]);
                break;

                
            }

            $rows = $this->gateway->updateAnonymous($anonymous, $data);

            // http_response_code(201);
            echo json_encode([
                "message" => "Record $id updated",
                "rows" => $rows
            ]);
            break;

            case "DELETE":
                $rows = $this->gateway->deleteAnonymous($id);

                echo json_encode(["message" => "Record $id deleted", 
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
                echo json_encode($this->gateway->getAllAnonymous()); //GetAll from ProductGateway
                break;
            
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                //added true bellow
                $errors = $this->getValidationErrors($data, true);

                if (! empty($errors)) {

                    http_response_code(422);
                    echo json_encode(["error" => $errors]);
                    break;
                }

                $id = $this->gateway->createAnonymous($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Report submitted successfully!",
                    "id" => $id
                ]);
                break;

                default:
                http_response_code(405);
                header("Allowed: GET, POST");
                    

        }
        

    }
    //DO ALL INPUT VALIDATION HERE
    // public function getValidationErrors(array $data, bool $is_new):array {
    //     $errors = [];
    //     if ($is_new && empty($data["crime_description"])) {
    //         $errors [] = "Crime details is required";
    //     }

    //     if(array_key_exists("size", array: $data)){
    //         // Yet to work here
    //         if (filter_var($data["size"], FILTER_VALIDATE_INT) === false){
    //             $errors [] = "Size must be an integer";
    //         }
    //     }
    //     return $errors;

    
    public function getValidationErrors(array $data, bool $is_new): array {
        $errors = [];
    
        if ($is_new) {
            if (empty($data["crime_type"])) {
                $errors[] = "Crime type is required.";
            }
    
            if (empty($data["crime_description"])) {
                $errors[] = "Crime description is required.";
            }
    
            if (empty($data["crime_location"])) {
                $errors[] = "Crime location is required.";
            }
    
            if (empty($data["crime_date"])) {
                $errors[] = "Crime date is required.";
            }
    
            if (empty($data["crime_time"])) {
                $errors[] = "Crime time is required.";
            }
            // if (empty($data["suspect_name"])) {
            //     $errors[] = "Suspect name is required.";
            // }
            // if (empty($data["suspect_description"])) {
            //     $errors[] = "Suspect description is required.";
            // }
            // if (empty($data["vehicle_type"])) {
            //     $errors[] = "Type of vehicle is required.";
            // }
            // if (empty($data["victim_name"])) {
            //     $errors[] = "Victime name is required.";
            // }
            // if (empty($data["witness_name"])) {
            //     $errors[] = "Witness name is required.";
            // }
        }
    
        // Example additional check
        if (!empty($data["crime_date"]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data["crime_date"])) {
            $errors[] = "Crime date must be in YYYY-MM-DD format.";
        }
    
        return $errors;
    }
    
}