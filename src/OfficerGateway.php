<?php

// class OfficerGateway {

//     public function __construct(private Database $database) {}

//     public function getUserByEmailOrOfficerNumber(string $email, $officer_number): array|false {
//         $sql = "SELECT * FROM officers WHERE email = :email OR officer_number = :officer_number";
//         $stmt = $this->database->getConnection()->prepare($sql);
//         $stmt->execute(["email" => $email, "officer_number" => $officer_number]);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

//     public function registerOfficer(array $data): bool {

//         function generateOfficerCode() {
//             return 'OFF-' . strtoupper(bin2hex(random_bytes(3)));
//         }

//         $officer_code = generateOfficerCode();

//         $sql = "INSERT INTO officers (
//                     first_name, middle_name, last_name, email, password, phone, 
//                     officer_number, rank, agency_id, zone_id, state_id, lga_id, 
//                     division_id, authorization_level, role, photo, officer_code, status
//                 ) VALUES (
//                     :first_name, :middle_name, :last_name, :email, :password, :phone,
//                     :officer_number, :rank, :agency_id, :zone_id, :state_id, :lga_id,
//                     :division_id, :authorization_level, :role, :photo, :officer_code, :status
//                 )";
    
//         $stmt = $this->database->getConnection()->prepare($sql);
    
//         // Hash password
//         $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
//         // Bind values
//         $stmt->bindValue(':first_name', $data['first_name'], PDO::PARAM_STR);
//         $stmt->bindValue(':middle_name', $data['middle_name'], PDO::PARAM_STR);
//         $stmt->bindValue(':last_name', $data['last_name'], PDO::PARAM_STR);
//         $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
//         $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
//         $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
//         $stmt->bindValue(':officer_number', $data['officer_number'], PDO::PARAM_STR);
//         $stmt->bindValue(':rank', $data['rank'], PDO::PARAM_STR);
//         $stmt->bindValue(':agency_id', $data['agency_id'], PDO::PARAM_INT);
//         $stmt->bindValue(':zone_id', $data['zone_id'], PDO::PARAM_INT);
//         $stmt->bindValue(':state_id', $data['state_id'], PDO::PARAM_INT);
//         $stmt->bindValue(':lga_id', $data['lga_id'], PDO::PARAM_INT);
//         $stmt->bindValue(':division_id', $data['division_id'], PDO::PARAM_INT);
//         $stmt->bindValue(':authorization_level', $data['authorization_level'], PDO::PARAM_INT);
//         $stmt->bindValue(':role', $data['role'], PDO::PARAM_STR);
//         $stmt->bindValue(':photo', $data['photo'], PDO::PARAM_STR); // e.g., Cloudinary URL or filename
//         $stmt->bindValue(':officer_code', $officer_code, PDO::PARAM_STR);
//         $stmt->bindValue(':status', $data['status'] ?? 'inactive', PDO::PARAM_STR); // default: inactive
    
//         return $stmt->execute();
//     }
    
// //update password
//     public function updatePassword(string $email, string $hashedPassword): bool {
//         $stmt = $this->database->getConnection()->prepare("UPDATE officers SET password = :password WHERE email = :email");
//         return $stmt->execute([
//             'password' => $hashedPassword,
//             'email' => $email
//         ]);
//     }
    

// }



class OfficerGateway {
    private PDO $conn;

    public function __construct(private Database $database) {
        $this->conn = $database->getConnection();
    }

    public function getOfficerByEmailOrEmploymentNumber(string $email, string $employment_number): array|false {
        $sql = "SELECT * FROM officers WHERE email = :email OR employment_number = :employment_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'employment_number' => $employment_number
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerOfficer(array $data): bool {
        $officer_code = $this->generateOfficerCode();
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO officers (
                    officer_code, first_name, middle_name, last_name, officer_rank, employment_number,
                    agency_id, zone_id, state_id, lga_id, division_id,
                    authorization_level, role, phone, email, password, photo, status, last_active
                ) VALUES (
                    :officer_code, :first_name, :middle_name, :last_name, :officer_rank, :employment_number,
                    :agency_id, :zone_id, :state_id, :lga_id, :division_id,
                    :authorization_level, :role, :phone, :email, :password, :photo, :status, :last_active
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':officer_code', $officer_code);
        $stmt->bindValue(':first_name', $data['first_name']);
        $stmt->bindValue(':middle_name', $data['middle_name']);
        $stmt->bindValue(':last_name', $data['last_name']);
        $stmt->bindValue(':officer_rank', $data['officer_rank']);
        $stmt->bindValue(':employment_number', $data['employment_number']);
        $stmt->bindValue(':agency_id', $data['agency_id'], PDO::PARAM_INT);
        $stmt->bindValue(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindValue(':state_id', $data['state_id'], PDO::PARAM_INT);
        $stmt->bindValue(':lga_id', $data['lga_id'], PDO::PARAM_INT);
        $stmt->bindValue(':division_id', $data['division_id'], PDO::PARAM_INT);
        $stmt->bindValue(':authorization_level', $data['authorization_level'], PDO::PARAM_INT);
        $stmt->bindValue(':role', $data['role']);
        $stmt->bindValue(':phone', $data['phone']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':photo', $data['photo'] ?? null);
        $stmt->bindValue(':status', $data['status'] ?? 'inactive');
        $stmt->bindValue(':last_active', $data['last_active'] ?? 'not assigned');

        return $stmt->execute();
    }

    public function updatePassword(string $email, string $hashedPassword): bool {
        $stmt = $this->conn->prepare("UPDATE officers SET password = :password WHERE email = :email");
        return $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
    }



    private function generateOfficerCode(): string {
        return 'OFF-' . strtoupper(bin2hex(random_bytes(3)));
    }



    // public function getAllOfficers(): array|false {
    //     $sql = "SELECT 
    //     o.id,
    //     CONCAT(o.first_name, ' ', o.last_name) AS name,
    //     o.email,
    //     o.role,
    //     a.name AS agency,
    //     o.status,
    //     DATE_FORMAT(o.last_active, '%M %e, %Y - %l:%i%p') AS last_active
    //     FROM officers o
    //     LEFT JOIN agencies a ON o.agency_id = a.id
    //     ";
    
    //     $stmt = $this->conn->query($sql);
    //     return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    // }



    // public function getFilteredOfficers(array $filters): array {
    //     $sql = "SELECT 
    //                 o.id,
    //                 CONCAT(o.first_name, ' ', o.last_name) AS name,
    //                 o.email,
    //                 o.role,
    //                 a.name AS agency,
    //                 o.status,
    //                 DATE_FORMAT(o.last_active, '%M %e, %Y - %l:%i%p') AS lastActive
    //             FROM officers o
    //             LEFT JOIN agencies a ON o.agency_id = a.id
    //             WHERE 1";
    
    //     $params = [];
    
    //     if (!empty($filters['agency'])) {
    //         $sql .= " AND a.name = :agency";
    //         $params[':agency'] = $filters['agency'];
    //     }
    
    //     if (!empty($filters['role'])) {
    //         $sql .= " AND o.role = :role";
    //         $params[':role'] = $filters['role'];
    //     }
    
    //     if (!empty($filters['status'])) {
    //         $sql .= " AND o.status = :status";
    //         $params[':status'] = $filters['status'];
    //     }
    
    //     if (!empty($filters['search'])) {
    //         $sql .= " AND (
    //                     o.first_name LIKE :search OR 
    //                     o.last_name LIKE :search OR 
    //                     o.email LIKE :search
    //                 )";
    //         $params[':search'] = '%' . $filters['search'] . '%';
    //     }
    
    //     $stmt = $this->conn->prepare($sql);
    //     foreach ($params as $key => $value) {
    //         $stmt->bindValue($key, $value);
    //     }
    
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
    

    public function getAllOfficers(array $filters = []): array {
        $sql = "SELECT 
                    o.id,
                    CONCAT(o.first_name, ' ', o.last_name) AS name,
                    o.email,
                    o.role,
                    a.name AS agency,
                    o.status,
                    DATE_FORMAT(o.last_active, '%M %e, %Y - %l:%i%p') AS lastActive
                FROM officers o
                LEFT JOIN agencies a ON o.agency_id = a.id
                LEFT JOIN zones z ON o.zone_id = z.id
                LEFT JOIN states s ON o.state_id = s.id
                LEFT JOIN lgas l ON o.lga_id = l.id
                LEFT JOIN divisions d ON o.division_id = d.id
                WHERE 1";
    
        $params = [];
    
        if (!empty($filters['agency'])) {
            $sql .= " AND a.code = :agency"; // Assuming code like EFCC, NPF
            $params[':agency'] = $filters['agency'];
        }
    
        if (!empty($filters['role'])) {
            $sql .= " AND o.role = :role";
            $params[':role'] = $filters['role'];
        }
    
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
    
        if (!empty($filters['zone'])) {
            $sql .= " AND o.zone_id = :zone_id";
            $params[':zone_id'] = $filters['zone'];
        }
    
        if (!empty($filters['state'])) {
            $sql .= " AND o.state_id = :state_id";
            $params[':state_id'] = $filters['state'];
        }
    
        if (!empty($filters['lga'])) {
            $sql .= " AND o.lga_id = :lga_id";
            $params[':lga_id'] = $filters['lga'];
        }
    
        if (!empty($filters['division'])) {
            $sql .= " AND o.division_id = :division_id";
            $params[':division_id'] = $filters['division'];
        }
    
        if (!empty($filters['search'])) {
            $sql .= " AND (
                o.first_name LIKE :search OR
                o.last_name LIKE :search OR
                o.email LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
    
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        }


