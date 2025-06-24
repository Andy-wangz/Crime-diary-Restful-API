<?php
// class ProductGateway{
class SuspectGateway{

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
    // public function getAllSuspects(): array {
    //     $sql = "SELECT * FROM suspects";

    //     $stmt = $this->conn->query($sql);

    //     $data = [];

    //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         //use the code bellow for boolen
    //         // $row["previous_incident"] = (bool) $row["previous_incident"];


    //         $data[] = $row;

    //     }
    //     return $data;

    // }



    public function getAllSuspects(?array $params): array {
        $sql = "SELECT * FROM suspects";
        $conditions = [];
        $queryParams = [];
    
        if (!empty($params['agency'])) {
            $conditions[] = "agency = :agency";
            $queryParams[':agency'] = $params['agency'];
        }
    
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
    
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
        $stmt = $this->conn->prepare($sql);
    
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $offset = ($page - 1) * $limit;
    
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
// I have worked on create for suspect
    public function createSuspect(array $data){
        function generateSuspectCode() {
            return 'SUS-' . strtoupper(bin2hex(random_bytes(3)));
        }

        function calculateAge($dob) {
            $dobDate = new DateTime($dob);
            $now = new DateTime();
            $age = $dobDate->diff($now)->y; // gets the difference in full years
            return $age;
        }
        
        $date_of_birth = $data['date_of_birth'];
        $age = calculateAge($date_of_birth);

        $suspect_code = generateSuspectCode();

        $_SESSION['suspect_code'] = $suspect_code; // Echo thesame code on successful submition of form
        
        $sql = "INSERT INTO suspects(first_name, middle_name, last_name, nick_name, date_of_birth, 
        age, height, weight, eye_color, skin_color, scar, tattoos, address, phone, nin, arrest_count, 
        criminal_affiliation, gang_membership, suspect_code, profile_photo, profiled_agency, 
        profiled_officer_name, profiled_officer_rank)
        VALUES (:first_name, :middle_name, :last_name, :nick_name, :date_of_birth, :age, :height, 
        :weight, :eye_color, :skin_color, :scar, :tattoos, :address, :phone, :nin, :arrest_count, 
        :criminal_affiliation, :gang_membership, :suspect_code, :profile_photo, :profiled_agency, 
        :profiled_officer_name, :profiled_officer_rank)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":first_name", $data["first_name"], PDO::PARAM_STR);
        $stmt->bindValue(":middle_name", $data["middle_name"], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $data["last_name"], PDO::PARAM_STR);
        $stmt->bindValue(":nick_name", $data["nick_name"], PDO::PARAM_STR);
        $stmt->bindValue(":date_of_birth", $data["date_of_birth"], PDO::PARAM_STR);
        $stmt->bindValue(":age", $age, PDO::PARAM_STR);
        $stmt->bindValue(":height", $data["height"], PDO::PARAM_STR);
        $stmt->bindValue(":weight", $data["weight"], PDO::PARAM_STR);
        $stmt->bindValue(":eye_color", $data["eye_color"], PDO::PARAM_STR);
        $stmt->bindValue(":skin_color", $data["skin_color"], PDO::PARAM_STR);
        $stmt->bindValue(":scar", $data["scar"], PDO::PARAM_STR);
        $stmt->bindValue(":tattoos", $data["tattoos"], PDO::PARAM_STR);
        $stmt->bindValue(":address", $data["address"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $data["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":nin", $data["nin"], PDO::PARAM_INT);
        $stmt->bindValue(":arrest_count", $data["arrest_count"] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(":criminal_affiliation", $data["criminal_affiliation"], PDO::PARAM_STR);
        $stmt->bindValue(":gang_membership", $data["gang_membership"], PDO::PARAM_STR);
        // $stmt->bindValue(":suspect_code", $data["suspect_code"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_code", $suspect_code , PDO::PARAM_STR);
        $stmt->bindValue(":profile_photo", $data["profile_photo"], PDO::PARAM_STR);
        $stmt->bindValue(":profiled_agency", $data["profiled_agency"] ?? "Unknown", PDO::PARAM_STR);
        $stmt->bindValue(":profiled_officer_name", $data["profiled_officer_name"] ?? "Unknown", PDO::PARAM_STR);
        $stmt->bindValue(":profiled_officer_rank", $data["profiled_officer_rank"] ?? "Unknown", PDO::PARAM_STR);


        $stmt->execute();

        $newId = (int)$this->conn->lastInsertId(); // newly inserted id
        return [
            'id'           => $newId,
            'suspect_code' => $suspect_code,
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
        eye_color = :eye_color, skin_color = :skin_color, scar = :scar, tattoos = :tattoos, address = :address, phone = :phone, 
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
        $stmt->bindValue(":tattoos", $new["tattoos"] ?? $current["tattoos"], PDO::PARAM_STR);
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