<?php

class GeoGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function getAgencies(): array {
        $stmt = $this->conn->query("SELECT id, name FROM agencies ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getZones(array $params): array {
        if (isset($params['agency_id'])) {
            $stmt = $this->conn->prepare("SELECT id, name FROM zones WHERE agency_id = :agency_id ORDER BY name");
            $stmt->bindValue(':agency_id', $params['agency_id'], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->conn->query("SELECT id, name FROM zones ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStates(): array {
        $stmt = $this->conn->query("SELECT id, name FROM states ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLgas(array $params): array {
        if (!isset($params['state_id'])) {
            return [];
        }

        $stmt = $this->conn->prepare("SELECT id, name FROM lgas WHERE state_id = :state_id ORDER BY name");
        $stmt->bindValue(':state_id', $params['state_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDivisions(array $params): array {
        if (isset($params['agency_id'])) {
            $stmt = $this->conn->prepare("SELECT id, name FROM divisions WHERE agency_id = :agency_id ORDER BY name");
            $stmt->bindValue(':agency_id', $params['agency_id'], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->conn->query("SELECT id, name FROM divisions ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
