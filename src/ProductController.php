<?php


class ProductController {

    public function __construct(private ProductGateway $gateway){
    
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
        
        $product = $this->gateway->get($id);

        if (! $product) {
            http_response_code(404);

            echo json_encode(["message" => "Product not found"]);

            return;
        }

        switch($method) {
            case "GET":
            echo json_encode($product);
            break;

            case "PATCH":
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data, false);

            if (! empty($errors)) {

                http_response_code(422);
                echo json_encode(["error" => $errors]);
                break;

                
            }

            $rows = $this->gateway->update($product, $data);

            // http_response_code(201);
            echo json_encode([
                "message" => "Product $id updated",
                "rows" => $rows
            ]);
            break;

            case "DELETE":
                $rows = $this->gateway->delete($id);

                echo json_encode(["message" => "Product $id deleted", 
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
                echo json_encode($this->gateway->getAll()); //GetAll from ProductGateway
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

                $id = $this->gateway->create($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Product created",
                    "id" => $id
                ]);
                break;

                default:
                http_response_code(405);
                header("Allowed: GET, POST");
                    

        }
        

    }
    
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