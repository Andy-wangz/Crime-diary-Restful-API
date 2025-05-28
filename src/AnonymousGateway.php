<?php
// class ProductGateway{
class AnonymousGateway{

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
    public function getAllAnonymous(): array {
        $sql = "SELECT * FROM anonymous_report";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //use the code bellow for boolen
            // $row["previous_incident"] = (bool) $row["previous_incident"];


            $data[] = $row;

        }
        return $data;

    }
// I have worked on create for anonymous reporter
    public function createAnonymous(array $data){
        $sql = "INSERT INTO anonymous_report(crime_type, crime_description, crime_location, crime_date, crime_time, 
        suspect_name, suspect_description, vehicle_type, victim_name, witness_name, One_time_occurrence, other_details)
        VALUES (:crime_type, :crime_description, :crime_location, :crime_date, :crime_time, :suspect_name, :suspect_description, 
        :vehicle_type, :victim_name, :witness_name, :One_time_occurrence, :other_details)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":crime_type", $data["crime_type"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_description", $data["crime_description"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_location", $data["crime_location"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_date", $data["crime_date"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_time", $data["crime_time"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_name", $data["suspect_name"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":suspect_description", $data["suspect_description"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":vehicle_type", $data["vehicle_type"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":victim_name", $data["victim_name"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":witness_name", $data["witness_name"] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":One_time_occurrence", $data["One_time_occurrence"] ?? false, PDO::PARAM_BOOL);
        $stmt->bindValue(":other_details", $data["other_details"] ?? null, PDO::PARAM_STR);


        $stmt->execute();
        return $this->conn->lastInsertId();
    }
    // public function get(string $id) : array |false {
    //     $sql = "SELECT * FROM product
    //     WHERE id = :id";

    //Get individual records by ID

    // GET ID: SELECT BY ID HERE AND USE IT TO UPDATE
    public function getAnonymous(string $id) {
        $sql = "SELECT * FROM anonymous_report
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

    public function updateAnonymous(array $current, array $new): int {
        $sql = "UPDATE  anonymous_report set crime_type = :crime_type, crime_description = :crime_description, crime_location = :crime_location, crime_date = :crime_date, 
        crime_time = :crime_time, suspect_name = :suspect_name, suspect_description = :suspect_description, vehicle_type = :vehicle_type, 
        victim_name = :victim_name, witness_name = :witness_name, One_time_occurrence = :One_time_occurrence, other_details = :other_details
        WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":crime_type", $new["crime_type"] ?? $current["crime_type"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_description", $new["crime_description"] ?? $current["crime_description"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_location", $new["crime_location"]?? $current["crime_location"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_date", $new["crime_date"] ?? $current["crime_date"], PDO::PARAM_STR);
        $stmt->bindValue(":crime_time", $new["crime_time"] ?? $current["crime_time"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_name", $new["suspect_name"] ?? $current["suspect_name"], PDO::PARAM_STR);
        $stmt->bindValue(":suspect_description", $new["suspect_description"] ?? $current["suspect_description"], PDO::PARAM_STR);
        $stmt->bindValue(":vehicle_type", $new["vehicle_type"] ?? $current["vehicle_type"], PDO::PARAM_STR);
        $stmt->bindValue(":victim_name", $new["victim_name"] ?? $current["victim_name"], PDO::PARAM_STR);
        $stmt->bindValue(":witness_name", $new["witness_name"] ?? $current["witness_name"], PDO::PARAM_STR);
        $stmt->bindValue(":One_time_occurrence", $new["One_time_occurrence"] ?? $current["One_time_occurrence"], PDO::PARAM_BOOL);
        $stmt->bindValue(":other_details", $new["other_details"] ?? $current["other_details"], PDO::PARAM_STR);



        // $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        // $stmt->bindValue(":size", $new["size"] ?? $current["size"], PDO::PARAM_INT);
        // $stmt->bindValue(":is_available", $new["is_available"] ?? $current["is_available"], PDO::PARAM_BOOL);

        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount();


    }

    public function deleteAnonymous(string $id): int {
        $sql = "DELETE FROM anonymous_report WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}