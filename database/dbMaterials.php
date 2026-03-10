<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Materials.php');

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
    return $material;
}

//Fetchs all materials in dbmaterials
function fetch_all_materials(){
    $con = connect();
    $query = "SELECT * FROM dbmaterials";
    $result = mysqli_query($con, $query);
    $materials = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = $row;
    }
    mysqli_close($con);  
    return $materials;
}
?>