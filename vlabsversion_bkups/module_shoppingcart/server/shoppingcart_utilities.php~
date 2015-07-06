<?php

require_once(dirname(__FILE__).'/checkout/google_checkout/lib/googlecart.php');
require_once(dirname(__FILE__).'/checkout/google_checkout/lib/googleitem.php');
require_once(dirname(__FILE__).'/checkout/google_checkout/lib/googleshipping.php');
require_once(dirname(__FILE__).'/checkout/google_checkout/lib/googletax.php');


require_once('checkout/nopayment_checkout/lib/genericcart.php');
require_once('checkout/nopayment_checkout/lib/genericitem.php');

//require_once("../../../config.php");
require_once ((dirname(dirname(dirname(dirname(__FILE__))))).'/libraries/configuration.php');
require_once (dirname(__FILE__).'/lib.php');
require_once (dirname(__FILE__).'/db/db.php');




function cleanCart() {
	session_start();
	$user = $_SESSION['userid'];
	$cart = $_SESSION[$user.'cart'];

    if($cart) {
        $items = explode(',', $cart);
        $contents = array();
        foreach ($items as $item) {
            $contents[$item] = (isset($contents[$item])) ? $contents[$item] + 1 : 1;
        }

        foreach ($contents as $i => $qty) {

            $id = substr($i, 2);

            $sql = 'SELECT * FROM mdl_shoppingcart_store_inventory WHERE id = ' . $id . ' and active =1';
            $result = db_getrecords($sql);

            if ($result == null || (is_array($result) && empty($result))) {
                if ($cart) {
                    $items = explode(',', $cart);
                    $newcart = '';
                    foreach ($items as $item) {
                        if ($i != $item) {
                            if ($newcart != '') {
                                $newcart .= ',' . $item;
                            } else {
                                $newcart = $item;
                            }
                        }
                    }
                }
                $cart = $newcart;

            }
        }
    }
    $_SESSION[$user.'cart'] = $cart;
}

function writeShoppingCart() {
	session_start();
	$user = $_SESSION['userid'];
	$cart = $_SESSION[$user.'cart'];

    if (!$cart) {
        return '<p>You have no items in your shopping cart</p>';
    } else {
        // Parse the cart session variable
        $items = explode(',', $cart);
        $s = (count($items) > 1) ? 's' : '';
        return '<p>You have <a href="#" onclick="openShoppingCartTab()" >' . count($items) . ' item' . $s . ' in your shopping cart</a></p>';
    }
    $_SESSION[$user.'cart'] = $cart;
}

function writeCartPreview() {
	session_start();
	$user = $_SESSION['userid'];
	$cart = $_SESSION[$user.'cart'];

    if (!$cart) {
        return '(0) Cart';
    } else {
        // Parse the cart session variable
        $items = explode(',', $cart);
        $s = (count($items) > 1) ? 's' : '';
        return '(' . count($items) . ') Cart';
    }
    $_SESSION[$user.'cart'] = $cart;
}

function showCart() {
	session_start();
	$user = $_SESSION['userid'];
	$cart = $_SESSION[$user.'cart'];

    //No payment Checkout
    //----------------------------------
    $genericCart = new GenericCart();
    //----------------------------------
    //Google Checkout
    //----------------------------------   
    $config_file = "checkout/google_checkout/google.conf";
	$comment = "#";
	
	$fp = fopen($config_file, "r");
	
	while (!feof($fp)) {
	  $line = trim(fgets($fp));
	  if ($line && !ereg("^$comment", $line)) {
	    list($option, $value) = split("=", $line, 2);
	    $config_values[$option] = $value;
	  }
	}
	fclose($fp);
	
	$merchant_id = $config_values['CONFIG_MERCHANT_ID'];  
	$merchant_key = $config_values['CONFIG_MERCHANT_KEY'];  
	$server_type = $config_values['CONFIG_SERVER_TYPE'];  
	$currency = $config_values['CONFIG_CURRENCY'];
	$editCartURL = $config_values['CONFIG_EDIT_URL'];
	$continueShoppingURL = $config_values['CONFIG_CONTINUE_URL'];
       
    $googlecart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
    $googlecart->SetEditCartUrl($editCartURL);
    $googlecart->SetContinueShoppingUrl($continueShoppingURL);
    //----------------------------------
    
    if ($cart) {
        $items = explode(',', $cart);
        $contents = array();
        foreach ($items as $item) {
            $contents[$item] = (isset($contents[$item])) ? $contents[$item] + 1 : 1;
        }

   		$formattedShopCartItems = array();
   		$result = array();
   		
        foreach ($contents as $i => $qty) {
            $type = substr($i, 0, 1);
            $id = substr($i, 2);

            $item = db_getItem($id);

        	$subtotal = $item->price * $qty;
        	$scitem = array(
        	$item->name,
        	$item->description,
        	$item->price,
        	$qty ,
        	$i,
        	$subtotal,
        	$item->id,
        	$type
        	);

        	array_push($formattedShopCartItems, $scitem);
        	 
        	//No payment Checkout
        	//----------------------------------
        	
        	if(!$item->billable){
	        	$genericItem = new GenericItem($item->name, // Item name
	        	$item->description, // Item description
	        	$qty, // Quantity
	        	0); // Unit price

        		$genericCart->AddItem($genericItem);
        	}

        	//----------------------------------
        	//Google Checkout
        	//----------------------------------
        	if($item->billable){
	        	$googleitem = new GoogleItem($item->name, // Item name
	        	$item->description, // Item description
	        	$qty, // Quantity
	        	$item->price); // Unit price

	        	$googleitem->SetMerchantItemId($item->id);
	        	
	        	//TODO:Change email to order email
        	    $googleitem->SetEmailDigitalDelivery("mmilani@it-scholars.com");
        		$googlecart->AddItem($googleitem);
        	}

        	//----------------------------------
        }

    }

    //Checkout Buttons
    $output[] = '<div id="checkoutButtonsContainer">';
    $buttonsCount = 0;
    if(count($genericCart->item_arr)){
    	$buttonsCount++;
    	$output[] = 	$genericCart->CheckoutButtonCode();
    }
 	if(count($googlecart->item_arr)){
 		$buttonsCount++;
    	$output[] = 	$googlecart->CheckoutButtonCode("SMALL");
    	$output[] = 	"<div class='ui-state-error' style='padding: 0pt 0.7em; float:left; width:30em;'><p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span>Please when you process the order with Google Checkout, <strong>DO NOT</strong> choose the option 'Keep my e-mail address confidential' because your order will be lost.</p></div>";
 	}
    $output[] = '</div>';


    $shoppingCart = $formattedShopCartItems;
    $checkoutButtons = join('', $output);
     
    $response = array(
	    	"shoppingCart"=>json_encode($formattedShopCartItems),
	    	"checkoutButtons"=>$checkoutButtons,
    		"buttonsCount"=>$buttonsCount

    );
     
    $_SESSION[$user.'cart'] = $cart; 
    return $response;
    

}



	

?>
