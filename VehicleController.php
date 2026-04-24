<?php
class VehicleController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function create($data) {
        $errors = CarDataValidation::validate($data, false); //validate input data
        if (!empty($errors)) { //if fails-> error
            $this->sendErrorResponse($errors);
        }

        $newId = $this->model->create($data);
        //success response return, new ID
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(["status" => "success", "id" => $newId]);
        exit;
    }

    public function update($id, $data) {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id < 1) { //positive int
            $this->sendErrorResponse(["Invalid ID"]);
        }

        $errors = CarDataValidation::validate($data, true); //validate input data
        if (!empty($errors)) {
            $this->sendErrorResponse($errors);
        }

        $currentVehicle = $this->model->find($id);
        if (!$currentVehicle) {
            $this->sendErrorResponse(["Vehicle not found"]); //if cant be found, error
        }
        //update new data with xisting data
        $this->model->update($id, $data, $currentVehicle);

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(["status" => "success"]);
        exit; //success response to user
    }

    public function getAll($params = []) {
        $vehicles = $this->model->getAll($params);

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($vehicles);
        exit;
    }

    public function delete($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id < 1) { //validates ID
            $this->sendErrorResponse(["Invalid ID"]);
        }

        $currentVehicle = $this->model->find($id); //check if exists
        if (!$currentVehicle) {
            $this->sendErrorResponse(["Vehicle not found"]); //error if doesn't exist
        }

        $this->model->delete($id); //deletes it from DB

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(["status" => "success"]); //informing user
        exit;
    }

    private function sendErrorResponse($errors) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(["status" => "error", "errors" => $errors]);
        exit;
    }
}