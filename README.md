# Vehicle API

A PHP REST API for managing vehicles.
by Tolis.

# Requirements

- PHP 8.0+
- MySQL
- Laravel Herd (or any local PHP server)

# Setup

1. Clone or copy the project files into your Herd site folder
2. Create a MySQL database called `vehicle_app`
3. Create the vehicles table:

```sql
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(255) NOT NULL,
    type_id INT NOT NULL,
    vehicle_type VARCHAR(255) NOT NULL,
    doors INT DEFAULT NULL,
    transmission VARCHAR(50) DEFAULT NULL,
    fuel VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL
);
```

4. Make sure `database.php` credentials match your local MySQL setup
5. Access the API at: http://vehicle-api.test/index.php

---

# Endpoints

### GET /vehicles
Returns all vehicles. Supports filtering and sorting via query parameters.

```
GET /vehicles
```

## POST /vehicles
Creates a new vehicle. Requires a JSON body.

```
POST /vehicles
Content-Type: application/json

{
    "model_name": "Fiat Panda",
    "type_id": 2,
    "vehicle_type": "car",
    "doors": 4,
    "transmission": "manual",
    "fuel": "petrol",
    "price": 9000
}
```

## PUT /vehicles/{id}
Updates an existing vehicle. Only send the fields you want to change.

```
PUT /vehicles/1
Content-Type: application/json

{
    "price": 9500
}
```

## DELETE /vehicles/{id}
Deletes a vehicle by ID. If it doesn't exists, it informs you.

```
DELETE /vehicles/1
```

---

# Filtering & Sorting

All filters and sorting are applied via query parameters on `GET /vehicles`.

# Sorting

| Parameter | Description |
|---|---|
| `?sort=name_asc` | Sort by model name A → Z |
| `?sort=name_desc` | Sort by model name Z → A |
| `?sort=price_asc` | Sort by price low → high |
| `?sort=price_desc` | Sort by price high → low |

## Price Filter

| Parameter | Description |
|---|---|
| `?price_min=100` | Vehicles with price >= 100 |
| `?price_max=500` | Vehicles with price <= 500 |
| `?price_min=100&price_max=500` | Vehicles between 100 and 500 |

## Transmission Filter

| Parameter | Description |
|---|---|
| `?transmission=manual` | Manual vehicles only |
| `?transmission=automatic` | Automatic vehicles only |

## Type Filter

| Parameter | Description |
|---|---|
| `?type_id=1` | Vehicles of type group 1 |

## Combined Filters

All filters can be combined together:

```
/vehicles?type_id=2&transmission=automatic&price_min=100&sort=price_asc
```

---

# Validation Rules

These rules apply on both create (POST) and update (PUT).

| Field | Rule |
|---|---|
| `model_name` | Required on create |
| `type_id` | Required on create, must be a whole number |
| `vehicle_type` | Required on create |
| `fuel` | Required on create, must be: petrol, diesel, electric, or hybrid |
| `price` | Required on create, must be a number >= 0 |
| `doors` | Optional, must be a positive whole number if provided |
| `transmission` | Optional, must be: manual or automatic if provided |

On update (PUT), required field checks are skipped, you can send only the fields you want to change. However, any field you do send must still pass validation and you will be informed about it. 

---

# Response Format

## Success — single vehicle

```json
{
    "id": 1,
    "model_name": "Fiat Panda",
    "type_id": 2,
    "vehicle_type": "car",
    "doors": 4,
    "transmission": "manual",
    "fuel": "petrol",
    "price": 90
}
```

## Success — create

```json
{
    "status": "success",
    "id": 1
}
```

## Success — update or delete

```json
{
    "status": "success"
}
```

## Error

```json
{
    "status": "error",
    "errors": [
        "The field 'model_name' is required.",
        "Invalid fuel type. Allowed: petrol, diesel, electric, hybrid"
    ]
}
```

---

# File Structure

```
vehicle-api/
│
├── index.php               Entry point — handles routing only
├── VehicleController.php   Handles validation, logic, and responses
├── VehicleModel.php        Handles all database queries
├── CarDataValidation.php   Handles all validation rules
└── database.php            Handles the database connection
```

Each file has one clear responsibility, for the controller never writes SQL.



## Assumptions & Design Decisions

Validation on both create and update: Applies to  all value rules (fuel type, transmission, price, doors) on updates as well, only the required field check is skipped. This prevents invalid data from entering the database through the update route. This feature wasn't applied from my first prototype.

Separate validation class:Validation logic started inside the controller as a private method. I moved it into its own class (named `CarDataValidation`) to keep each file focused on one job.

Separate model class: All database queries were moved from the controller into `VehicleModel.php`. The controller calls the model and handles the response, but never writes SQL directly. This makes the code easier to read and easier to change in the future.

`vehicle_type`: is free text, the task did not specify what values are allowed for `vehicle_type`, so it accepts any non-empty string on create. Can be updated in the future with strictier rules.

No authentication: The task did not include authentication requirements, so the API is open. In a production app, API key can be added.


## What Was Most Important

Validation coverage: was the main priority. The goal was to make sure no invalid data could enter the database whether through create or update. Every field that has a rule is checked before any database query runs, and all errors are returned together in one response so the client knows exactly what to fix.

Clean structure: was the second priority. Each file has one job: routing, logic, database, validation, connection. This makes the code easier to read, easier to test, and easier to extend.

Security: All database queries use prepared statements to prevent SQL injection. Sort values are whitelisted so only safe values are accepted.
