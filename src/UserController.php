<?php
class UserController {
    public function __construct(private UserGateway $gateway) {}

    //REGISTRATION
    public function register() {
        //Get input coming from form
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data["surname"]) || empty($data["firstname"])|| empty($data["email"]) || empty($data["password"])) {
            http_response_code(422);
            echo json_encode(["message" => "All fields and password are required."]);
            return;
        }
        //Check if username already exist
        $existing = $this->gateway->getUserByEmail($data["email"]);
        if ($existing) {
            http_response_code(409);
            echo json_encode(["message" => "Email already exists."]);
            return;
        }

        $success = $this->gateway->register($data["surname"], $data["firstname"], $data["email"], $data["password"]);
        if ($success) {
            http_response_code(201);
            echo json_encode(["message" => "User registered successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Registration failed."]);
        }
    }


//LOGIN
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data["email"]) || empty($data["password"])) {
            http_response_code(422);
            echo json_encode(["message" => "Email and password are required."]);
            return;
        }

        $user = $this->gateway->getUserByEmail($data["email"]);
        if (!$user || !password_verify($data["password"], $user["password"])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials."]);
            return;
        }
        

        http_response_code(200);
        echo json_encode(["message" => "Login successful."]);
        // For real apps: generate and return JWT or session token here

        // header('Location: testing.php');
    }
}
