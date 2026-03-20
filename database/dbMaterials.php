<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Materials.php');

//encapsulates row information from query into a materials object for function access.
function prepare_material_object($material){
    return new Materials(
        $material['material_id'], 
        $material['name'], 
        $material['location'], 
        $material['resource_type'],
        $material['isbn'],
        $material['author'],
        $material['description'],
        $material['copy_capacity'],
        $material['copy_instock'],
    );
}

//Fetchs all materials in dbmaterials, can return empty array
function fetch_all_materials(){
    $con = connect();
    $query = "SELECT * FROM dbmaterials";
    $result = mysqli_query($con, $query);
    $materials = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = prepare_material_object($row);
    }
    mysqli_close($con);  
    return $materials;
}

//Fetchs a material by a given id
function fetch_material_by_id($id){
    $con = connect();
    $query = "SELECT * FROM dbmaterials WHERE material_id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    $material = mysqli_fetch_assoc($result);
    if (!$result) {
        mysqli_close($con); 
        return null;
    }
    mysqli_close($con); 
    return prepare_material_object($material);
}

//Fetchs a material by a given name (Requires exact match, should be updated later to get materials with similar names)
function fetch_material_by_name($name){
    $con = connect();
    $query = "SELECT * FROM dbmaterials WHERE name = '" . $name . "'";
    $result = mysqli_query($con, $query);
    $materials = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = prepare_material_object($row);
    }
    mysqli_close($con);  
    return $materials;
}

//Fetchs a material by a given query (usually in the form of a processed search for materials)
function fetch_materials_by_query($query){
    $materials = fetch_all_materials();
    $queried_materials = [];
    foreach ($materials as $material) {
	    if (
		   str_contains(strtolower((string)$material->getName()), $query) ||
        	   str_contains(strtolower((string)$material->getAuthor()), $query) ||
        	   str_contains(strtolower((string)$material->getDescription()), $query) ||
        	   str_contains(strtolower((string)$material->getISBN()), $query)
    		) {
        	$queried_materials[] = $material;
	    }
    }
    return $queried_materials;
}

//Updates the copy_instock of a material for a self service transaction. isCheckout is true for checkout, false for return. 
//Error checking should be prevented before this function is called. 
function self_service_update($id, $isCheckout){
    $con = connect();
    if ($isCheckout){ //Checkout transaction
        $query = "UPDATE dbmaterials SET copy_instock = copy_instock - 1 WHERE material_id = '" . $id . "'";
    } else { //Return transaction
        $query = "UPDATE dbmaterials SET copy_instock = copy_instock + 1 WHERE material_id = '" . $id . "'";
    }
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}
?>