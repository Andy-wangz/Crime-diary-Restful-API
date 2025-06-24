<?php

// class MetaController {
//     private PDO $conn;

//     public function __construct(PDO $pdo) {
//         $this->conn = $pdo;
//     }

//     // Get all unique roles from officers table
// public function getRoles(): void {
//     $stmt = $this->conn->prepare("SELECT DISTINCT role FROM officers ORDER BY role ASC");
//     $stmt->execute();
//     echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
// }

// // Get all unique statuses from officers table
// public function getStatuses(): void {
//     $stmt = $this->conn->prepare("SELECT DISTINCT status FROM officers ORDER BY status ASC");
//     $stmt->execute();
//     echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
// }

// // Get all agencies
// public function getAgencies(): void {
//     $stmt = $this->conn->prepare("SELECT id, name FROM agencies ORDER BY name ASC");
//     $stmt->execute();
//     echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
// }


// }
