<?php
// class ProductGateway{
class CaseGateway{

    private PDO $conn;
    public function __construct(Database $database){

        $this->conn = $database->getConnection();

    }

    public function getConnection(): PDO {
        return $this->conn;
    }


        //GetAll will be consume by the admin and the known reporter to view histrory of thier report by id or email

    public function adminGetAll(): array {
        $sql = "SELECT * FROM known_report";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //use the code bellow for boolen
            // $row["previous_incident"] = (bool) $row["previous_incident"];


            $data[] = $row;

        }
        return $data;

    }


    //GetAll will be consume by the admin and the known reporter to view histrory of thier report by id or email
    // public function getAllCases(): array {
    //     $sql = "SELECT 
    //                 c.*, 
    //                 s.suspect_code, 
    //                 s.first_name, 
    //                 s.middle_name, 
    //                 s.last_name,
    //                 s.gender,
    //                 s.profile_photo,
    //                 s.age
    //             FROM cases c
    //             INNER JOIN suspects s ON c.suspect_id = s.id
    //             ORDER BY c.id DESC";
    
    //     $stmt = $this->conn->query($sql);
    //     $data = [];
    
    //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         // Combine full name
    //         $row['suspect_full_name'] = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    
    //         // Map case status to label
    //         $status = strtolower($row['case_status']);
    //         $row['case_status_label'] = match ($status) {
    //             'pending'   => 'Ongoing',
    //             'open'      => 'Ongoing',
    //             'closed'    => 'Closed',
    //             'resolved'  => 'Resolved',
    //             'dismissed' => 'Dismissed',
    //             default     => ucfirst($status),
    //         };
    
    //         $data[] = $row;
    //     }
    
    //     return $data;
    // }


    //THIS SAME METHOD WE CAN FILTER ALL CASES BY STATUS ($statusFilter) IF NO STATUS IT DISPLAY ALL PAGE BY PAGE 
    // public function getAllCases(int $page = 1, int $limit = 10, ?string $statusFilter = null): array {
    //     $offset = ($page - 1) * $limit;
    
    //     // Base SQL with JOIN

    //     // $statusFilter = "closed"; //YOU CAN ADD FILTER BY CASES STATUS 
    //     $sql = "SELECT 
    //                 c.*, 
    //                 s.suspect_code, 
    //                 s.first_name, 
    //                 s.middle_name, 
    //                 s.last_name,
    //                 s.gender,
    //                 s.profile_photo,
    //                 s.age
    //             FROM cases c
    //             INNER JOIN suspects s ON c.suspect_id = s.id";
    
    //     // Add WHERE clause if filtering by status
    //     if ($statusFilter) {
    //         $sql .= " WHERE LOWER(c.case_status) = :status";
    //     }
    
    //     $sql .= " ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
    
    //     $stmt = $this->conn->prepare($sql);
    
    //     if ($statusFilter) {
    //         $stmt->bindValue(':status', strtolower($statusFilter), PDO::PARAM_STR);
    //     }
    //     $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    //     $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    //     $stmt->execute();
    
    //     $data = [];
    //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         $row['suspect_full_name'] = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    
    //         $status = strtolower($row['case_status']);
    //         $row['case_status_label'] = match ($status) {
    //             'pending', 'open'     => 'Ongoing',
    //             'closed'              => 'Closed',
    //             'resolved'            => 'Resolved',
    //             'dismissed'           => 'Dismissed',
    //             default               => ucfirst($status),
    //         };
    
    //         $row['case_status_color'] = match ($status) {
    //             'pending', 'open'     => 'yellow',
    //             'closed', 'resolved'  => 'green',
    //             'dismissed'           => 'red',
    //             default               => 'gray',
    //         };
    
    //         $data[] = $row;
    //     }
    
    //     return $data;
    // }



    public function getAllCases($page = 1, $limit = 10, $status = null, $search = null): array {
        $offset = ($page - 1) * $limit;
    
        $sql = "SELECT c.*, s.suspect_code, s.first_name, s.last_name 
                FROM cases c
                LEFT JOIN suspects s ON c.suspect_id = s.id
                WHERE 1=1";
    
        $params = [];
    
        if ($status) {
            $sql .= " AND c.case_status = :status";
            $params[':status'] = $status;
        }
    
        if ($search) {
            $sql .= " AND (
            LOWER(c.charge) LIKE :search OR 
            LOWER(c.category_of_crime) LIKE :search OR 
            LOWER(c.suspect_statement) LIKE :search OR
            LOWER(s.first_name) LIKE :search OR
            LOWER(s.last_name) LIKE :search
        )";
            $params[':search'] = '%'. strtolower($search) . '%';
        }
    
        //FIXED: use LIMIT :limit OFFSET :offset
        $sql .= " ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
    
        $stmt = $this->conn->prepare($sql);
    
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
    
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
        //Temporal log for error
        error_log("SQL: " . $sql);
        error_log("Params: " . json_encode($params));

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
            
    // public function getCaseCount(?string $statusFilter = null): int {
    //     $sql = "SELECT COUNT(*) as total FROM cases";
    //     if ($statusFilter) {
    //         $sql .= " WHERE LOWER(case_status) = :case_status";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bindValue(':case_status', strtolower($statusFilter), PDO::PARAM_STR);
    //     } else {
    //         $stmt = $this->conn->prepare($sql);
    //     }
    
    //     $stmt->execute();
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return (int)($result['total'] ?? 0);
    // }


    public function getCaseCount(?string $status = null, ?string $search = null): int {
        $sql = "SELECT COUNT(*) 
                FROM cases c
                LEFT JOIN suspects s ON c.suspect_id = s.id
                WHERE 1=1";
    
        $params = [];
    
        if ($status) {
            $sql .= " AND c.case_status = :status";
            $params[':status'] = $status;
        }
    
        if ($search) {
            $sql .= " AND (
            LOWER(c.charge) LIKE :search OR 
            LOWER(c.category_of_crime) LIKE :search OR 
            LOWER(c.suspect_statement) LIKE :search OR
            LOWER(s.first_name) LIKE :search OR
            LOWER(s.last_name) LIKE :search
    )";
            $params[':search'] = '%' .  strtolower($search)  . '%';
        }
    
        $stmt = $this->conn->prepare($sql);
    
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
        
    
        // I have worked on create for suspect


public function createCase(array $data){
function generateUUIDv4() {
    $data = random_bytes(16);
    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generateCaseRefNumber() {
    return 'CASE-' . generateUUIDv4();
}
        

        $case_ref_number = generateCaseRefNumber();
        $case_status = $data['case_status'] ?? 'pending';
        $suspect_id = (int) $data['suspect_id'];

        // $_SESSION['suspect_id'] = $suspect_id;
        $charge = $data['charge'] ?? 'pending';


        // $_SESSION['case_ref_number'] = $case_ref_number; // Echo thesame code on successful submition of form
        
        $sql = "INSERT INTO cases(case_ref_number, suspect_id, case_status, evidence, charge, 
        category_of_crime, suspect_statement, agency_name, investigating_division, officer_in_charge, 
        case_officer_rank, date_of_interrogation, date_of_report, date_of_crime, date_of_arrest)
        VALUES (:case_ref_number, :suspect_id, :case_status, :evidence, :charge, :category_of_crime, 
        :suspect_statement, :agency_name, :investigating_division, :officer_in_charge, :case_officer_rank, 
        :date_of_interrogation, :date_of_report, :date_of_crime, :date_of_arrest)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":case_ref_number", $case_ref_number , PDO::PARAM_STR);
        $stmt->bindValue(":suspect_id",  $suspect_id, PDO::PARAM_INT);
        $stmt->bindValue(":case_status", $case_status, PDO::PARAM_STR);
        $stmt->bindValue(":evidence", $data["evidence"], PDO::PARAM_STR);
        $stmt->bindValue(":charge",    $charge , PDO::PARAM_STR);
        $stmt->bindValue(":category_of_crime", $data["category_of_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_statement", $data["suspect_statement"], PDO::PARAM_STR);
        $stmt->bindValue(":agency_name", $data["agency_name"], PDO::PARAM_STR);
        $stmt->bindValue(":investigating_division", $data["investigating_division"], PDO::PARAM_STR);
        $stmt->bindValue(":officer_in_charge", $data["officer_in_charge"], PDO::PARAM_STR);
        $stmt->bindValue(":case_officer_rank", $data["case_officer_rank"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_interrogation", $data["date_of_interrogation"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_report", $data["date_of_report"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_crime", $data["date_of_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_arrest",  $data["date_of_arrest"], PDO::PARAM_STR);


        $stmt->execute();

        $newId = (int)$this->conn->lastInsertId(); // newly inserted id

        // Fetch suspect details
$stmt = $this->conn->prepare("SELECT suspect_code, first_name, middle_name , last_name FROM suspects WHERE id = :id");
$stmt->bindValue(":id", $suspect_id, PDO::PARAM_INT);
$stmt->execute();
$suspect = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'id' => $newId,
            'case_ref_number' => $case_ref_number,
            'suspect_id' => $suspect_id,
            'suspect_code' => $suspect['suspect_code'] ?? null,
            'suspect_first' => $suspect['first_name'] ?? null,
            'suspect_middle' => $suspect['middle_name'] ?? null,
            'suspect_last' => $suspect['last_name'] ?? null
        ];
        
    }
    // public function get(string $id) : array |false {
    //     $sql = "SELECT * FROM product
    //     WHERE id = :id";

    //Get individual records by ID

    // GET ID: SELECT BY ID HERE AND USE IT TO UPDATE
    public function getSuspect(string $id) {
        
        $sql = "SELECT * FROM suspects
        WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // if ($data !== false) {
        //     $data["is_available"] = (bool) $data["is_available"];

        // }

        return $data;



    }

    public function getKnownByTrackId(int $trackId): array {
        $sql = "SELECT * FROM known_report WHERE track_id = :track_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":track_id", $trackId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function updateSuspect(array $current, array $new): int {
        $sql = "UPDATE suspects set first_name = :first_name, middle_name = :middle_name, last_name = :last_name, 
        nick_name = :nick_name, date_of_birth = :date_of_birth, age = :age, height = :height, weight = :weight, 
        eye_color = :eye_color, skin_color = :skin_color, scar = :scar, address = :address, phone = :phone, 
        nin = :nin, arrest_count = :arrest_count, criminal_affiliation = :criminal_affiliation, 
        gang_membership = :gang_membership, suspect_code = :suspect_code, profile_photo = :profile_photo  
        WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":first_name", $new["first_name"] ?? $current["first_name"], PDO::PARAM_STR);
        $stmt->bindValue(":middle_name", $new["middle_name"] ?? $current["middle_name"], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $new["last_name"]?? $current["last_name"], PDO::PARAM_STR);
        $stmt->bindValue(":nick_name", $new["nick_name"] ?? $current["nick_name"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_birth", $new["date_of_birth"] ?? $current["date_of_birth"], PDO::PARAM_STR);
        $stmt->bindValue(":age", $new["age"] ?? $current["age"], PDO::PARAM_STR);
        $stmt->bindValue(":height", $new["height"] ?? $current["height"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_date", $new["report_date"] ?? $current["report_date"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_time", $new["report_time"] ?? $current["report_time"], PDO::PARAM_STR);
        $stmt->bindValue(":weight", $new["weight"] ?? $current["weight"], PDO::PARAM_STR);
        $stmt->bindValue(":eye_color", $new["eye_color"] ?? $current["eye_color"], PDO::PARAM_STR);
        $stmt->bindValue(":skin_color", $new["skin_color"] ?? $current["skin_color"], PDO::PARAM_STR);
        $stmt->bindValue(":scar", $new["scar"] ?? $current["scar"], PDO::PARAM_STR);
        $stmt->bindValue(":address", $new["address"] ?? $current["address"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $new["phone"] ?? $current["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":nin", $new["nin"] ?? $current["nin"], PDO::PARAM_STR);
        $stmt->bindValue(":arrest_count", $new["arrest_count"] ?? $current["arrest_count"], PDO::PARAM_INT);
        $stmt->bindValue(":criminal_affiliation", $new["criminal_affiliation"] ?? $current["criminal_affiliation"], PDO::PARAM_STR);
        $stmt->bindValue(":gang_membership", $new["gang_membership"] ?? $current["gang_membership"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_code", $new["suspect_code"] ?? $current["suspect_code"], PDO::PARAM_STR);
        $stmt->bindValue(":profile_photo", $new["profile_photo"] ?? $current["profile_photo"], PDO::PARAM_STR);

        $_SESSION['suspect_code_on_update'] = $current["suspect_code"];

        // $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        // $stmt->bindValue(":size", $new["size"] ?? $current["size"], PDO::PARAM_INT);
        // $stmt->bindValue(":is_available", $new["is_available"] ?? $current["is_available"], PDO::PARAM_BOOL);

        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount();


    }

    public function deleteSuspect(string $id): int {

        $selectSql = "SELECT suspect_code FROM suspects WHERE id = :id";
        $selectStmt = $this->conn->prepare($selectSql);
        $selectStmt->bindValue(":id", $id, PDO::PARAM_INT);
        $selectStmt->execute();
    
        $data = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
        if ($data && isset($data['suspect_code'])) {
            $_SESSION['deleted_suspect_code'] = $data['suspect_code'];
        }

    $deleteSql = "DELETE FROM suspects WHERE id = :id";
    $deleteStmt = $this->conn->prepare($deleteSql);
    $deleteStmt->bindValue(":id", $id, PDO::PARAM_INT);
    $deleteStmt->execute();
    

    
    return $deleteStmt->rowCount();

    
    }
}