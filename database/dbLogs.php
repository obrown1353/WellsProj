<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Log.php');

//encapsulates row information from query into a log object for function access.
function prepare_log_object($log){
    return new Log(
        $log['log_id'], 
        $log['log_type'], 
        $log['message'], 
        $log['log_time']
    );
}

//Fetchs all logs in dblogs, can return empty array
function fetch_all_logs(){
    $con = connect();
    $query = "SELECT * FROM dblogs ORDER BY log_time DESC";
    $result = mysqli_query($con, $query);
    $logs = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = prepare_log_object($row);
    }
    mysqli_close($con);  
    return $logs;
}

//Fetchs all logs by type
function fetch_logs_by_type($type){
    $con = connect();
    $query = "SELECT * FROM dblogs WHERE log_type = '" . $type . "' ORDER BY log_time DESC";
    $result = mysqli_query($con, $query);
    $logs = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = prepare_log_object($row);
    }
    mysqli_close($con);  
    return $logs;
}

//Fetchs a logs by a given id
function fetch_log_by_id($id){
    $con = connect();
    $query = "SELECT * FROM dblogs WHERE log_id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    $log = mysqli_fetch_assoc($result);
    if (!$result) {
        mysqli_close($con); 
        return null;
    }
    mysqli_close($con); 
    return prepare_log_object($log);
}


//Adds a new log to database based on passed log object. Requires a log object
function new_log(Log $log){
    $con = connect();
    $query = "INSERT INTO `dblogs` (`log_type`, `message`, `log_time`)
    VALUES ('" . $log->getLogType() . "', 
            '" . $log->getMessage() . "',
            '" . $log->getLogTime() . "')";
    $result = mysqli_query($con, $query);
    mysqli_commit($con);
    mysqli_close($con);
    return $result;
}

//Removes a log from the log database if the log exists
function remove_log($id){
    $con = connect();
    $query = "SELECT * FROM dblog WHERE log_id = '" . $id . "'";
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) { //if checkout doesn't exist
        mysqli_close($con);
        return false; 
    }
    $query = "DELETE FROM dblog WHERE log_id = '" . $id . "'";
    $result = mysqli_query($con,$query);
    mysqli_close($con);
    return true;
}

function delete_logs_by_ids($ids) {
    $con = connect();
    $ids_str = implode(',', array_map('intval', $ids));
    $query = "DELETE FROM dblogs WHERE log_id IN ($ids_str)";
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

function delete_logs_before_date($date){
    $con = connect();
    $query = "DELETE FROM dblogs WHERE log_time < '" . $date . "'";
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

?>
