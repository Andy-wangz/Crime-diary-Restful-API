<?php

class AccessRequestGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    public function createAccessRequest(array $data): array {
        $sql = "INSERT INTO access_requests (
                    suspect_id,
                    requesting_agency,
                    requesting_officer,
                    officer_rank,
                    approving_agency,	
                    reason_for_request,
                    status,
                    approval_date,
                    approved_by,
                    reason
                ) VALUES (
                    :suspect_id,
                    :requesting_agency,
                    :requesting_officer,
                    :officer_rank,
                    :approving_agency,	
                    :reason_for_request,
                    'pending',
                    :approval_date,
                    :approved_by,
                    :reason
                )";


        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':suspect_id', $data['suspect_id'], PDO::PARAM_INT);
        $stmt->bindValue(':requesting_agency', $data['requesting_agency'], PDO::PARAM_STR);
        $stmt->bindValue(':requesting_officer', $data['requesting_officer'], PDO::PARAM_STR);
        $stmt->bindValue(':officer_rank', $data['officer_rank'], PDO::PARAM_STR);
        $stmt->bindValue(':approving_agency', $data['approving_agency'], PDO::PARAM_STR);
        $stmt->bindValue(':reason_for_request', $data['reason_for_request'], PDO::PARAM_STR);
        $stmt->bindValue(':approval_date', $data['approval_date'], PDO::PARAM_STR);
        $stmt->bindValue(':approved_by', $data['approved_by'], PDO::PARAM_STR);
        $stmt->bindValue(':reason', $data['reason'], PDO::PARAM_STR);

        $stmt->execute();

        $id = $this->conn->lastInsertId();

        return [
            'id' => $id,
            'suspect_id' => $data['suspect_id'],
            'requesting_agency' => $data['requesting_agency'],
            'requesting_officer' => $data['requesting_officer'],
            'officer_rank' => $data['officer_rank'],
            'reason_for_request' => $data['reason_for_request'],
            'status' => 'pending',
            'approval_date' => $data['approval_date'] ?? null,
            'approval_by' => $data['approval_date'] ?? null,
            'reason' => $data['reason'] ?? null
            
        ];
    }

    // requested_by

    public function getRequestById(string $id): array|false {
        $sql = "SELECT * FROM access_requests WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllRequests(?string $agency = null): array {
        if ($agency) {
            $sql = "SELECT * FROM access_requests WHERE approving_agency = :agency ORDER BY requested_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':agency', $agency, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }
        
    // public function getAllRequestsAwaitingApproval(?string $agency = null): array {
    //     $approval_agency = $_SESSION['agency'] ?? 'NPF'; //This will be session data
    //     if ($agency) {
    //         $sql = "SELECT * FROM access_requests WHERE approving_agency = :agency ORDER BY requested_date DESC";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bindValue(':agency', $approval_agency, PDO::PARAM_STR);
    //         $stmt->execute();
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } else {
    //         $sql = "SELECT * FROM access_requests ORDER BY requested_date DESC";
    //         $stmt = $this->conn->query($sql);
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     }
    // }


    public function updateRequestStatus(string $id, string $status, string $approved_by): int {
        $sql = "UPDATE access_requests 
                SET status = :status, approval_date = NOW(), approved_by = :approved_by 
                WHERE id = :id";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':approved_by', $approved_by, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->rowCount();
    }
    
    }
