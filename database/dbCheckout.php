<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Checkout.php');

//encapsulates row information from query into a checkout object for function access.
function prepare_checkout_object($material){
    return new Checkout(
        $material['checkout_id'], 
        $material['material_id'], 
        $material['first_name'], 
        $material['last_name'],
        $material['email'],
        $material['checkout_date'],
        $material['due_date'],
    );
}

//Fetchs all materials in dbcheckout, can return empty array
function fetch_all_checkouts(){
    $con = connect();
    $query = "SELECT * FROM dbcheckout";
    $result = mysqli_query($con, $query);
    $checkouts = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $checkouts[] = prepare_checkout_object($row);
    }
    mysqli_close($con);  
    return $checkouts;
}

//Fetchs a checkout by a given checkout id
function fetch_material_by_checkout_id($id){
    $con = connect();
    $query = "SELECT * FROM dbcheckout WHERE checkout_id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    $checkout = mysqli_fetch_assoc($result);
    if (!$result) {
        mysqli_close($con); 
        return null;
    }
    mysqli_close($con); 
    return prepare_checkout_object($checkout);
}

//Fetchs a material by a given material id (multiple checkouts can have the same material id, in case a material has multiple copies)
function fetch_checkout_by_material_id($id){
    $con = connect();
    $query = "SELECT * FROM dbcheckout WHERE material_id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    $checkouts = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $checkouts[] = prepare_checkout_object($row);
    }
    mysqli_close($con);  
    return $checkouts;
}

//Adds a new checkout entry to the database
function new_checkout($material_id, $first_name, $last_name, $email, $checkout_date, $due_date){
    $con = connect();
    $query = 'INSERT INTO dbcheckout (material_id, first_name, last_name, email, checkout_date, due_date) 
    VALUES ("' .
            $material_id . '","' .
            $first_name . '","' .
            $last_name . '","' .
            $email . '","' .
            $checkout_date . '","' .
            $due_date  . '");';
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}

//Removes a checkout from the checkout database if the material is returned
function remove_checkout($material_id, $email){
    $con=connect();
    $query = "SELECT * FROM dbcheckout WHERE material_id = '" . $material_id . "' AND email = '". $email ."'";
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) { //if checkout doesn't exist
        mysqli_close($con);
        return false; 
    }
    $query = "DELETE FROM dbcheckout WHERE material_id = '" . $material_id . "' AND email = '". $email ."'";
    $result = mysqli_query($con,$query);
    mysqli_close($con);
    return true;
}
?>
