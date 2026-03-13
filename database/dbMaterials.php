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

//Fetchs all materials in dbmaterials
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
?>