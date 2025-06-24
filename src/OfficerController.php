<?php

class OfficerController {
    public function __construct(private OfficerGateway $gateway) {}

    // REGISTER OFFICER
    // public function registerOfficer() {
    //     // Ensure request is POST and content-type is multipart/form-data
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         http_response_code(405);
    //         echo json_encode(["message" => "Method not allowed."]);
    //         return;
    //     }

    //     // Required fields
    //     $required = ["first_name", "last_name", "email", "password", "employment_number"];
    //     foreach ($required as $field) {
    //         if (empty($_POST[$field])) {
    //             http_response_code(422);
    //             echo json_encode(["message" => "$field is required."]);
    //             return;
    //         }
    //     }

    //     // Handle photo upload
    //     $photoPath = null;
    //     if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    //         $uploadDir = __DIR__ . "/../../uploads/";
    //         if (!is_dir($uploadDir)) {
    //             mkdir($uploadDir, 0777, true);
    //         }

    //         $fileTmp = $_FILES['photo']['tmp_name'];
    //         $fileName = uniqid("officer_") . "_" . basename($_FILES['photo']['name']);
    //         $targetPath = $uploadDir . $fileName;

    //         if (move_uploaded_file($fileTmp, $targetPath)) {
    //             $photoPath = "uploads/" . $fileName;
    //         } else {
    //             http_response_code(500);
    //             echo json_encode(["message" => "Failed to upload photo."]);
    //             return;
    //         }
    //     }

    //     // Check for existing email or employment number
    //     $existing = $this->gateway->getOfficerByEmailOrEmploymentNumber($_POST["email"], $_POST["employment_number"]);
    //     if ($existing) {
    //         http_response_code(409);
    //         echo json_encode(["message" => "Email or Employment number already exists."]);
    //         return;
    //     }

    //     // Hash the password
    //     $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);

    //     // Prepare data
    //     $officerData = [
    //         "first_name" => $_POST["first_name"],
    //         "middle_name" => $_POST["middle_name"] ?? "",
    //         "last_name" => $_POST["last_name"],
    //         "email" => $_POST["email"],
    //         "employment_number" => $_POST["employment_number"],
    //         "officer_rank" => $_POST["officer_rank"] ?? "",
    //         "phone" => $_POST["phone"] ?? "",
    //         "password" => $passwordHash,
    //         "role" => $_POST["role"] ?? "user",
    //         "authorization_level" => $_POST["authorization_level"] ?? "",
    //         "agency_id" => $_POST["agency_id"] ?? null,
    //         "zone_id" => $_POST["zone_id"] ?? null,
    //         "state_id" => $_POST["state_id"] ?? null,
    //         "lga_id" => $_POST["lga_id"] ?? null,
    //         "division_id" => $_POST["division_id"] ?? null,
    //         "photo" => $photoPath,
    //     ];

    //     // Save to DB
    //     $officer = $this->gateway->registerOfficer($officerData);
    //     if ($officer) {
    //         unset($officer["password"]); // don't return password hash
    //         http_response_code(201);
    //         echo json_encode([
    //             "message" => "Officer registered successfully.",
    //             "officer" => $officer,
    //         ]);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode(["message" => "Registration failed."]);
    //     }
    // }

    // LOGIN OFFICER
    // public function login(): void {
    //     $data = json_decode(file_get_contents("php://input"), true);

    //     if (
    //         empty($data["email"]) ||
    //         empty($data["employment_number"]) ||
    //         empty($data["password"])
    //     ) {
    //         http_response_code(422);
    //         echo json_encode(["message" => "Email, Employment Number, and Password are required."]);
    //         return;
    //     }

    //     $user = $this->gateway->getOfficerByEmailOrEmploymentNumber($data["email"], $data["employment_number"]);
    //     if (!$user || !password_verify($data["password"], $user["password"])) {
    //         http_response_code(401);
    //         echo json_encode(["message" => "Invalid credentials."]);
    //         return;
    //     }

    //     // In production: issue session/JWT here
    //     http_response_code(200);
    //     echo json_encode([
    //         "message" => "Login successful.",
    //         "user" => [
    //             "id" => $user["id"],
    //             "first_name" => $user["first_name"],
    //             "last_name" => $user["last_name"],
    //             "email" => $user["email"]
    //         ]
    //     ]);
    // }

    //     public function getAllOfficers(): void {
    //     $officers = $this->gateway->getAllOfficersWithAgency();
    
    //     echo json_encode($officers);
    // }

}


