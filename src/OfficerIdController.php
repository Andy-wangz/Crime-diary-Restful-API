<?php

class OfficerIdController {
    // private OfficerGateway $gateway;

    public function __construct(private OfficerGateway $gateway) {
        // $this->gateway = new OfficerGateway($db);
    }

    public function processRequest(string $method, ?string $id): void {
        if ($method !== "GET") {
            http_response_code(405);
            header("Allow: GET");
            return;
        }

        $officerId = $this->gateway->getOfficerId($id);

        if (! $officerId) {
            http_response_code(404);
            echo json_encode(["error" => "Officer ID not found"]);
            return;
        }

        echo json_encode($officerId);
    }
}
