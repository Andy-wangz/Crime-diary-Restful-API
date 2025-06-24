<?php
// AuditLogger.php (unchanged)
class AuditLogger {
    private PDO $conn;

    public function __construct(PDO $pdo) {
        $this->conn = $pdo;
    }

    public function log(string $tableName, int $recordId, string $action, string $officerUsername, ?string $description = null,  ?string $url = null): void {
        $sql = "INSERT INTO audit_logs (table_name, record_id, action, officer_username, description, url)
                VALUES (:table_name, :record_id, :action, :officer_username, :description, :url)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':table_name'     => $tableName,
            ':record_id'      => $recordId,
            ':action'         => $action,
            ':officer_username' => $officerUsername,
            ':description'    => $description,
            ':url'             => $url        ]);
    }
}
