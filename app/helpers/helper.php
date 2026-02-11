<?php
require_once __DIR__ . '/../models/User.php';
class Helper {
    private User $userModel;

    public function __construct(PDO $pdo) {
        $this->userModel = new User($pdo);
    }
    public function getRole($id) {
       $user =  $this->userModel->getById($id);
    // $user = $id;
       return $user;
    }
}


?>