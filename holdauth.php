<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

spl_autoload_register(function ($class) {
    require __DIR__ . "/../src/$class.php";
});

$database = new Database("localhost", "product_db", "root", "");
$userGateway = new UserGateway($database);

$data = json_decode(file_get_contents("php://input"), true);
$path = $_SERVER["REQUEST_URI"];

if (str_contains($path, "register")) {
    if (empty($data["username"]) || empty($data["password"])) {
        http_response_code(400);
        echo json_encode(["error" => "Username and password required"]);
        exit;
    }

    $success = $userGateway->register($data);
    if ($success) {
        http_response_code(201);
        echo json_encode(["message" => "User registered"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Registration failed (possibly duplicate username)"]);
    }

} elseif (str_contains($path, "login")) {
    $user = $userGateway->login($data["username"]);
    if (!$user || !password_verify($data["password"], $user["password"])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }

    echo json_encode([
        "message" => "Login successful",
        "user" => ["id" => $user["id"], "username" => $user["username"]],
        // optionally: generate a JWT token here
    ]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Not found"]);
}
