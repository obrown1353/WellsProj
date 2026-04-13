<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Materials.php');

function prepare_stats_object($stats){
    return new Stats(
        $stats['material_id'], 
        $stats['times_checkedout'], 
        $stats['last_checkout'], 
        $stats['last_return'],
    );
}

//Fetchs stats by a given id
function fetch_stats_by_id($id){
    $con = connect();
    $query = "SELECT * FROM dbstats WHERE material_id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    $stats = mysqli_fetch_assoc($result);
    if (!$result) {
        mysqli_close($con); 
        return null;
    }
    mysqli_close($con); 
    return prepare_stats_object($stats);
}

//follows directly from add_material
function create_new_stats($material_id){
    $con = connect();
    $query = "INSERT INTO `dbstats` (`material_id`)
    VALUES ('" . $material_id . "')";
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}

//follows directly from delete_materials_by_ids and uses the same comma seperated list of ids
function delete_stats($ids_str){
    $con = connect();
    $query = "DELETE FROM dbstats WHERE material_id IN ($ids_str)";
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

//updates material stats if checked out
function update_for_checkout($material_id){
    $con = connect();
    $checkout_date = date('Y-m-d H:i:s');
    $query = "UPDATE dbstats 
    SET times_checkedout = times_checkedout + 1, 
    last_checkout = '" . $checkout_date . "'
    WHERE material_id = '" . $material_id . "'";
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}


//updates material stats if returned
function update_for_return($material_id){
    $con = connect();
    $return_date = date('Y-m-d H:i:s');
    $query = "UPDATE dbstats 
    SET last_return = '" . $return_date . "'
    WHERE material_id = '" . $material_id . "'";
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}