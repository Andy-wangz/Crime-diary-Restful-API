<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class OfficerAuthController {
    public function __construct(private OfficerGateway $gateway) {}

    public function registerOfficer() {
        // Ensure request is POST and content-type is multipart/form-data
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed."]);
            return;
        }

        // Required fields
        $required = ["first_name", "last_name", "email", "password", "employment_number"];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                http_response_code(422);
                echo json_encode(["message" => "$field is required."]);
                return;
            }
        }

        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . "/../uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmp = $_FILES['photo']['tmp_name'];
            $fileName = uniqid("officer_") . "_" . basename($_FILES['photo']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $photoPath = "/uploads/" . $fileName;
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload photo."]);
                return;
            }
        }

        // Check for existing email or employment number
        $existing = $this->gateway->getOfficerByEmailOrEmploymentNumber($_POST["email"], $_POST["employment_number"]);
        if ($existing) {
            http_response_code(409);
            echo json_encode(["message" => "Email or Employment number already exists."]);
            return;
        }

        // Hash the password
        $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);

        // Prepare data
        $officerData = [
            "first_name" => $_POST["first_name"],
            "middle_name" => $_POST["middle_name"] ?? "",
            "last_name" => $_POST["last_name"],
            "email" => $_POST["email"],
            "employment_number" => $_POST["employment_number"],
            "officer_rank" => $_POST["officer_rank"] ?? "",
            "phone" => $_POST["phone"] ?? "",
            "password" => $passwordHash,
            "role" => $_POST["role"] ?? "user",
            "authorization_level" => $_POST["authorization_level"] ?? "",
            "agency_id" => $_POST["agency_id"] ?? null,
            "zone_id" => $_POST["zone_id"] ?? null,
            "state_id" => $_POST["state_id"] ?? null,
            "lga_id" => $_POST["lga_id"] ?? null,
            "division_id" => $_POST["division_id"] ?? null,
            "photo" => $photoPath,
        ];

        // Save to DB
        $officer = $this->gateway->registerOfficer($officerData);
        if ($officer) {
            // unset($officer["password"]); // don't return password hash
            http_response_code(201);
            echo json_encode([
                "message" => "Officer registered successfully.",
                "officer" => $officer,
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Registration failed."]);
        }
    }


    // public function getAllOfficers(): void {
    //     header("Content-Type: application/json");
    //     $officers = $this->gateway->getAllOfficersWithAgency();
    //     echo json_encode($officers);
    // }

    // public function getAllOfficers(): void {
        
    //     $officers = $this->gateway->getAllOfficers();
    
    //     if ($officers !== false && is_array($officers)) {
    //         echo json_encode($officers);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode(["message" => "Failed to fetch officers"]);
    //     }
    // //        echo json_encode(["debug" => "This method was called"]); // just test
    // // return;
    // }

    // public function getAllOfficers(): void {
    //     $filters = [
    //         'agency' => $_GET['agency'] ?? null,
    //         'role' => $_GET['role'] ?? null,
    //         'status' => $_GET['status'] ?? null,
    //         'search' => $_GET['search'] ?? null,
    //         'zone' => $_GET['zone'] ?? null,
    //         'state' => $_GET['state'] ?? null,
    //         'lga' => $_GET['lga'] ?? null,
    //         'division' => $_GET['division'] ?? null,        
    //     ];
    
    //     $officers = $this->gateway->getAllOfficers($filters);
    //     echo json_encode($officers);
    // }
    
    public function getAllOfficers(): void {
        header("Content-Type: application/json; charset=UTF-8");
    
        $filters = [
            'agency'   => $_GET['agency']   ?? null,
            // 'role'     => $_GET['role']     ?? null,
            'role' => $_GET['role_id'] ?? null,
            'status'   => $_GET['status']   ?? null,
            'search'   => $_GET['search']   ?? null,
            'zone'     => $_GET['zone']     ?? null,
            'state'    => $_GET['state']    ?? null,
            'lga'      => $_GET['lga']      ?? null,
            'division' => $_GET['division'] ?? null,        
        ];
    
        try {
            $officers = $this->gateway->getAllOfficers($filters);
            echo json_encode($officers);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
            error_log("getAllOfficers error: " . $e->getMessage());
        }
    }
    

    
    

    // public function getAllFilteredOfficers(): void {
    //     $filters = [
    //         'agency' => $_GET['agency'] ?? null,
    //         'role' => $_GET['role'] ?? null,
    //         'status' => $_GET['status'] ?? null,
    //         'search' => $_GET['search'] ?? null,
    //     ];
    
    //     $officers = $this->gateway->getFilteredOfficers($filters);
    //     echo json_encode($officers);
    // }


    // public function getAllOfficers(): void {
    //     $filters = [
    //         'agency' => $_GET['agency'] ?? null,
    //         'role' => $_GET['role'] ?? null,
    //         'status' => $_GET['status'] ?? null,
    //         'search' => $_GET['search'] ?? null,
    //     ];
    
    //     $officers = $this->gateway->getAllOfficers($filters);
    //     echo json_encode($officers);
    // }
    
    

    public function profile(): void {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not logged in']);
            return;
        }

        echo json_encode([
            "user_id" => $_SESSION["user_id"],
            "first_name" => $_SESSION["first_name"],
            "last_name" => $_SESSION["last_name"],
            "email" => $_SESSION["email"]
        ]);
    }


    
    

    public function login(): void {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data["email"]) ||
            empty($data["employment_number"]) ||
            empty($data["password"])
        ) {
            http_response_code(422);
            echo json_encode(["message" => "Email, Employment Number, and Password are required."]);
            return;
        }

        $user = $this->gateway->getOfficerByEmailOrEmploymentNumber($data["email"], $data["employment_number"]);
        if (!$user || !password_verify($data["password"], $user["password"])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials."]);
            return;
        }

        // In production: issue session/JWT here
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "user" => [
                "id" => $user["id"],
                "first_name" => $user["first_name"],
                "last_name" => $user["last_name"],
                "email" => $user["email"]
            ]
        ]);
    }


    public function logout(): void {
        session_start();
        session_unset();
        session_destroy();
        echo json_encode(["message" => "Logged out"]);
    }

    public function sendResetEmail(): void {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data["email"] ?? null;
        $employment_number = $data["employment_number"] ?? null;

        if (!$email) {
            http_response_code(400);
            echo json_encode(["message" => "Email is required"]);
            return;
        }

        $user = $this->gateway->getOfficerByEmailOrEmploymentNumber($email, $employment_number);
        if (!$user) {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
            return;
        }

        $otp = rand(100000, 999999);
        session_start();
        $_SESSION["otp"] = (string) $otp;
        $_SESSION["otp_email"] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wangeraandrew@gmail.com'; // use env in real app
            $mail->Password   = 'wffb sytj xqsb ilst';      // use env in real app
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('wangeraandrew@gmail.com', 'BDIC Crime Diary');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "Your OTP for password reset is <strong>$otp</strong>";
            $mail->AltBody = "Your OTP is $otp";

            $mail->send();
            echo json_encode(["message" => "OTP sent to email"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Email failed: " . $mail->ErrorInfo]);
        }
    }

    public function verifyOTP(): void {
        session_start();
        $data = json_decode(file_get_contents("php://input"), true);
        $enteredOtp = $data['otp'] ?? '';

        if (!isset($_SESSION['otp'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No OTP session found']);
            return;
        }

        if ($enteredOtp === $_SESSION['otp']) {
            $_SESSION['otp_verified'] = true;
            echo json_encode(['message' => 'OTP verified successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid OTP']);
        }
    }

    public function resetPassword(): void {
        session_start();
        $data = json_decode(file_get_contents("php://input"), true);
        $newPassword = $data['password'] ?? '';

        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
            http_response_code(403);
            echo json_encode(['message' => 'OTP not verified']);
            return;
        }

        $email = $_SESSION['otp_email'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = $this->gateway->updatePassword($email, $hashedPassword);

        if ($success) {
            unset($_SESSION['otp'], $_SESSION['otp_verified']);
            echo json_encode(['message' => 'Password reset successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update password']);
        }
    }

    public function resendOtp(): void {
        session_start();
        if (!isset($_SESSION['otp_email'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No email found in session.']);
            return;
        }

        $email = $_SESSION['otp_email'];
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = (string) $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wangeraandrew@gmail.com';
            $mail->Password   = 'wffb sytj xqsb ilst';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('wangeraandrew@gmail.com', 'BDIC Crime Diary');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP is: <strong>$otp</strong>";
            $mail->AltBody = "Your OTP is: $otp";

            $mail->send();
            echo json_encode(['message' => 'OTP sent successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Mailer Error: ' . $mail->ErrorInfo]);
        }
    }
}
