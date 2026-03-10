<?php

class Materials {
    private $material_id;
    private $name;
    private $location;
    private $resource_type;
    private $isbn;
    private $author;
    private $description;
    private $copy_capacity;
    private $copy_instock;
    
    function __construct($material_id, $name, $location, $resource_type, $isbn, $author, $description, $copy_capacity, $copy_instock) {
        $this->material_id = $material_id;
        $this->name = $name;
        $this->location = $location;
        $this->resource_type = $resource_type;
        $this->isbn = $isbn;
        $this->author = $author;
        $this->description = $description;
        $this->copy_capacity = $copy_capacity;
        $this->copy_instock = $copy_instock;
    }

    function getMaterialID() {
        return $this->material_id;
    }

    function getName() {
        return $this->name;
    }
    function getLocation() {
        return $this->location;
    }

    function getResourceType() {
        return $this->resource_type;
    }

    function getISBN() {
        return $this->isbn;
    }

    function getAuthor() {
        return $this->author;
    }

    function getDescription() {
        return $this->description;
    }

    function getCopyCapacity() {
            return $this->copy_capacity;
    }

    function getCopyInstock() {
            return $this->copy_instock;
    }
}