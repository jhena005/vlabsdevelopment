<?php




require_once('db/db.php');

require_once('packages.php');
require_once ('transactions.php');

//header("Content-type: text/x-json");


if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	$action = "";
}

if ($action == "reload") {

	


	$preassignments = db_getPreassignments();


	
	$formattedPreasssignments = array();
	
	foreach ($preassignments as $p){
		$item = db_getItem($p['itemid']);
		//echo '<script type="text/javascript">alert("foreach loop, preassignment.php source itemid: '. $itemid . '")</script>';
		$course = db_getCourseById($p['courseid']);
		$courseid = "";
		$courseshortn = "";

		$itemid = "";
		$itemname = "";

		foreach ($item as $i){
			$itemid = $i['id'];
			$itemname = $i['name'];
		}

		foreach ($course as $c)
		{
			$courseid = $c['id'];
			$courseshortn = $c['shortname'];
			//echo '<script type="text/javascript">alert("For each loop, preassignment.php courseid: '. $courseid . ' course shortname: '. $courseshortn.'")</script>';
		}

		$preassignment = array($p['id'],
				$courseid,
				$courseshortn,
				$itemid,
				$itemname,
				$p['quantity'],
				$p['active']);
		array_push($formattedPreasssignments, $preassignment);
	}
	
	echo json_encode($formattedPreasssignments);
	
	
}else if ($action == "getCourses") {

	$courses = db_getCourses();

	$formattedCourses = array();

	
	foreach ($courses as $c){
		$course = array(
				"id"=>$c->id,
				"name"=>$c->shortname);
		array_push($formattedCourses, $course);
	}

	echo json_encode($formattedCourses);
	
	
}else if ($action == "getItems") {
	
	if (isset($_POST['courseid'])) {
		$courseId = $_POST['courseid'];
	} else {
		$courseId = "";
	}

	$courses_arr = array();
	array_push($courses_arr,$courseId);

	try {
		
		$params = array('courseId' => $courses_arr);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
		$response = $client->getCreditTypesByCourse($params);

		if (!is_array($response->creditType))
			$references = array($response->creditType);
		else
			$references = $response->creditType;

		
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
			foreach ($items as $item) {
				$item = array("id"=>$item->id,"name"=>$item->name, "type"=>$item->type);
				 array_push($formattedStoreItems, $item);
			}

			$packages = getPackagesWithItems($itemsForPackages);

			foreach ($packages as $package) {
				$item = array("id"=>$package->id,"name"=>$package->name,"type"=>$package->type);				
				array_push($formattedStoreItems, $item);
			}
		}
		
		
/*		//Filter Items that has been assigned to that course already
		$filteredItems = array();
		foreach ($formattedStoreItems as $item) {
			$preassignment = db_getPreassignment($courseId, $item["id"]);
			//print_r($preassignment);
			if($preassignment == null){
				array_push($filteredItems, $item);				
			}
		}*/

		echo json_encode($formattedStoreItems);
		
		
	} catch (Exception $e) {
		echo $e->getMessage();
		
	} catch (SoapFault $soapfault) {
		echo $soapfault->getMessage();
	}
		
	
} else if ($action == "addPreassignment") {

	
	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
		
	} else {
		$itemid = "";
	}
	
	if (isset($_POST['courseid'])) {
		$courseid = $_POST['courseid'];
	} else {
		$courseid = "";
	}
		
	if (isset($_POST['quantity'])) {
		$quantity = $_POST['quantity'];
	} else {
		$quantity = 0;
	}

	
	$id = uniqid("CA",false);

	//Generate purchase id combining order number and item id
	$item = db_getItem($itemid);
	$purchaseId = $id."".$item->id;
	$course = db_getCourseById($courseid);
	$preassignment = array(
						"id"=>$id,
						"courseId"=>$course->id,
						"courseName"=>$course->shortname,
						"itemId"=>$item->id,
						"itemName"=>$item->name,
						"quantity"=>$quantity,
						"active"=>1);
	

	$assignments = array();
		
	if($item->type=="PACKAGE"){
		$packageItems = db_getPackageItems($item->id);
		foreach($packageItems as $pi){
			$item = db_getItem($pi->itemid);
			$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity*$pi->quantity, "active"=>false);
			array_push($assignments, $assignment);
		}
	}else{

		$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity, "active"=>false);
		array_push($assignments, $assignment);
	}
		
	$assignmentsResult = ws_assignQuotaToCourse($assignments);
	
	//Check that all assignments were added successfully
	$success = true;
	
	$assignmentsResultArr = array();
	
	if(!is_array($assignmentsResult)){
		array_push($assignmentsResultArr, $assignmentsResult);
	}else{
		array_merge($assignmentsResultArr,$assignmentsResult);
	}
	
	foreach ($assignmentsResultArr as $ar){
		
		if(!$ar->active){
			$preassignment["active"] = 0;
			$success= false;
			break;
		}
	}
	$message="";
	if($success){
		$message="Pressignment could not be modified";
		db_addPreasssignment($id, $courseid, $itemid, $quantity, TRUE);
	}

	echo json_encode(array("success"=>$success, "preassignment"=>$preassignment, "message"=>$message));

} else if ($action == "modifyPreassignment") {

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		
	} else {
		$id = "";
	}
	
	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
		
	} else {
		$itemid = "";
	}
	
	if (isset($_POST['courseid'])) {
		$courseid = $_POST['courseid'];
	} else {
		$courseid = "";
	}
	
	
	if (isset($_POST['quantity'])) {
		$quantity = $_POST['quantity'];
	} else {
		$quantity = 0;
	}

	
	//Generate purchase id combining order number and item id
	$item = db_getItem($itemid);
	$course = db_getCourseById($courseid);
	$preassignmentResponse = array(
			"id"=>$id,
			"courseId"=>$course->id,
			"courseName"=>$course->shortname,
			"itemId"=>$item->id,
			"itemName"=>$item->name,
			"quantity"=>$quantity,
			"active"=>1);
	
	$purchaseId = $id."".$item->id;

	$assignments = array();
		
	$preassignment = db_getPreassignmentById($purchaseId);
	
	if($item->type=="PACKAGE"){
		$packageItems = db_getPackageItems($item->id);
		foreach($packageItems as $pi){
			$item = db_getItem($pi->itemid);
			$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity*$pi->quantity, "active"=>true);
			array_push($assignments, $assignment);
		}
	}else{

		$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity, "active"=>true);
		array_push($assignments, $assignment);
	}
	
	
	$assignmentsResult = ws_modifyCourseQuota($assignments);
	
	//Check that all assignments were added successfully
	$success = true;
	$assignmentsResultArr = array();
	
	if(!is_array($assignmentsResult)){
		array_push($assignmentsResultArr, $assignmentsResult);
	}else{
		array_merge($assignmentsResultArr,$assignmentsResult);
	}
	
	
	//print_r($assignmentsResultArr);
	
	foreach ($assignmentsResultArr as $ar){
		if(!$ar->active){
			$preassignmentResponse["active"] =0;
			$success= false;
			break;
		}
	}
	
	$message = "";
	if($success){
		$message="Pressignment could not be modified";
		db_modifyPreasssignment($id, $courseid, $itemid, $quantity, TRUE);
	}
	
	echo json_encode(array("success"=>$success, "preassignment"=>$preassignmentResponse, "message"=>$message));
	
	
}else if ($action == "deletePreassignment") {

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		
	} else {
		$id = "";
	}
	
	$preassignment = db_getPreassignmentById($id);
	$item = db_getItem($preassignment->itemid);
    $purchaseId = $id."".$item->id;
	$assignments = array();
		
    $quantity = $preassignment->quantity;

	
	if($item->type=="PACKAGE"){
		$packageItems = db_getPackageItems($item->id);
		foreach($packageItems as $pi){
			$item = db_getItem($pi->itemid);
			$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity*$pi->quantity, "active"=>true);
			array_push($assignments, $assignment);
		}
	}else{

		$assignment = array("purchaseId"=>$purchaseId, "creditTypeId"=>$item->referenceid,
							"quantity"=>$quantity, "active"=>true);
		array_push($assignments, $assignment);
	}
	
	$assignmentsResult = ws_cancelCourseQuota($assignments);
	
	//Check that all assignments were added successfully
	$success = true;
	$message = "";
	foreach ($assignmentsResult as $ar){
		if($ar->active){
			$message = "Course assignment could not be cancelled";
			$success = false;
			break;
		}
	}
	
	if($success){
		db_deletePreassignment($id);
	}
	
	echo json_encode(array("success"=>$success, "message"=>$message));

	
}else if ($action == "getPreassignment") {

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		
	} else {
		$id = "";
	}
	
	$preassignment = db_getPreassignmentById($id);

	echo json_encode(array("preassignment"=>$preassignment));
}

?>
