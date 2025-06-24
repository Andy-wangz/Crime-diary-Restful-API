<?php

class SuspectSearchGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function searchSuspects(string $query): array {
        $sql = "SELECT * FROM suspects 
                WHERE LOWER(nin) LIKE :nin OR
                      LOWER(suspect_code) LIKE :code OR
                      LOWER(first_name) LIKE :first OR
                      LOWER(last_name) LIKE :last
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $search = '%' . strtolower($query) . '%';
        $stmt->bindValue(':nin', $search, PDO::PARAM_STR);
        $stmt->bindValue(':code', $search, PDO::PARAM_STR);
        $stmt->bindValue(':first', $search, PDO::PARAM_STR);
        $stmt->bindValue(':last', $search, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
