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


    public function getPatientById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM patients WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $age, $gender, $phone, $address) {
        $stmt = $this->pdo->prepare("INSERT INTO patients (name, age, gender, phone, address) VALUES (:name, :age, :gender, :phone, :address)");
        $stmt->execute([':name' => $name, ':age' => $age, ':gender' => $gender, ':phone' => $phone, ':address' => $address]);
        $id =$this->pdo->lastInsertId();
        $stmt1 = $this->pdo->prepare(
            "SELECT * FROM patients WHERE id = :id"
        );
        $stmt1->execute([':id' => $id]);
        return $stmt1->fetch(
            PDO::FETCH_ASSOC
        );
    }

    public function update($id, $data){
         $fields = [];

        // params actual values : id:1
        $params = ['id' => $id];

        /* binding the values 
        fields : name:name, age:age, phone:phone
        params : name:tom, age:25
        */
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        // implode sets the field 
        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        //execute with acutal value in fields
        $stmt->execute($params);
        

        // fetching updated value for response
        $stmt1 = $this->pdo->prepare(
            "SELECT * FROM patients WHERE id = :id"
        );
        $stmt1->execute([':id' => $id]);
        return $stmt1->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
         // capturing the value before deletion for response
        $stmt1 = $this->pdo->prepare(
            "SELECT * FROM patients WHERE id = :id"
        );
        $stmt1->execute([':id' => $id]);
        
        // deletion
        $stmt = $this->pdo->prepare(
            "DELETE FROM patients WHERE id = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt1->fetch(PDO::FETCH_ASSOC);
    }
}

?>