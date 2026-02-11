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
    public function getPatientById($id) {
        $patient = $this->patient->getPatientById($id);
        if ($patient) {
            successResponse($patient, 200);
        }else {
            errorResponse('Patient not found', 404);
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
    
    public function updatePatient($id, $data){
        $patient = $this->patient->update($id, $data);
        if ($patient) {
            successResponse($patient, 'Patient updated successfully', 200);
        } else {
            errorResponse('Failed to update patient', 500);
        }
    }

    public function deletePatient($id){
        $patient = $this->patient->delete($id);
        if ($patient) {
            successResponse($patient, 'Patient deleted successfully', 200);
        } else {
            errorResponse('Failed to delete patient', 500);
        }
    }
}



