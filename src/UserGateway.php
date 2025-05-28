<?php

class UserGateway {
    // public function __construct(private Database $db) {}

    // public function getUserByEmail(string $email): array|false {
    //     $sql = "SELECT * FROM known_user WHERE email = :email";
    //     $stmt = $this->db->getConnection()->prepare($sql);
    //     $stmt->execute(["email" => $email]);
    //     return $stmt->fetch(PDO::FETCH_ASSOC);
    // }

    // public function register(string $surname, string $firstname, string $email, string $password): bool {
    //     $sql = "INSERT INTO known_user (surname, firstname, email, password) VALUES (:surname, :firstname, :email, :password)";
    //     $stmt = $this->db->getConnection()->prepare($sql);
    //     return $stmt->execute([
    //         "surname" => $surname,
    //         "firstname" => $firstname,
    //         "email" => $email,
    //         "password" => password_hash($password, PASSWORD_DEFAULT),
    //     ]);
    // }


    public function __construct(private Database $database) {}

    public function getUserByEmail(string $email): array|false {
        $sql = "SELECT * FROM known_user WHERE email = :email";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute(["email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register(string $surname, string $firstname, string $email, string $password): bool {
        $sql = "INSERT INTO known_user (surname, firstname, email, password) VALUES (:surname, :firstname, :email, :password)";
        $stmt = $this->database->getConnection()->prepare($sql);
        return $stmt->execute([
            "surname" => $surname,
            "firstname" => $firstname,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

//update password
    public function updatePassword(string $email, string $hashedPassword): bool {
        $stmt = $this->database->getConnection()->prepare("UPDATE known_user SET password = :password WHERE email = :email");
        return $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
    }
    

}
