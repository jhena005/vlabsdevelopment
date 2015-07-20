<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

/*
 * dbadmin.php
 *
 * This class contains methods concerned with the eFront
 * vLabs modules database administration.
 *
 */


require_once((dirname(__FILE__)).'/db/db.php');


//$config_file = "checkout/google_checkout/google.conf"; jh
//$comment = "#"; jh

/*
$fp = fopen($config_file, "r");

while (!feof($fp)) {
  $line = trim(fgets($fp));
  if ($line && !ereg("^$comment", $line)) {
    $pieces = explode("=", $line);
    $option = trim($pieces[0]);
    $value = trim($pieces[1]);
    $config_values[$option] = $value;
  }
}
fclose($fp);
*/ 


/*

$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $currency);
*/

if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	$action = "";
}


if (isset($_POST['email'])) {
	$email = $_POST['email'];
} else {
	$email = "";
}


//Reload orders of an specific user depending on who is logged in
if ($action == "getModules") {

	//echo '<script type="text/javascript">alert("In reloadOrders")</script>';
	
		//echo '<script type="text/javascript">alert("user id is: '.$userid .'")</script>';
	//call to the db
	$modules = db_getModules();
	//echo '<script type="text/javascript">alert("after db_getUserById")</script>';

    echo json_encode($modules);

	//Reload all orders of all users. Only used for administartor view
}


?>
