<?php

require_once(dirname(__FILE__).'/db/db.php');
require_once(dirname(__FILE__).'/ws/webserviceconfig.php');

ini_set("soap.wsdl_cache_enabled", "0");
header("Content-type: text/x-json");

if (isset($_GET['action'])) 
    $action = $_GET['action'];
else 
    $action = "";

    
session_start(); 
$userId = $_SESSION["userid"];
$userRole = $_SESSION["role"];
$userdb = db_getUserName($userId);
$username = $userdb['login'];
$timeZoneId = $userdb['timezone'];
    
    
//Handle the available actions
if($action == "getTimeZones"){
	$client = new SoapClient(WSDL_VL, array('location' => LOCATION_VL));
	$result = $client->getAvailableTimeZoneIds();
	echo json_encode($result);
	
}else if($action == "getUserTimeZone"){
	$client = new SoapClient(WSDL_VL, array('location' => LOCATION_VL));
	$params = array("requestingUser"=>$username, "userName"=>$username);
	$result = $client->getUserDefaultTimeZoneId($params);
	echo json_encode($timeZoneId);
	
}else if($action == "setUserTimeZone"){
	
	if (isset($_GET['timezone'])) 
	    $timeZoneId = $_GET['timezone'];
	else 
	    $timeZoneId = "";
	
	$client = new SoapClient(WSDL_VL, array('location' => LOCATION_VL));
	$params = array("requestingUser"=>$username, "userName"=>$username, "timeZoneId"=>$timeZoneId);
	$result = $client->setUserDefaultTimeZoneId($params);

	if($result->success==1){
		db_setUserTimeZone($userId, $timeZoneId);
			
	}
	$response = array("role"=>$userRole, "userid"=>$userId);
	echo json_encode($response);
}

?>
