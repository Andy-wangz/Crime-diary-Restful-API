<?php
// class ProductGateway{
class KnownGateway{

    private PDO $conn;
    public function __construct(Database $database){

        $this->conn = $database->getConnection();

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
    public function getAllKnownUserReport(): array {
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
// I have worked on create for known reporter
    public function createKnown(array $data){
        $sql = "INSERT INTO known_report(name, phone, email, type_of_crime, crime_location, 
        crime_date, crime_time, crime_description, victim_name, 
        victim_age, victim_injury, suspect_description, suspect_motive, suspect_connection_to_crime, 
        witness_name, witness_contact, previous_incident, status, track_id)
        VALUES (:name, :phone, :email, :type_of_crime, :crime_location, :crime_date, :crime_time, 
        :crime_description, :victim_name, :victim_age, :victim_injury, 
        :suspect_description, :suspect_motive, :suspect_connection_to_crime, :witness_name, :witness_contact, 
        :previous_incident, :status, :track_id)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $data["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindValue(":type_of_crime", $data["type_of_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_location", $data["crime_location"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_date", $data["crime_date"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_time", $data["crime_time"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_date", $data["report_date"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_time", $data["report_time"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_description", $data["crime_description"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_name", $data["victim_name"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_age", $data["victim_age"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_injury", $data["victim_injury"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_description", $data["suspect_description"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_motive", $data["suspect_motive"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_connection_to_crime", $data["suspect_connection_to_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":witness_name", $data["witness_name"], PDO::PARAM_STR);
        $stmt->bindValue(":witness_contact", $data["witness_contact"], PDO::PARAM_STR);
        $stmt->bindValue(":previous_incident", $data["previous_incident"], PDO::PARAM_STR);
        $stmt->bindValue(":status", $data["status"], PDO::PARAM_STR);
        $stmt->bindValue(":track_id", $data["track_id"], PDO::PARAM_STR);


        $stmt->execute();
        return $this->conn->lastInsertId();
    }
    // public function get(string $id) : array |false {
    //     $sql = "SELECT * FROM product
    //     WHERE id = :id";

    //Get individual records by ID

    // GET ID: SELECT BY ID HERE AND USE IT TO UPDATE
    public function getKnown(string $id) {
        
        $sql = "SELECT * FROM known_report
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
    

    public function updateKnown(array $current, array $new): int {
        $sql = "UPDATE known_report set name = :name, phone = :phone, email = :email, type_of_crime = :type_of_crime, 
        crime_location = :crime_location, crime_date = :crime_date, crime_time = :crime_time, crime_description = :crime_description, 
        victim_name = :victim_name, victim_age = :victim_age, victim_injury = :victim_injury, suspect_description = :suspect_description, 
        suspect_motive = :suspect_motive, suspect_connection_to_crime = :suspect_connection_to_crime, witness_name = :witness_name, witness_contact = :witness_contact, 
        previous_incident = :previous_incident, status = :status 
        WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $new["phone"] ?? $current["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $new["email"]?? $current["email"], PDO::PARAM_STR);
        $stmt->bindValue(":type_of_crime", $new["type_of_crime"] ?? $current["type_of_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_location", $new["crime_location"] ?? $current["crime_location"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_date", $new["crime_date"] ?? $current["crime_date"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_time", $new["crime_time"] ?? $current["crime_time"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_date", $new["report_date"] ?? $current["report_date"], PDO::PARAM_STR);
        // $stmt->bindValue(":report_time", $new["report_time"] ?? $current["report_time"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_description", $new["crime_description"] ?? $current["crime_description"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_name", $new["victim_name"] ?? $current["victim_name"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_age", $new["victim_age"] ?? $current["victim_age"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_injury", $new["victim_injury"] ?? $current["victim_injury"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_description", $new["suspect_description"] ?? $current["suspect_description"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_motive", $new["suspect_motive"] ?? $current["suspect_motive"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_connection_to_crime", $new["suspect_connection_to_crime"] ?? $current["suspect_connection_to_crime"], PDO::PARAM_STR);
        $stmt->bindValue(":witness_name", $new["witness_name"] ?? $current["witness_name"], PDO::PARAM_STR);
        $stmt->bindValue(":witness_contact", $new["witness_contact"] ?? $current["witness_contact"], PDO::PARAM_STR);
        $stmt->bindValue(":previous_incident", $new["previous_incident"] ?? $current["previous_incident"], PDO::PARAM_STR);
        $stmt->bindValue(":status", $new["status"] ?? $current["status"], PDO::PARAM_STR);



        // $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        // $stmt->bindValue(":size", $new["size"] ?? $current["size"], PDO::PARAM_INT);
        // $stmt->bindValue(":is_available", $new["is_available"] ?? $current["is_available"], PDO::PARAM_BOOL);

        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount();


    }

    public function deleteKnown(string $id): int {
        $sql = "DELETE FROM known_report WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}