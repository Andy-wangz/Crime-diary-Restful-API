<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class AuthController {
    public function __construct(private UserGateway $gateway) {}

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data["surname"]) || empty($data["firstname"]) || empty($data["email"]) || empty($data["password"])) {
            http_response_code(422);
            echo json_encode(["message" => "All fields are required."]);
            return;
        }

        if ($this->gateway->getUserByEmail($data["email"])) {
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

    public function login(): void {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["email"], $data["password"])) {
            http_response_code(400);
            echo json_encode(["message" => "Email and password required"]);
            return;
        }

        $user = $this->gateway->getUserByEmail($data["email"]);
        if (!$user || !password_verify($data["password"], $user["password"])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials"]);
            return;
        }

        session_start();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["surname"] = $user["surname"];
        $_SESSION["firstname"] = $user["firstname"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["otp_email"] = $user["email"]; // required for resendOtp

        session_write_close();

        echo json_encode([
            "message" => "Login successful",
            "user" => [
                "id" => $user["id"],
                "surname" => $user["surname"]
            ]
        ]);
    }

    public function profile(): void {
        session_start();
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not logged in']);
            return;
        }

        echo json_encode([
            "user_id" => $_SESSION["user_id"],
            "surname" => $_SESSION["surname"],
            "firstname" => $_SESSION["firstname"],
            "email" => $_SESSION["email"]
        ]);
    }

    public function logout(): void {
        session_start();
        session_unset();
        session_destroy();
        echo json_encode(["message" => "Logged out"]);
    }

//Send Email
    public function sendResetEmail(): void
{
    // I USED THIS TO CHECK ERROR LOG, I COMMENTED IT BUT IF THERE ARE ISSUES, WILL REMOVE IT
    // file_put_contents('php://stderr', print_r($_POST, true)); // or log $_POST and `file_get_contents("php://input")`
    // $data = json_decode(file_get_contents("php://input"), true);
    // error_log(print_r($data, true));
    
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data["email"] ?? null;

    if (!$email) {
        http_response_code(400);
        echo json_encode(["message" => "Email is required"]);
        return;
    }

    $user = $this->gateway->getUserByEmail($email);
    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
        return;
    }

    $otp = rand(100000, 999999);

    session_start();
    $_SESSION["otp"] = strval($otp);
    $_SESSION["otp_email"] = $email;

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'wangeraandrew@gmail.com'; // change later to use .env
        $mail->Password   = 'wffb sytj xqsb ilst';    // change later to use .env
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('wangeraandrew@gmail.com', 'BDIC Crime Diary');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = "Your OTP for password reset is <strong>$otp</strong>. It will expire soon.";
        $mail->AltBody = "Your OTP is $otp";

        $mail->send();
        echo json_encode(["message" => "OTP sent to email"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Email failed: " . $mail->ErrorInfo]);
    }
    
}

//Verify OTP
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

        if (!$newPassword) {
            http_response_code(400);
            echo json_encode(['message' => 'Password is required']);
            return;
        }

        // $email = $_SESSION['email'];
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
        
        unset($_SESSION['otp'], $_SESSION['otp_verified']);

        // echo json_encode(['message' => 'Password reset successfully']);
    }

    public function resendOtp(): void {
        session_start();
        if (!isset($_SESSION['otp_email'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No email found in session.']);
            return;
        }

        $to = $_SESSION['otp_email'];
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = strval($otp);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'wangeraandrew@gmail.com';  // ✅ Move to .env/config
            $mail->Password   = 'wffb sytj xqsb ilst';     // ✅ Never commit this
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('wangeraandrew@gmail.com', 'BDIC Crime Diary');
            $mail->addAddress($to);

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
