<?php

  class GenericItem {
     
    var $item_name; 
    var $item_description;
    var $unit_price;
    var $quantity;
    
    function GenericItem($name, $desc, $qty, $price) {
      $this->item_name = $name;
      $this->item_description= $desc;
      $this->unit_price = $price;
      $this->quantity = $qty;

    }
    
  }

  
?>
