<?php

//require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/libraries/configuration.php');
require_once(dirname(__FILE__).'/db/db.php');
require_once (dirname(__FILE__).'/ws/webserviceconfig.php');
//require_once(dirname(__FILE__).'/shoppingcart_utilities.php');
require_once (dirname(__FILE__).'/packages.php');



ini_set("soap.wsdl_cache_enabled", "0");



if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	$action = "";
}


if ($action == "reload") {
	header('Content-Type: text/x-json');
	//echo '<script type="text/javascript">alert("In store.php action == reload")</script>';

	if (isset($_POST['user'])) {
		$user = $_POST['user'];
	} else {
		$user = "";
	}

	$timeZoneId = db_getUserTimeZone($user)['timezone'];
	$courses = getAvailCourses($user);
	$courses_arr = array();

	foreach ($courses as $course) {
		array_push($courses_arr, $course['id']);
	}

	try {
		
		$params = array('courseId' => $courses_arr);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
		$response = $client->getCreditTypesByCourse($params);
		
		if (!is_array($response->creditType)){
			$references = array($response->creditType);
		}else{
			$references = $response->creditType;
		}
		
		$items = array();
		$itemsForPackages = array();

		foreach ($references as $reference) {			

			$itemsbyref = db_getItemsByReference($reference->id);
			
			if ($itemsbyref != null)
				$items = array_merge($items, $itemsbyref);

			//Get elegible items for packages
			$itemsForPkgbyref= db_getPackageItemsByReference($reference->id);

			if ($itemsForPkgbyref != null)
				$itemsForPackages = array_merge($itemsForPackages, $itemsForPkgbyref);

		}	
		
		$formattedStoreItems = array();

		if (is_array($items)) {

		
			$format = "%s 
					  <strong>Description:</strong>%s<br /> 
					  <strong>Price:</strong>%s ";
			
			$formatPackage = "%s 
					  <strong>Description:</strong>%s 
					  <strong>Price:</strong>%s ";
			
			foreach ($items as $item) {
				
				$detail = sto_getItemDescription($item['id']);
				
				foreach ($references as $reference)
				{
					if($reference->id == $item->referenceid)
						$referenceName = $reference->name;
						
				}
				
				$description = "";				
				if($item['description'] !=""){
					$description = 	$item['description']."<br/>";
				}
				
				$price = "Not billable";
				if($item->billable=="1"){
					$price = "$".$item['price'];
				}
				
				
				 $product = array(
				 'item',
				 "<strong>".$item['name']."</strong>",
				 sprintf($format, $description , $detail, $price ),
				 $item['id']
				 );
				 
				 array_push($formattedStoreItems, $product);
			}

			$packages = getPackagesWithItems($itemsForPackages);

			foreach ($packages as $package) {
				
				$description = "";				
				if($item['description'] !=""){
					$description = 	$package['description']."<br/>";
				}
				
				$price = "Not billable";
				if($package['billable']=="1"){
					$price = "$".$package['price'];
				}
				
				$detail = sto_getItemDescription($package['id']);
				
				$product = array(
				'package',
				"<strong>".$package->name."</strong>",
				sprintf($formatPackage, $description, $detail, $price),
				$package['id']
				);
				
				array_push($formattedStoreItems, $product);
			}
		}

		echo json_encode($formattedStoreItems);
		
		
	} catch (Exception $e) {
		echo $e->getMessage();
		
	} catch (SoapFault $soapfault) {
		echo $soapfault->getMessage();
	}
} else if ($action == "getItem") {
	
	if (isset($_POST['itemid'])) {
		$id = $_POST['itemid'];
	} else {
		$id = "";
	}

	 $item = db_getItem($id);
    $formattedItem= array();
    foreach ($item as $i) {
        //echo '<script type="text/javascript">alert("In store.php getItem foreach loop")</script>';

        $formattedItem = array("name"=>$i['name'],
            "type"=>$i['type'],
            "billable"=>$i['billable'],
            "active"=>$i['active'],
            "description"=>$i['description'],
            "price"=>$i['price'],
            "referenceid"=>$i['referenceid'] );

        //array_push($formattedItem, $i);

    }

	echo json_encode($formattedItem);

}else if ($action == "getInventory") {

 //echo "<script type='text/javascript'>alert('In store.php')</script>";

    $sql = 'SELECT COUNT(*) FROM information_schema.tables  WHERE table_schema = "efront"  AND table_name = "module_vlabs_shoppingcart_order"';
    $tcount = eF_executeQuery($sql);
    //echo "checking if orders table exists: " . PHP_EOL;
    //var_dump($tcount);
    foreach($tcount as $t){
        if($t['COUNT(*)'] < 1 ) {
            echo json_encode(array());
            return;
        }
    }

	$inventory = db_getInventory();

	$formattedInventory= array();

	//echo '<script type="text/javascript">alert("Writing to log")</script>';	
	//error_log("store.php getInventory output:\n");
	//error_log(array_values($inventory));
				
	foreach ($inventory as $item) {
		

		if ($item['billable'] == 1)
			$price = $item['price'];
		else
			$price = 'Not Billable';

		//echo '<script type="text/javascript">alert("about to go into SoapClient call")</script>';

        // jh NOTE: this needs to get executed, since soap calls are really slow commenting temporarily
		try {
			$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        	$creditType = $client->getCreditTypeById($item->referenceid);
        	//print_r($creditType);
        	
	    } catch (Exception $e) {	
			$creditType = null;
	
	    } catch (SoapFault $soapfault) {	    	
			$creditType = null;
	
	    }
        //jh end commenting soap calls */

$creditType = "TestCreditType";
		if($creditType!=null)
		{
			$i = array($item['id'], 
			$item['name'] ,
			$item['description'],
			$item['type'],
			$price,
			$creditType['name'],
			$item['active'],
			$item['creationdate']
			);	
			//error_log("In for each loop ". array_values($i));
			array_push($formattedInventory, $i);
		}	
			
	}
	
	//print_r($inventory);
	//print_r($formattedInventory);
	echo json_encode($formattedInventory);
	
} else if ($action == "getReferences") {

	try {

		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
       //echo '<script type="text/javascript">alert("SoapClient call succeeded")</script>';
		$references = $client->getAssignableCreditTypes();
		$creditTypes = $references->creditType;
		$policies = $references->policy;

		if (!is_array($policies))
		{
			$policies_arr = array($policies);
				
		}else{
			$policies_arr = $policies;

		}
		
		
		if (!is_array($creditTypes))
		{
			addPolicyToCreditType($policies_arr, $creditTypes);
			$result = array('references' => array($creditTypes));
		
		
		}else{
			foreach ($creditTypes as $creditType)
			{
				addPolicyToCreditType($policies_arr, $creditType);
			}
			$result = array('references' => $creditTypes);

		}
        //echo '<script type="text/javascript">alert("SoapClient call succeeded")</script>';
		 //var_dump($result);

		echo json_encode($result);
		
	} catch (Exception $e) {

		echo $e->getMessage();
	} catch (SoapFault $soapfault) {

		echo $soapfault->getMessage();
	}
} else if ($action == "addItem") {

	if (isset($_POST['itemdesc'])) {
		$itemdesc = $_POST['itemdesc'];
	} else {
		$itemdesc = "";
	}


	if (isset($_POST['itemqty'])) {
		$itemqty = $_POST['itemqty'];
	} else {
		$itemqty = 0;
	}

	if (isset($_POST['itemname'])) {
		$itemname = $_POST['itemname'];
	} else {
		$itemname = "";
	}

	if (isset($_POST['itemprice'])) {
		$itemprice = $_POST['itemprice'];
	} else {
		$itemprice = 0;
	}

	if (isset($_POST['active'])) {
		$active = $_POST['active'];
	} else {
		$active = "";
	}

	if (isset($_POST['unlimited'])) {
		$unlimited = $_POST['unlimited'];
	} else {
		$unlimited = "";
	}

	if (isset($_POST['referenceid'])) {
		$referenceid = $_POST['referenceid'];
	} else {
		$referenceid = "";
	}

	if (isset($_POST['billable'])) {
		$billable = $_POST['billable'];
	} else {
		$billable = "";
	}

	if (isset($_POST['type'])) {
		$type = $_POST['type'];
	} else {
		$type = "";
	}

	if (db_addItem($itemname,$itemdesc, $itemid, $itemprice, $billable,$referenceid, $type, $active)) {

		$item = db_getItemByName($itemname);

        //echo '<script type="text/javascript">alert("added item successfully")</script>';
        //echo "dumping item_array: ";
        //var_dump($item_array);

		$result = array('success' => true, 'item' => $item);
	} else {
		$result = array('success' => false, 'message' => "Item could not be saved");
	}
	echo json_encode($result);
} else if ($action == "modifyItem") {

	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
	} else {
		$itemid = "";
	}


	if (isset($_POST['itemdesc'])) {
		$itemdesc = $_POST['itemdesc'];
	} else {
		$itemdesc = "";
	}


	if (isset($_POST['itemqty'])) {
		$itemqty = $_POST['itemqty'];
	} else {
		$itemqty = 0;
	}

	if (isset($_POST['itemname'])) {
		$itemname = $_POST['itemname'];
	} else {
		$itemname = "";
	}

	if (isset($_POST['itemprice'])) {
		$itemprice = $_POST['itemprice'];
	} else {
		$itemprice = 0;
	}

	if (isset($_POST['active'])) {
		$active = $_POST['active'];
	} else {
		$active = false;
	}

	if (isset($_POST['unlimited'])) {
		$unlimited = $_POST['unlimited'];
	} else {
		$unlimited = false;
	}

	if (isset($_POST['referenceid'])) {
		$referenceid = $_POST['referenceid'];
	} else {
		$referenceid = "";
	}

	if (isset($_POST['billable'])) {
		$billable = $_POST['billable'];
	} else {
		$billable = "";
	}

	if (isset($_POST['type'])) {
		$type = $_POST['type'];
	} else {
		$type = "";
	}

	$sql = 'UPDATE module_vlabs_shoppingcart_store_inventory SET ';
	$sql .='name= "' . $itemname . '",';
	$sql .='description= "' . $itemdesc . '",';
	$sql .='price=  ' . $itemprice . ',';
	$sql .='active= ' . $active . ',';
	$sql .='referenceid= "' . $referenceid . '",';
	$sql .='billable=  ' . $billable . ',';
	$sql .='type= "' . $type . '",';
	$sql .='lastmodification= "' . date(DATE_ATOM) . '" ';
	$sql .='WHERE id= ' . $itemid . '';


	if(isItemBeingUsed($itemid)){
		
		$result = array('success' => false, 'message' => 'Item cannot be deleted since it is being referenced in orders or packages');
		
	}else{
		if (db_execute($sql)) {
		    $item = db_getItem($itemid);

            $i_array = array();
            foreach($item as $i) {
                $i_array = array("id"=>$i['id'],
                    "name"=>$i['name'],
                    "description"=>$i['description'],
                    "type"=>$i['type'],
                    "billable"=>$i['billable'],
                    "active"=>$i['active'],
                    "creationdate"=>$i['creationdate']);
            }



            $result = array('success' => true, 'item' => $i_array);
		} else {
			$result = array('success' => false, 'message' => "Item could not be edited.");
		}		
		
	}


	echo json_encode($result);
} else if ($action == "deleteItem") {

	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
	} else {
		$itemid = "";
	}



	if(isItemBeingUsed($itemid))
	{
		$result = array('success' => false, 'message' => 'Item cannot be deleted since it is being referenced in orders or packages');

	}else
	{

		$sql = 'DELETE FROM module_vlabs_shoppingcart_store_inventory ';
		$sql .='WHERE id= ' . $itemid . '';

	
		if(isItemBeingUsed($itemid)){
			$result = array('success' => false, 'message' => "Item is being used and cannot be modified.");
			
		}else{
			if (db_execute($sql)) {
	
				$result = array('success' => true);
			} else {
				$result = array('success' => false, 'message' => 'Item could not be deleted');
			}
		}
	}


	echo json_encode($result);
}

function getAvailCourses($userid) {

	$courses = array();
	$courses = db_getCoursesByUser($userid);
	return $courses;
}



function addPolicyToCreditType($policies, $creditType){
	
	$policyId = $creditType->policyId;
	
	if($policyId!=null)
	{
		foreach ($policies as $policy)
		{
			if($policyId == $policy->id)
				$creditType->policy = $policy;
		}
	}else{
		$creditType->policy = null;
	}
	
}


function isItemBeingUsed($itemid) {
		
	$sql_orders = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE itemid ='.$itemid;
	$sql_packages = 'SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE itemid ='.$itemid;

	$dborders = db_getrecords($sql_orders);
	$dbpackages = db_getrecords($sql_packages);

    $dborders_counter = 0;
    $dbpackages_counter = 0;

    foreach($dborders as $dbO){

        $dborders_counter++;
    }

    foreach($dbpackages as $dbP){
        $dbpackages_counter++;
    }

	if ($dborders_counter > 0 || $dbpackages_counter >0)
		return true;
	else
		return false;
}


function sto_getItemDescription($itemid){
	


	session_start(); 
	$userId = $_SESSION["userid"]; 
	$item = refactored_db_getItem($itemid);
    $userdb = db_getUserTimeZone($userId);
	$timeZoneId = $userdb['timezone'];
	$description = "";
	
	if($item['type']=="PACKAGE"){

		$items = array();		
		$packageItems = db_getPackageItems($item['id']);
		foreach($packageItems as $packageItem){
			$item = refactored_db_getItem($packageItem['itemid']);
			$item['quantity'] = $packageItem['quantity'];
			array_push($items, $item);	
		}
		
		$description .= "<ul>";
		foreach ($items as $item){				
			$creditType = ws_getCreditTypeById($item['referenceid']);
			$course = db_getCourseById($creditType->courseId);
						
			$description .= "<li>";
			$description .= "<strong>".$item['name']."(".$item['quantity']."):</strong> ";
			$description .= "This item allows students enrolled in the course ".$course->shortname." to use the resource ".$creditType->resource." for ";	
			$description .= sto_getPolicyDescription($creditType->policyId, $timeZoneId);			
			$description .= "</li>";
		}
		$description .= "</ul>";
		
						
	}else{	
		$creditType = ws_getCreditTypeById($item['referenceid']);
		$course = db_getCourseById($creditType->courseId);
		
		$description .=  "This item allows students enrolled in the course ".$course->shortname."  to use the resource ".$creditType->resource." for ";	
		$description .= sto_getPolicyDescription($creditType->policyId, $timeZoneId);
	}

	return $description;

}


function sto_getPolicyDescription($policyId, $timeZoneId){


	$policy = ws_getPolicyById($policyId, $timeZoneId);					
	$policyType = $policy->policyType;
	$absolute = $policy->absolute;
	
	$compatibleTimezone = substr($timeZoneId,10);
	date_default_timezone_set($compatibleTimezone);
		
	if($policyType =="NOEXPIRATION"){
		$description .= $policy->quotaInPeriod." minutes. ";
		$description .= "This item will not expire. ";
	
	}else if($policyType =="FIXED"){
		$description .= $policy->quotaInPeriod." minutes. ";				
		$daysFromStart = $policy->numberOfPeriods * $policy->daysInPeriod;
		
		if($absolute){
			$expDate = strtotime ( '+ '.$daysFromStart.' day' , strtotime ( $policy->startDate ) ) ;
			$description .= "This item can be used from ".date('F j, Y, g:i a' ,strtotime ( $policy->startDate ) )." until ". date ( 'F j, Y, g:i a' , $expDate ).". ";	
		}else{
			$description .= "This item will expire ".$daysFromStart ." days after purchase. ";
		}
	
	}else if($policyType =="GRADUAL"){
		$description .= $policy->quotaInPeriod." minutes per period (".$policy->numberOfPeriods." periods of ".$policy->daysInPeriod." days each). ";			
		$description.= "A maximum of ".$policy->maximum." minutes can be used each period. ";
	
		$daysFromStart = $policy->numberOfPeriods * $policy->daysInPeriod;
			
		if($absolute){
			$expDate = strtotime ( '+ '.$daysFromStart.' day' , strtotime ( $policy->startDate ) ) ;
			$description .= "This item can be used from ".date('F j, Y, g:i a' ,strtotime ( $policy->startDate ) )." until ". date ( 'F j, Y, g:i a' , $expDate ).". ";	
		}else{
			$description .= "This item will expire ".$daysFromStart ." days after purchase. ";
		}		
	
	}else if($policyType =="MINMAX"){
		$description .= $policy->quotaInPeriod." minutes per period (".$policy->numberOfPeriods." periods of ".$policy->daysInPeriod." days each). ";							
		$description .= "A maximum of ".$policy->maximum." minutes can be used each period. ";
		$description .= "A minimum of ".$policy->minimum." minutes must be used each period. Otherwise, they remaining minutes will expire. ";
				
		$daysFromStart = $policy->numberOfPeriods * $policy->daysInPeriod;				
		
		if($absolute){
			$expDate = strtotime ( '+ '.$daysFromStart.' day' , strtotime ( $policy->startDate ) ) ;
			$description .= "This item can be used from ".date('F j, Y, g:i a' ,strtotime ( $policy->startDate ) )." until ". date ( 'F j, Y, g:i a' , $expDate ).". ";	
		}else{
			$description .= "This item will expire ".$daysFromStart ." days after purchase. ";
		}
		
	
	}
	
	return $description;
}




?>
