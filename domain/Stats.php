<?php

class Stats {
    private $material_id;
    private $times_checkedout;
    private $last_checkout;
    private $last_return;

    function __construct($material_id, $times_checkedout, $last_checkout, $last_return) {
        $this->material_id = $material_id;
        $this->times_checkedout = $times_checkedout;
        $this->last_checkout = $last_checkout;
        $this->last_return = $last_return;
    }

    function getMaterialID(){
        return $this->material_id;
    }

    function getTimesCheckedOut(){
        return $this->times_checkedout;
    }

    function getLastCheckout(){
        return $this->last_checkout;
    }

    function getLastReturn(){
        return $this->last_return;
    }
}
?>