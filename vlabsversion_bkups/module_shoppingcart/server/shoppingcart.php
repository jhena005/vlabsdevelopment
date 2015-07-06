<?php

require_once('shoppingcart_utilities.php');
require_once ('ws/webserviceconfig.php');

session_start();
$user = $_SESSION['userid'];
$cart = $_SESSION[$user.'cart'];

header("Content-type: text/x-json");

if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = "";
}


if ($action == "add") {
    if (isset($_POST['type'])) {
        $t = $_POST['type'];
    } else {
        $t = "";
    }
    
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    } else {
        $id = "";
    }
    

	$success = ws_isResourceAvailable($id, 1);
	
	if($success == true){
	
	    $prefix = 'i-';
	    if($t=='package')
	        $prefix = 'p-';
	
	    if ($cart) {
	        $cart .= ',' . $prefix.$id;
	    } else {
	        $cart = $prefix.$id;
	    }
	    $_SESSION[$user.'cart'] = $cart;	 
	       		
	}
	
	echo true;

} else if ($action == "delete") {

    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    } else {
        $id = "";
    }

    
	$cart = deleteItem($id, $cart);
	$_SESSION[$user.'cart'] = $cart;
    


} else if ($action == "update") {

    if ($cart) {
        $newcart = '';
        foreach ($_POST as $key => $value) {
            if (stristr($key, 'qty')) {
                $id = str_replace('qty', '', $key);
                $items = ($newcart != '') ? explode(',', $newcart) : explode(',', $cart);
                $newcart = '';
                foreach ($items as $item) {
                    if ($id != $item) {
                        if ($newcart != '') {
                            $newcart .= ',' . $item;
                        } else {
                            $newcart = $item;
                        }
                    }
                }
                
                for ($i = 1; $i <= $value; $i++) {
                    if ($newcart != '') {
                        $newcart .= ',' . $id;
                    } else {
                        $newcart = $id;
                    }
                }
            }
        }
    }
    
    
    $items = explode(',', $newcart);
    $newcart = '';
    $contents = array();
    foreach ($items as $item) {
    	$contents[$item] = (isset($contents[$item])) ? $contents[$item] + 1 : 1;
    }

    $formattedShopCartItems = array();
    $result = array();
     
    foreach ($contents as $i => $qty) {
    	$id = substr($i, 2);
    	if(ws_isResourceAvailable($id, $qty)){
    		$item = db_getItem($id);
    		$prefix = "i-";
    		if($item->type=="PACKAGE")
    			$prefix = "p-";
    		for($i=0;$i<$qty;$i++){
    			if ($newcart) {
			        $newcart .= ',' . $prefix.$id;
			    } else {
			        $newcart = $prefix.$id;
			    }  			
    		}
    	}
    }

    $_SESSION[$user.'cart'] = $newcart;
    
} else if($action == "reloadShoppingCart"){
    cleanCart();
    $shoppingcart = showCart();
    $preview = writeShoppingCart();
    $cartpreview= writeCartPreview();
    $result = array('cart'=>$shoppingcart,'preview' => $preview,'cartpreview'=>$cartpreview, 'session'=>$cart);
    echo json_encode($result);

}


function deleteItem($id, $cart){

	$items = explode(',', $cart);
	$newcart = '';
	foreach ($items as $item) {
		 
		if ($id != $item) {
			if ($newcart != '') {
				$newcart .= ',' . $item;
			} else {
				$newcart = $item;
			}
		}
	}
	
	return $newcart;
    
}

?>