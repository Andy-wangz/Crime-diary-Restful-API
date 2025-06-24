<?php
require_once 'AuditLogger.php';
require_once 'AccessRequestGateway.php';

class AccessRequestController {

    private AuditLogger $logger;

    public function __construct(private AccessRequestGateway $gateway) {
        $this->logger = new AuditLogger($this->gateway->getConnection());
    }

    public function processRequest(string $method, ?string $id): void {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void {
        $request = $this->gateway->getRequestById($id);

        if (!$request) {
            http_response_code(404);
            echo json_encode(["message" => "Request not found"]);
            return;
        }

        switch ($method) {
            case "PATCH":
                session_start();
                $data = (array) json_decode(file_get_contents("php://input"), true);
            
                $errors = $this->getValidationErrors($data, false);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["error" => $errors]);
                    return;
                }
            
                $status = $data['status'] ?? null;
                $approved_by = $_SESSION['username'] ?? 'unknown'; // Use session value
            
                if (!$status) {
                    http_response_code(400);
                    echo json_encode(["message" => "Status is required"]);
                    return;
                }
            
                $rows = $this->gateway->updateRequestStatus($id, $status, $approved_by);
            
                $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            
                $this->logger->log(
                    'access_requests',
                    (int)$id,
                    'update',
                    $approved_by,
                    "Updated access request status to ({$status})",
                    $currentUrl
                );
            
                echo json_encode(["message" => "Request updated", "rows" => $rows]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: PATCH");
        }
    }

    private function processCollectionRequest(string $method): void {
        switch ($method) {
            case "GET":
                session_start();
                $officer = $_SESSION['username'] ?? 'unknown';
                // $agency = $_SESSION['agency'] ?? null;
                $agency = 'NPF';        // Hardcoded approving agency
            
                if (!$agency) {
                    echo json_encode(["requests" => [], "message" => "Agency not set in session"]);
                    exit;
                }
            
                $this->logger->log(
                    'access_requests',
                    0,
                    'read',
                    $officer,
                    "Viewed access requests for approval",
                    $_SERVER['REQUEST_URI']
                );
            
                $requests = $this->gateway->getAllRequests($agency);
            
                echo json_encode(["requests" => $requests]);
                break;
            
            case "POST":
                session_start();
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($data, true);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["error" => $errors]);
                    return;
                }

                $created = $this->gateway->createAccessRequest($data);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                $currentUrl = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $officer = $_SESSION['username'] ?? 'unknown';

                $this->logger->log(
                    'access_requests',
                    (int)$created['id'],
                    'create',
                    $officer,
                    "Created access request for suspect ID ({$created['suspect_id']})",
                    $currentUrl
                );

                http_response_code(201);
                echo json_encode([
                    "message" => "Access request submitted",
                    "id" => $created['id']
                ]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    public function getValidationErrors(array $data, bool $is_new): array {
        $errors = [];

        if ($is_new && empty($data['suspect_id'])) {
            $errors[] = "Suspect ID is required";
        }

        if ($is_new && empty($data['requesting_agency'])) {
            $errors[] = "Requesting agency is required";
        }

        if ($is_new && empty($data['requesting_officer'])) {
            $errors[] = "Requesting officer name is required";
        }

        if (array_key_exists('status', $data) && !in_array($data['status'], ['pending', 'approved', 'declined'])) {
            $errors[] = "Invalid status value";
        }

        return $errors;
    }
}
