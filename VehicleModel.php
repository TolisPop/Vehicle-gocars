<?php
class VehicleModel {
    private $db; //database connection

    public function __construct($db) {
        $this->db = $db; //inject DB into model
    }

    public function getAll($params = []) {
        $sql = "SELECT * FROM vehicles WHERE 1=1";
        $bindings = [];

        //filter min price
        if (isset($params['price_min']) && is_numeric($params['price_min'])) {
            $sql .= " AND price >= ?"; //add condition to sql
            $bindings[] = $params['price_min'];
        }
        if (isset($params['price_max']) && is_numeric($params['price_max'])) {
            $sql .= " AND price <= ?";
            $bindings[] = $params['price_max']; //add value for placeholder
        }

        $allowedTransmission = ['manual', 'automatic'];
        if (isset($params['transmission']) && in_array(strtolower($params['transmission']), $allowedTransmission)) {
            $sql .= " AND transmission = ?";
            $bindings[] = strtolower($params['transmission']); //strtolower to accept caps lock
        }
        //fitler for ID type
        if (isset($params['type_id']) && filter_var($params['type_id'], FILTER_VALIDATE_INT)) {
            $sql .= " AND type_id = ?";
            $bindings[] = $params['type_id'];
        }
        //sorting options - security feature for SQL injection
        $sortOptions = [
            'name_asc'   => 'model_name ASC',
            'name_desc'  => 'model_name DESC',
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
        ];
        if (isset($params['sort']) && array_key_exists($params['sort'], $sortOptions)) {
            $sql .= " ORDER BY " . $sortOptions[$params['sort']];
        } //sorting if valid option is provided

        $stmt = $this->db->prepare($sql); //sql injection-proof
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); //prepares and executes final sql query
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$id]); //executes with ID as parameter
        return $stmt->fetch(PDO::FETCH_ASSOC); //returns single row
    }

    public function create($data) {
        $sql = "INSERT INTO vehicles (model_name, type_id, vehicle_type, doors, transmission, fuel, price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"; //sql insert query

        $stmt = $this->db->prepare($sql); //prepares statement
        $stmt->execute([ //executes with provided data
            $data['model_name'] ?? null,
            $data['type_id'] ?? null,
            $data['vehicle_type'] ?? null,
            $data['doors'] ?? null,
            $data['transmission'] ?? null,
            $data['fuel'] ?? null,
            $data['price'] ?? null
        ]);
        //return ID of new inserted row
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $currentVehicle) {
        $sql = "UPDATE vehicles SET 
            model_name = ?, type_id = ?, vehicle_type = ?, 
            doors = ?, transmission = ?, fuel = ?, price = ? 
            WHERE id = ?"; //updates all fields

        $stmt = $this->db->prepare($sql); //statement preparation
        $stmt->execute([
            $data['model_name'] ?? $currentVehicle['model_name'],
            $data['type_id'] ?? $currentVehicle['type_id'],
            $data['vehicle_type'] ?? $currentVehicle['vehicle_type'],
            $data['doors'] ?? $currentVehicle['doors'],
            $data['transmission'] ?? $currentVehicle['transmission'],
            $data['fuel'] ?? $currentVehicle['fuel'],
            $data['price'] ?? $currentVehicle['price'],
            $id //if new value exists, is used, otherwise current data is being kept
        ]);

        return $stmt->rowCount(); //returns number of affected rows
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");//prepares delete query
        $stmt->execute([$id]); //executes with ID
        return $stmt->rowCount() > 0; //returns true if deleted, false if not
    }
}