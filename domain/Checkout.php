<?php

class Checkout {
    private $checkout_id;
    private $material_id;
    private $first_name;
    private $last_name; 
    private $email;
    private $checkout_date;
    private $due_date;
    
    function __construct($checkout_id, $material_id, $first_name, $last_name, $email, $checkout_date, $due_date) {
        $this->checkout_id = $checkout_id;
        $this->material_id = $material_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->checkout_date = $checkout_date;
        $this->due_date = $due_date;
    }

    function getCheckoutID(){
        return $this->checkout_id;
    }
    function getMaterialID() {
        return $this->material_id;
    }

    function getFirstName() {
        return $this->first_name;
    }
    function getLastName() {
        return $this->last_name;
    }

    function getEmail() {
        return $this->email;
    }

    function getCheckoutDate() {
        return $this->checkout_date;
    }

    function getDueDate() {
        return $this->due_date;
    }

    function isOverdue(){
        $today = new DateTime();
        $due = new DateTime($this->getDueDate());
        if ($due < $today) {
            return true;
        } else {
            return false;
        }
    }
}
?>