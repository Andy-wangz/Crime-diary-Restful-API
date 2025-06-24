
<?php

// class DropdownController {

//     private $conn;

//     public function __construct($conn) {
//         $this->conn = $conn;
//     }


//     public function getStatuses() {
//         header('Content-Type: application/json');
//         echo json_encode(['active', 'inactive']);
//     }

//     public function getRoles() {
//         header('Content-Type: application/json');
//         echo json_encode(['user', 'admin', 'supervisor']);
//     }

//     public function getAgencies() {
//         header('Content-Type: application/json');

//         global $conn;
//         $stmt = $conn->query("SELECT code, name FROM agencies ORDER BY name");
//         echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
//     }

//     public function getZones() {
//         global $conn;
//         $stmt = $conn->query("SELECT id, name FROM zones ORDER BY name");
//         echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
//     }

//     public function getStates() {
//         global $conn;
//         $stmt = $conn->query("SELECT id, name FROM states ORDER BY name");
//         echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
//     }

//     public function getLgas() {
//         global $conn;
//         $stmt = $conn->query("SELECT id, name FROM lgas ORDER BY name");
//         echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
//     }

//     public function getDivisions() {
//         global $conn;
//         $stmt = $conn->query("SELECT id, name FROM divisions ORDER BY name");
//         echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
//     }
// }



class DropdownController {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getStatuses(): void {
        header('Content-Type: application/json');
        echo json_encode(['active', 'inactive']);
    }

    public function getRoles(): void {
        header('Content-Type: application/json');
        echo json_encode(['user', 'admin', 'supervisor']);
    }

    public function getAgencies(): void {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT code, name FROM agencies ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getZones(): void {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT id, name FROM zones ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getStates(): void {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT id, name FROM states ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getLgas(): void {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT id, name FROM lgas ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getDivisions(): void {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT id, name FROM divisions ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
