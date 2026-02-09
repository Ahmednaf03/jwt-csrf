<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/response.php';

class Patient {
    private $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPatients() {
        $stmt = $this->pdo->prepare("SELECT * FROM patients");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $age, $gender, $phone, $address) {
        $stmt = $this->pdo->prepare("INSERT INTO patients (name, age, gender, phone, address) VALUES (:name, :age, :gender, :phone, :address)");
        $stmt->execute([':name' => $name, ':age' => $age, ':gender' => $gender, ':phone' => $phone, ':address' => $address]);
        return $this->pdo->lastInsertId();
    }
}

?>