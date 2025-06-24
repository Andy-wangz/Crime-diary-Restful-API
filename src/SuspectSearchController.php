<?php

class SuspectSearchController {
    public function __construct(
        private SuspectSearchGateway $gateway
    ) {}

    public function processRequest(string $method, ?string $id): void {
        if ($method === "GET") {
            $query = $_GET['search'] ?? '';
            $suspects = $this->gateway->searchSuspects($query);

            echo json_encode(['suspects' => $suspects]);
        } else {
            http_response_code(405);
            header("Allow: GET");
        }
    }
}
