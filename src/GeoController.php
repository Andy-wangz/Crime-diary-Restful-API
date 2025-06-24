<?php

class GeoController {
    public function __construct(
        private GeoGateway $gateway
    ) {}

    public function processRequest(string $method, ?string $id): void {
        $path = strtok($_SERVER['REQUEST_URI'], '?');

        if ($method === 'GET') {
            switch ($path) {
                case '/crime_api/agencies':
                    echo json_encode($this->gateway->getAgencies());
                    return;

                case '/crime_api/zones':
                    echo json_encode($this->gateway->getZones($_GET));
                    return;

                case '/crime_api/states':
                    echo json_encode($this->gateway->getStates());
                    return;

                case '/crime_api/lgas':
                    echo json_encode($this->gateway->getLgas($_GET));
                    return;

                case '/crime_api/divisions':
                    echo json_encode($this->gateway->getDivisions($_GET));
                    return;

                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Invalid geo endpoint']);
                    return;
            }
        }

        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
    }
}
