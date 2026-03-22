<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Return.php');

function prepare_return_object($row) {
    return new ReturnItem(
        $row['return_id'],
        $row['material_id'],
        $row['first_name'],
        $row['last_name'],
        $row['email'],
        $row['checkout_date'],
        $row['due_date'],
        $row['return_date'],
    );
}

// Fetch all returned items — auto-creates the table if it doesn't exist yet
function fetch_all_returns() {
    $con = connect();

    // Create table if it doesn't exist
    $create = "CREATE TABLE IF NOT EXISTS dbreturns (
        return_id    INT AUTO_INCREMENT PRIMARY KEY,
        material_id  INT          NOT NULL,
        first_name   VARCHAR(100) NOT NULL,
        last_name    VARCHAR(100) NOT NULL,
        email        VARCHAR(255) NOT NULL,
        checkout_date DATETIME    NOT NULL,
        due_date      DATETIME    NOT NULL,
        return_date   DATETIME    NOT NULL
    )";
    mysqli_query($con, $create);

    $query = "SELECT * FROM dbreturns ORDER BY return_date DESC";
    $result = mysqli_query($con, $query);
    $returns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $returns[] = prepare_return_object($row);
    }
    mysqli_close($con);
    return $returns;
}

// Add a new return record (call this when someone returns a material)
function add_return($material_id, $first_name, $last_name, $email, $checkout_date, $due_date) {
    $con = connect();

    // Create table if it doesn't exist
    $create = "CREATE TABLE IF NOT EXISTS dbreturns (
        return_id    INT AUTO_INCREMENT PRIMARY KEY,
        material_id  INT          NOT NULL,
        first_name   VARCHAR(100) NOT NULL,
        last_name    VARCHAR(100) NOT NULL,
        email        VARCHAR(255) NOT NULL,
        checkout_date DATETIME    NOT NULL,
        due_date      DATETIME    NOT NULL,
        return_date   DATETIME    NOT NULL
    )";
    mysqli_query($con, $create);

    $return_date = date('Y-m-d H:i:s');
    $query = 'INSERT INTO dbreturns (material_id, first_name, last_name, email, checkout_date, due_date, return_date)
              VALUES ("' . $material_id . '","' . $first_name . '","' . $last_name . '","' .
              $email . '","' . $checkout_date . '","' . $due_date . '","' . $return_date . '")';
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}
?>