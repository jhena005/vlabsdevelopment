<?php


class GenericCart {

    var $item_arr;

    function GenericCart() {

        $this->item_arr = array();
    }

    function AddItem($item) {
        $this->item_arr[] = $item;
    }

    function GetXML() {
        
        header("Content-Type: text/plain");

        //create the xml document
        $xmlDoc = new DOMDocument();

        //create the root element
        $root = $xmlDoc->appendChild(
                        $xmlDoc->createElement("shoppingcart"));

        foreach ($this->item_arr as $item) {

            $itemTag = $root->appendChild(
                            $xmlDoc->createElement("item"));

            $name = $xmlDoc->createElement("name");
            $name->appendChild($xmlDoc->createTextNode($item->item_name));

            $description = $xmlDoc->createElement("description");
            $description->appendChild($xmlDoc->createTextNode($item->item_description));

            $price = $xmlDoc->createElement("price");
            $price->appendChild($xmlDoc->createTextNode($item->unit_price));

            $quantity = $xmlDoc->createElement("quantity");
            $quantity->appendChild($xmlDoc->createTextNode($item->quantity));

            $itemTag->appendChild($name);
            $itemTag->appendChild($description);
            $itemTag->appendChild($price);
            $itemTag->appendChild($quantity);

        }

        return $xmlDoc->saveXML();
    }

    function CheckoutButtonCode() {

		
		$cartXML = urlencode($this->GetXML());
    	
        $output[]="<div id='nopaymentcheckout'>";
        $output[]="	<form id='sc_checkout_form' >";
        $output[]="		<input id='cartxml' type='hidden' name='cart' value='".$cartXML."'>";
        $output[]="		<a href='#' onclick='checkout()'><img src='css/images/icons/checkout.png' alt='No payment checkout' height=30 /></a>";
        $output[]="	</form>";
        $output[]="</div>";

        return join('',$output);
        
        
    }

}

?>