<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/JWT.php';


class PatientController {
    private $patient;
    public function __construct(PDO $pdo) {
        $this->patient = new Patient($pdo);
    }


    public function getPatients () {
        $patients = $this->patient->getAllPatients();
        if ($patients) {
            successResponse($patients, 200);
        }else {
            errorResponse('No patients found', 404);
        }
    }
    public function createPatient ($name, $age, $gender, $phone, $address) {
    $patient =  $this->patient->create($name, $age, $gender, $phone, $address);

    if ($patient) {
        successResponse($patient, 'Patient created successfully', 201);
    } else {
        errorResponse('Failed to create patient', 500);

    }
}

}



