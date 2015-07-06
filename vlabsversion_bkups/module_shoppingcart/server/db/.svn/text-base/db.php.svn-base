<?php
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');


function db_execute($sql)
{
    return execute_sql($sql, false);
    //log($sql);

}


function db_getrecords($sql)
{
    //log($sql);
    $result = get_records_sql($sql);
    return $result;
}


function db_getrecord($sql)
{
    //log($sql);
    $result = get_record_sql($sql);
    return $result;
}

//Orders

function db_getOrders()
{	
	$sql = "SELECT * FROM mdl_shoppingcart_order ORDER BY lastmodification";
	$result  = get_records_sql($sql);
    return $result;
}

function db_getOrdersByEmail($email)
{	
	$sql = "SELECT * FROM mdl_shoppingcart_order WHERE email = '".$email."' ORDER BY id";

    return get_records_sql($sql);
}

function db_getOrdersByUser($userid)
{	
	$sql = "SELECT * FROM mdl_shoppingcart_order WHERE userid = ".$userid." ORDER BY lastmodification";
    return get_records_sql($sql);
}

function db_getOrdersWithPackage($packageid)
{
	
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE itemid =' . $packageid;
	$result  = get_records_sql($sql);
    return $result;
}


function db_addNoPaymentOrder($userid, $ordernumber)
{
	$nowMillis = 1000 * strtotime(date(DATE_ATOM));

    $sql = "INSERT INTO mdl_shoppingcart_order (userid, purchasedate, lastmodification,"
            . "ordernumber, financialorderstate, fulfillmentorderstate, cancelled, payment)"
            . "VALUES (" . $userid . ",'" . $nowMillis . "','" . $nowMillis . "','"
            . $ordernumber . "', 'NO PAYMENT', 'PENDING APPROVAL',0,0);";
            
    //print_r($sql);

    return execute_sql($sql, false);     
            
}

function db_addGoogleCheckoutOrder($email, $ordernumber, $purchaseDate,$fulfillmentorderstate, $financialorderstate){
	
	$user = db_getUserByEmail($email);
	
	$purchaseDateMillis = 1000 * strtotime($purchaseDate);
	
	$sql = "INSERT INTO mdl_shoppingcart_order (userid, purchasedate, lastmodification,"
		." ordernumber, financialorderstate, fulfillmentorderstate, cancelled, payment) VALUES ("
		."". $user->id. ","
		."'". $purchaseDateMillis. "',"
		."'". $purchaseDateMillis . "',"
        ."'". $ordernumber . "',"
        ."'". $fulfillmentorderstate."',"
        ."'". $financialorderstate."',"
        ."0,"
        ."1)";
        

    return execute_sql($sql, false);     
            
}



function db_modifyGoogleCheckoutOrder($new_financial_state,$new_fulfillment_order,$date,$ordernumber){

	$dateMillis = 1000 * strtotime($date);
	$sql = 'UPDATE mdl_shoppingcart_order SET financialorderstate = "' . $new_financial_state . '", fulfillmentorderstate = "' . $new_fulfillment_order . '", lastmodification = "' . $dateMillis . '" WHERE ordernumber = "' . $ordernumber . '";';
    return execute_sql($sql, false);
	
}


function db_addOrderItem($orderid,$itemid,$quantity,$price)
{
	$sql = "INSERT INTO mdl_shoppingcart_order_summary (orderid, itemid, quantity, unitprice, cancelled) VALUES (" . $orderid. "," . $itemid . "," . $quantity . ",".$price.", 0);";	
	return execute_sql($sql, false);       
}
function db_cancelOrderItem($orderid,$itemid)
{
	$sql = "UPDATE mdl_shoppingcart_order_summary SET cancelled = 1 where orderid =" . $orderid. " and itemid=" . $itemid ;
	return execute_sql($sql, false);       
}

function db_cancelOrder($orderid)
{	
	$orderItems = db_getOrderItems($orderid);
	foreach ($orderItems as $orderItem){
		db_cancelOrderItem($orderid, $orderItem->itemid);
	}
	$sql = "UPDATE mdl_shoppingcart_order SET cancelled = 1, fulfillmentorderstate= 'CANCELLED'  where id =" . $orderid ;
    return execute_sql($sql, false);       
}

function db_cancelGoogleCheckoutOrder($orderid)
{	
	$sql = "UPDATE mdl_shoppingcart_order SET cancelled = 1, fulfillmentorderstate= 'WILL_NOT_DELIVER', financialorderstate = 'CANCELLATION IN PROCESS'  where id =" . $orderid ;
    return execute_sql($sql, false);       
}

function db_setOrderRefund($orderid, $refund)
{	
	$sql = "UPDATE mdl_shoppingcart_order SET refund = ".$refund."  where id =" . $orderid ;
    return execute_sql($sql, false);       
}

function db_getActiveOrderItems($orderid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE cancelled = 0 and orderid = ' . $orderid . ';';
	return get_records_sql($sql);
}

function db_getCancelledOrderItems($orderid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE cancelled = 1 and orderid = ' . $orderid . ';';
	return get_records_sql($sql);
}

function db_getOrderItem($orderid, $itemid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE cancelled = 0 and orderid = ' . $orderid . ' and itemid = ' . $itemid . ';';
	return get_record_sql($sql);
}

function db_getOrderItemById($id)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE id = ' . $id . ';';
	return array_pop(get_records_sql($sql));
}

function db_getOrderByOrderNumber($ordernumber)
{
	$sql = "SELECT * FROM mdl_shoppingcart_order WHERE ordernumber = '" . $ordernumber . "'";
	
	//print_r($sql);

    return get_record_sql($sql);
	
}

function db_modifyOrderTotal($orderid, $total)
{
	$sql = "UPDATE mdl_shoppingcart_order SET total=" . $total . " WHERE id =" . $orderid;
	
	//print_r($sql);
	
    return execute_sql($sql, false);
	
	
}

function db_getOrderItems($orderid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_order_summary WHERE orderid = ' . $orderid . ';';
	return get_records_sql($sql);
}



function db_approveOrder($orderid)
{

	$sql = "UPDATE mdl_shoppingcart_order SET ";
	$sql .= "lastmodification='" . date(DATE_ATOM) . "' , ";
	$sql .= "financialorderstate ='NO PAYMENT' , ";
	$sql .= "fulfillmentorderstate= 'APPROVED' ";
	$sql .= "WHERE id =" . $orderid;

	return execute_sql($sql, false);
}

function db_declineOrder($orderid)
{
	$sql = "UPDATE mdl_shoppingcart_order SET ";
	$sql .= "lastmodification='" . date(DATE_ATOM) . "' , ";
	$sql .= "financialorderstate ='NO PAYMENT' , ";
	$sql .= "fulfillmentorderstate= 'DECLINED' ";
	$sql .= "WHERE id =" . $orderid;

	return execute_sql($sql, false);
	
	
}

function db_deleteOrder($orderid){
	
	$sql="DELETE FROM  mdl_shoppingcart_order_summary WHERE orderid =".$orderid;
	execute_sql($sql,false);
	$sql="DELETE FROM  mdl_shoppingcart_order WHERE id =".$orderid;
	execute_sql($sql,false);
	
}


//Packages
function db_getPackages()
{

    $sql = "SELECT * FROM mdl_shoppingcart_store_inventory WHERE type ='PACKAGE'";
    $result = get_records_sql($sql);	
    return $result;
	
}



function db_getPackageSummary($packageid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_package_summary WHERE packageid = ' . $packageid . ';';
	$result = get_records_sql($sql);
	return $result;
	
}

function db_getElegibleItemsForPackage($packageid)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_store_inventory ';
	$sql .= 'WHERE type IN ("PACKAGE ITEM","ITEM") and active=1 and ';
	$sql .= 'billable =(SELECT billable FROM mdl_shoppingcart_store_inventory WHERE id = ' . $packageid . ') and ';
	$sql .= 'not exists (SELECT * FROM mdl_shoppingcart_package_summary WHERE itemid = mdl_shoppingcart_store_inventory.id and packageid = ' . $packageid . ');';
	$result = get_records_sql($sql);
	return $result;
	
}

function db_addPackage($packagename,$packagedesc ,$active , $billable)
{
	$sql = 'INSERT INTO mdl_shoppingcart_store_inventory'
	. '(name, description, quantity, price, unlimited, active, referenceid, billable, type, creationdate, lastmodification)'
	. 'VALUES ("' . $packagename . '","' . $packagedesc . '",null,0.0,false, '.$active.',null,'
	. $billable . ',"PACKAGE","' . date(DATE_ATOM) . '","' . date(DATE_ATOM) . '");';
	
	return execute_sql($sql, false);
}


function db_modifyPackage($packageid,$packagename,$packagedesc ,$active , $billable){
	$sql = 'UPDATE mdl_shoppingcart_store_inventory SET ';
	$sql .='name= "' . $packagename . '",';
	$sql .='description= "' . $packagedesc . '",';
	$sql .='quantity= 0,';
	$sql .='price=  null,';
	$sql .='unlimited=  1,';
	$sql .='active= ' . $active . ',';
	$sql .='referenceid= null,';
	$sql .='billable=  ' . $billable . ',';
	$sql .='type= "PACKAGE",';
	$sql .='lastmodification= "' . date(DATE_ATOM) . '" ';
	$sql .='WHERE id= ' . $packageid . '';

	return execute_sql($sql, false);
}

function db_deletePackage($packageid)
{
	
	$sql = 'DELETE FROM mdl_shoppingcart_store_inventory WHERE id= ' . $packageid . '';
	return execute_sql($sql, false);
	
}

function db_getPackageStatus($packageId)
{
	$sql = "SELECT * FROM mdl_shoppingcart_store_inventory WHERE id =".$packageId;
    $result = get_records_sql($sql);
    return $result[$packageId]->active;
	
}


function db_changePackageStatus($packageId)
{
	$status = db_getPackageStatus($packageId);

	if($status==0)
		$newstatus=1;
	else
		$newstatus=0;
		
		
	$sql = "UPDATE mdl_shoppingcart_store_inventory SET active = ".$newstatus ." WHERE id= " . $packageId;
	return execute_sql($sql, false);
}


function db_setPackageTotal($packageid, $total)
{
	$sql = "UPDATE mdl_shoppingcart_store_inventory SET price = ".$total ." WHERE id= " . $packageid;
	return execute_sql($sql, false);
}

function db_addItemToPackage($itemid,$itemqty ,$packageid , $price)
{
	$sql = 'INSERT INTO mdl_shoppingcart_package_summary (itemid, quantity, packageid, price) ';
	$sql .='VALUES (' . $itemid . ',' . $itemqty . ',' . $packageid . ',' . $price . ')';
	return execute_sql($sql, false);
}


function db_updatePackageTotal($packageid)
{
	$items = db_getPackageItems($packageid);
	$total = 0;
	foreach ($items as $item)
	{
		$total +=($item->price * $item->quantity);
	}
	
	
	return db_setPackageTotal($packageid, $total);
	
}

function db_getPackageItems($packageid)
{
	$sql = "SELECT * FROM mdl_shoppingcart_package_summary WHERE packageid = ".$packageid;
	return  get_records_sql($sql);	
	
}


function db_getPackageItem($id)
{
	$sql = "SELECT * FROM mdl_shoppingcart_package_summary WHERE id = ".$id;
	return  array_pop(get_records_sql($sql));	
	
}

function db_getPackageItemByPackageAndItem($packageId, $itemId)
{
	$sql = "SELECT * FROM mdl_shoppingcart_package_summary WHERE packageid = ".$packageId." and itemid = ".$itemId;
	//print_r($sql);
	return  array_pop(get_records_sql($sql));	
	
}

function db_deletePackageItem($id, $packageid)
{
	
	$sql  = 'DELETE FROM mdl_shoppingcart_package_summary ';
	$sql .= 'WHERE id = ' . $id;

	if(execute_sql($sql, false))
	{
		if(db_updatePackageTotal($packageid))
			return true;
	}else {
			return false;
	}

	return false;
	
	
}
//Store
 
 function db_getInventory()
{

	$sql = "SELECT * FROM mdl_shoppingcart_store_inventory WHERE type IN ('ITEM','PACKAGE ITEM') ORDER BY lastmodification";
    return get_records_sql($sql);
	
}

 
function db_getItemsByReference($reference)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_store_inventory WHERE referenceid = "' . $reference . '" and active=1 and type IN ("ITEM");';
	return get_records_sql($sql);
	
}


function db_getPackageItemsByReference($reference)
{
	$sql = 'SELECT * FROM mdl_shoppingcart_store_inventory WHERE referenceid = "' . $reference . '" and active=1 and type IN ("PACKAGE ITEM");';
	return get_records_sql($sql);
	
}

	

function db_getItem($id)
{
	
	$sql = 'SELECT * FROM mdl_shoppingcart_store_inventory WHERE id = ' . $id . ';';
	$items  = get_records_sql($sql);
	return $items[$id];
}


function db_getItemByName($name)
{
	
	$sql = "SELECT * from mdl_shoppingcart_store_inventory WHERE name = '" . str_replace("'", "''",$name) . "';";     
    $response = get_record_sql($sql);
    return array_pop(get_records_sql($sql));

}


function db_addItem($itemname,$itemdesc, $itemid, $itemprice, $billable,$referenceid, $type, $active)
{

	$sql = 'INSERT INTO mdl_shoppingcart_store_inventory(';
	$sql .= 'name, ';
	$sql .= 'description, ';
	$sql .= 'active, ';
	$sql .= 'referenceid, ';
	$sql .= 'type, ';

	
	$sql .= 'billable, ';
	if($billable=="true")
		$sql .= 'price, ';
	
	$sql .= 'creationdate, ';
	$sql .= 'lastmodification ';

	$sql .= ') VALUES (';
	$sql .= '"' . $itemname . '","';
	$sql .=  $itemdesc . '",';
	$sql .=  $active . ',';
	$sql .= '"' . $referenceid . '",';
	$sql .= '"'.$type . '",';

	
	$sql .= $billable . ',';
	if($billable=="true")
		$sql .=  $itemprice . ', ';

	$sql .= '"'.date(DATE_ATOM) . '",';
	$sql .= '"'.date(DATE_ATOM) . '"';

	
	$sql .= ');';
	
	//print_r($sql);
	
	return execute_sql($sql, false);
	
}


function db_getUserByEmail($email)
{
	$sql = "SELECT * FROM mdl_user WHERE LOWER(email) = '" . strtolower($email) . "'";
    return get_record_sql($sql);	
	
}


function db_getUserById($userid)
{
	$sql = "SELECT * FROM mdl_user WHERE id = ".$userid;
    return get_record_sql($sql);	
}


function db_getOrderById($orderid)
{

    $sql = "SELECT * FROM mdl_shoppingcart_order WHERE id =" . $orderid;
    return get_record_sql($sql);
    
	
}


//Pre assignments


function db_addPreasssignment($id, $courseid, $itemid, $quantity, $active){
	$sql = "INSERT INTO mdl_shoppingcart_preassignment (id, courseid, itemid, quantity, assignmentdate, lastmodification, active)".
			"VALUES ('".$id."',".$courseid.",".$itemid.",".$quantity.",'".date(DATE_ATOM)."','".date(DATE_ATOM) . "',".$active.")";
	//print_r($sql);
	return execute_sql($sql, false);
	
}

function db_modifyPreasssignment($id, $courseid, $itemid, $quantity, $active){
	$sql = "UPDATE mdl_shoppingcart_preassignment SET courseid=".$courseid.", itemid= ".$itemid.", quantity=".$quantity.", lastmodification='".date(DATE_ATOM) . "', active=".$active." WHERE id='".$id."'";
	return execute_sql($sql, false);
	
}

function db_cancelPreasssignment($id){
	$sql = "UPDATE mdl_shoppingcart_preassignment SET active=0 WHERE id='".$id."'";
	return execute_sql($sql, false);	
}

function db_deletePreassignment($id){
	$sql ="DELETE FROM mdl_shoppingcart_preassignment WHERE id='".$id."'";
	return execute_sql($sql, false);
}

function db_getPreassignmentsByCourse($courseid){
	$sql = "SELECT * FROM mdl_shoppingcart_preassignment WHERE courseid =".$courseid;
	return get_records_sql($sql);
}

function db_getPreassignments(){
	$sql = "SELECT * FROM mdl_shoppingcart_preassignment";
	return get_records_sql($sql);
}

function db_getPreassignmentById($id){
	$sql = "SELECT * FROM mdl_shoppingcart_preassignment WHERE id='".$id."'";
	return get_record_sql($sql);
}

function db_getPreassignment($courseid, $itemid){
	$sql = "SELECT * FROM mdl_shoppingcart_preassignment WHERE courseid =".$courseid." and itemid=".$itemid." and active = 1";	
	//print_r($sql);
	return get_record_sql($sql);	
}


function db_getCoursesByUser($userid){
	
	$sql = "SELECT * FROM mdl_course WHERE id in ".
				"(SELECT instanceid FROM mdl_context WHERE contextlevel = 50 AND id in ".
					"(SELECT contextid FROM mdl_role_assignments WHERE userid =".$userid." and roleid = 5))";
	
	return get_records_sql($sql);
	
}

function db_getCourses(){
	
	$sql = "SELECT * FROM mdl_course";
	return get_records_sql($sql);
	
}


function db_getCourseById($courseid){
	
	$sql = "SELECT * FROM mdl_course WHERE id =".$courseid;
	return get_record_sql($sql);
	
}

function db_getEnrollments(){
	
	$enrollments = array();
		
	$sql = "SELECT * FROM moodle.mdl_role_assignments WHERE roleid = 5 and 
		contextid IN (SELECT id FROM moodle.mdl_context WHERE contextlevel = 50) ORDER BY id";
	

	$result  = get_records_sql($sql);

	foreach ($result as $e){
		$sql = "SELECT * FROM mdl_context WHERE id = $e->contextid ORDER BY id";
		$context = get_record_sql($sql);
		
		$enrollment = array(
			"enrollmentId"=>$e->id,
			"courseId"=>$context->instanceid,
			"userId"=>$e->userid
		);
		array_push($enrollments, $enrollment);
		
	}
	
	return $enrollments;
}

function db_getAdministrators(){
	
	$sql = "SELECT userid FROM mdl_role_assignments WHERE roleid IN (SELECT id FROM mdl_role WHERE name = 'Administrator')";
    return get_records_sql($sql);
	
}


function db_getUserTimeZone($userId){
		
   	$sql = "SELECT data FROM mdl_user_info_data WHERE userid = ".$userId." and fieldid = 4";
	return get_record_sql($sql);
	
}

function db_setUserTimeZone($userId, $timeZoneId){
   	try {
   		
   		$sql = "UPDATE mdl_user_info_data SET data ='".$timeZoneId."' WHERE userid = ".$userId." and fieldid = 4";
	    execute_sql($sql,false);
	    
    } catch (Exception $e) {
        echo $e->getMessage();
    }	
}

function db_getUserName($userId){
   	try {
   		
   		$sql = "SELECT username FROM mdl_user WHERE id = ".$userId;
	    $username = get_record_sql($sql);
        return $username;
    } catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }	
}



?>