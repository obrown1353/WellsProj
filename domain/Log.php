<?php

class Log {
    private $log_id;
    private $log_type;
    private $message; 
    private $log_time;

    
    function __construct($log_id, $log_type, $message, $log_time) {
        $this->log_id = $log_id;
        $this->log_type = $log_type;
        $this->message = $message;
        $this->log_time = $log_time;
    }

    function getLogID() {
        return $this->log_id;
    }

    function getLogType() {
        return $this->log_type;
    }
    function getMessage() {
        return $this->message;
    }

    function getLogTime() {
        return $this->log_time;
    }
}
?>