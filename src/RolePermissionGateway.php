<?php

class RolePermissionGateway {
    private PDO $conn;
    public function __construct(private Database $database) {
        $this->conn = $database->getConnection();

    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    // public function getRolename(string $name): array|false {
    //     $sql = "SELECT * FROM roles WHERE name = :name";
    //     $stmt = $this->conn->prepare($sql); // if I had declear public $conn
    //     $stmt->execute(["name" => $name]);
    //     return $stmt->fetch(PDO::FETCH_ASSOC);
    // }


    public function addOfficerRole(string $name): ?array {
        $sql = "INSERT INTO roles (name) VALUES (:name)";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([':name' => $name])) {
            return [
                'id' => (int) $this->conn->lastInsertId(),
                'name' => $name
            ];
        }
    
        return null;
    }
    

    public function getAllRoles(): array {
        $sql = "SELECT * FROM roles";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


            $data[] = $row;

        }
        return $data;

    }


    public function getAllPermissions(): array {
        $sql = "SELECT * FROM permissions";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


            $data[] = $row;

        }
        return $data;

    }



    // public function addOfficerPermission(string $name): bool {
    //     $sql = "INSERT INTO permissions (name) VALUES (:name)";
    //     $stmt = $this->conn->prepare($sql);
    //     return $stmt->execute([':name' => $name]);
    // }


    public function addOfficerPermission(string $name): ?array {
        $sql = "INSERT INTO permissions (name) VALUES (:name)";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([':name' => $name])) {
            return [
                'id' => (int) $this->conn->lastInsertId(),
                'name' => $name
            ];
        }
    
        return null;
    }
    



    public function assignPermissionToRole(int $roleId, int $permissionId): bool {
        $sql = "INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (:role_id, :permission_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':role_id' => $roleId, ':permission_id' => $permissionId]);
    }

    public function getPermissionsForRole(int $roleId): array {
        $sql = "SELECT p.name FROM permissions p
                INNER JOIN role_permission rp ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
