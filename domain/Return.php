<?php

class ReturnItem {
    private $return_id;
    private $material_id;
    private $first_name;
    private $last_name;
    private $email;
    private $checkout_date;
    private $due_date;
    private $return_date;

    public function __construct($return_id, $material_id, $first_name, $last_name,
                                $email, $checkout_date, $due_date, $return_date) {
        $this->return_id     = $return_id;
        $this->material_id   = $material_id;
        $this->first_name    = $first_name;
        $this->last_name     = $last_name;
        $this->email         = $email;
        $this->checkout_date = $checkout_date;
        $this->due_date      = $due_date;
        $this->return_date   = $return_date;
    }

    public function getReturnId()     { return $this->return_id; }
    public function getMaterialId()   { return $this->material_id; }
    public function getFirstName()    { return $this->first_name; }
    public function getLastName()     { return $this->last_name; }
    public function getEmail()        { return $this->email; }
    public function getCheckoutDate() { return $this->checkout_date; }
    public function getDueDate()      { return $this->due_date; }
    public function getReturnDate()   { return $this->return_date; }
}
?>