<?php
class CarDataValidation {
    public static function validate($data, $isUpdate = false) { //its for create, not update
        $errors = [];


        if (!$isUpdate) { //for create
            $required = ['model_name', 'type_id', 'vehicle_type', 'fuel', 'price', 'doors', 'transmission'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                    $errors[] = "The field '$field' is required.";
                }
            }
        }


        if (isset($data['type_id']) && filter_var($data['type_id'], FILTER_VALIDATE_INT) === false) {
            $errors[] = "type_id must be a valid number."; //error if its not a number
        }


        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) { //check if price was sent
            $errors[] = "Price must be a valid positive number.";
        }


        if (isset($data['doors']) && trim((string)$data['doors']) !== '') {
            $doors = filter_var($data['doors'], FILTER_VALIDATE_INT);
            if ($doors === false || $doors < 1)  { // cant accept 0 and negative
                $errors[] = "Doors must be a positive whole number.";
            }
        }


        $allowedTransmission = ['manual', 'automatic'];
        if (isset($data['transmission']) && !in_array(strtolower($data['transmission']), $allowedTransmission)) {
            $errors[] = "Transmission must be manual or automatic.";
        }


        $allowedFuel = ['petrol', 'diesel', 'electric', 'hybrid']; //allowed fuel
        if (isset($data['fuel']) && !in_array(strtolower($data['fuel']), $allowedFuel)) {
            $errors[] = "Invalid fuel type. Allowed: " . implode(', ', $allowedFuel);
        }

        return $errors;
    }
}