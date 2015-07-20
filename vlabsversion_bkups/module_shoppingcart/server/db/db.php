<?php
//require_once((dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/libraries/configuration.php');

class TableRows extends RecursiveIteratorIterator {
    function __construct($it) {
        parent::__construct($it, self::LEAVES_ONLY);
    }

    function current() {
        return "<td style='width:150px;border:1px solid black;'>" . parent::current(). "</td>";
    }

    function beginChildren() {
        echo "<tr>";
    }

    function endChildren() {
        echo "</tr>" . "\n";
    }
}



function db_execute($sql)
{
    return eF_executeQuery($sql);
    //log($sql);

}


function db_getrecords($sql)
{
    //log($sql);
    $result = eF_executeQuery($sql);
    return $result;
}


function db_getrecord($sql)
{

    $result = eF_executeQuery($sql);
    return $result;
}

//Orders

function db_getOrders()
{	

$sql = "SELECT * FROM module_vlabs_shoppingcart_order ORDER BY lastmodification";
//$result  = eF_getTableData('module_vlabs_shoppingcart_order','*','','lastmodification');
$result = eF_executeQuery($sql);
//print_r("db_getOrders data:");
//print_r($result);
return $result;

}

function array_orders($result){

    $o_array = array();
    if($result!=null){
        foreach($result as $r) {
            $row = array("id" => $r['id'],
                "userid" => $r['userid'],
                "purchasedate" => $r['purchasedate'],
                "lastmodification" => $r['lastmodification'],
                "fulfillmentorderstate" => $r['fulfillmentorderstate'],
                "financialorderstate" => $r['financialorderstate'],
                "ordernumber" => $r['ordernumber'],
                "total" => $r['total'],
                "cancelled" => $r['cancelled'],
                "payment" => $r['payment'],
                "refund" => $r['refund']
                );
            array_push($o_array,$row);
        }
        return $o_array;
    }else{
        return $result;
    }
}


function db_getOrdersByEmail($email)
{	
	$sql = "SELECT * FROM module_vlabs_shoppingcart_order WHERE email = '".$email."' ORDER BY id";

    return eF_executeQuery($sql);
}

function db_getOrdersByUser($userid)
{	
	//original 	$sql = "SELECT * FROM module_vlabs_shoppingcart_order WHERE userid = ".$userid." ORDER BY lastmodification";
	$sql = "SELECT * FROM module_vlabs_shoppingcart_order WHERE userid = ".$userid;
    return eF_executeQuery($sql);
}

function db_getOrdersWithPackage($packageid)
{
	
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE itemid =' . $packageid;
	$result  = eF_executeQuery($sql);
    return $result;
}


function db_addNoPaymentOrder($userid, $ordernumber)
{
	$nowMillis = 1000 * strtotime(date(DATE_ATOM));

    $sql = "INSERT INTO module_vlabs_shoppingcart_order (userid, purchasedate, lastmodification,"
            . "ordernumber, financialorderstate, fulfillmentorderstate, cancelled, payment)"
            . "VALUES (" . $userid . ",'" . $nowMillis . "','" . $nowMillis . "','"
            . $ordernumber . "', 'NO PAYMENT', 'PENDING APPROVAL',0,0);";
            
    //print_r($sql);

    return eF_executeQuery($sql);
            
}

function db_addGoogleCheckoutOrder($email, $ordernumber, $purchaseDate,$fulfillmentorderstate, $financialorderstate){
	
	$user = db_getUserByEmail($email);
	
	$purchaseDateMillis = 1000 * strtotime($purchaseDate);
	
	$sql = "INSERT INTO module_vlabs_shoppingcart_order (userid, purchasedate, lastmodification,"
		." ordernumber, financialorderstate, fulfillmentorderstate, cancelled, payment) VALUES ("
		."". $user->id. ","
		."'". $purchaseDateMillis. "',"
		."'". $purchaseDateMillis . "',"
        ."'". $ordernumber . "',"
        ."'". $fulfillmentorderstate."',"
        ."'". $financialorderstate."',"
        ."0,"
        ."1)";
        

    return eF_executeQuery($sql, false);     
            
}



function db_modifyGoogleCheckoutOrder($new_financial_state,$new_fulfillment_order,$date,$ordernumber){

	$dateMillis = 1000 * strtotime($date);
	$sql = 'UPDATE module_vlabs_shoppingcart_order SET financialorderstate = "' . $new_financial_state . '", fulfillmentorderstate = "' . $new_fulfillment_order . '", lastmodification = "' . $dateMillis . '" WHERE ordernumber = "' . $ordernumber . '";';
    return eF_executeQuery($sql, false);
	
}


function db_addOrderItem($orderid,$itemid,$quantity,$price)
{
	$sql = "INSERT INTO module_vlabs_shoppingcart_order_summary (orderid, itemid, quantity, unitprice, cancelled) VALUES (" . $orderid. "," . $itemid . "," . $quantity . ",".$price.", 0);";	
	return eF_executeQuery($sql);
}
function db_cancelOrderItem($orderid,$itemid)
{
	$sql = "UPDATE module_vlabs_shoppingcart_order_summary SET cancelled = 1 where orderid =" . $orderid. " and itemid=" . $itemid ;
	return eF_executeQuery($sql);
}

function db_cancelOrder($orderid)
{	
	$orderItems = db_getOrderItems($orderid);
	foreach ($orderItems as $orderItem){
		db_cancelOrderItem($orderid, $orderItem['itemid']);
	}
	$sql = "UPDATE module_vlabs_shoppingcart_order SET cancelled = 1, fulfillmentorderstate= 'CANCELLED'  where id =" . $orderid ;
    return eF_executeQuery($sql);
}

function db_cancelGoogleCheckoutOrder($orderid)
{	
	$sql = "UPDATE module_vlabs_shoppingcart_order SET cancelled = 1, fulfillmentorderstate= 'WILL_NOT_DELIVER', financialorderstate = 'CANCELLATION IN PROCESS'  where id =" . $orderid ;
    return eF_executeQuery($sql, false);       
}

function db_setOrderRefund($orderid, $refund)
{	
	$sql = "UPDATE module_vlabs_shoppingcart_order SET refund = ".$refund."  where id =" . $orderid ;
    return eF_executeQuery($sql, false);       
}

function db_getActiveOrderItems($orderid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE cancelled = 0 and orderid = ' . $orderid . ';';
	return eF_executeQuery($sql);
}

function db_getCancelledOrderItems($orderid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE cancelled = 1 and orderid = ' . $orderid . ';';
	return orderSummary_arrays(eF_executeQuery($sql));
}

function db_getOrderItem($orderid, $itemid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE cancelled = 0 and orderid = ' . $orderid . ' and itemid = ' . $itemid . ';';
	return eF_executeQuery($sql);
}

function db_getOrderItemById($id)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE id = ' . $id . ';';
	return array_pop(orderSummary_arrays(eF_executeQuery($sql)));
}

function orderSummary_arrays($result){

    $oSummary_array = array();
    if($result!=null){
        foreach($result as $r){
            $s_array = array("id" => $r['id'],
            "orderid" => $r['orderid'],
            "itemid" => $r['itemid'],
            "quantity" => $r['quantity'],
            "unitprice" => $r['unitprice'],
            "cancelled" => $r['cancelled']);
            array_push($oSummary_array,$s_array);
        }
        return $oSummary_array;
    } else {
        return $result;
    }
}

function db_getOrderByOrderNumber($ordernumber)
{
	$sql = "SELECT * FROM module_vlabs_shoppingcart_order WHERE ordernumber = '" . $ordernumber . "'";
	
	//print_r($sql);

    return eF_executeQuery($sql);
	
}

function db_modifyOrderTotal($orderid, $total)
{
	$sql = "UPDATE module_vlabs_shoppingcart_order SET total=" . $total . " WHERE id =" . $orderid;
	
	//print_r($sql);
	
    return eF_executeQuery($sql, false);
	
	
}

function db_getOrderItems($orderid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE orderid = ' . $orderid . ';';
	return eF_executeQuery($sql);
}

function refactored_db_getOrderItems($orderid)
{
    $sql = 'SELECT * FROM module_vlabs_shoppingcart_order_summary WHERE orderid = ' . $orderid . ';';
    return orderSummary_arrays(eF_executeQuery($sql));
}


function db_approveOrder($orderid)
{

	$sql = "UPDATE module_vlabs_shoppingcart_order SET ";
	$sql .= "lastmodification='" . date(DATE_ATOM) . "' , ";
	$sql .= "financialorderstate ='NO PAYMENT' , ";
	$sql .= "fulfillmentorderstate= 'APPROVED' ";
	$sql .= "WHERE id =" . $orderid;

	return eF_executeQuery($sql); //jh not sure if we need to use 'false' argument here??
}

function db_declineOrder($orderid)
{
	$sql = "UPDATE module_vlabs_shoppingcart_order SET ";
	$sql .= "lastmodification='" . date(DATE_ATOM) . "' , ";
	$sql .= "financialorderstate ='NO PAYMENT' , ";
	$sql .= "fulfillmentorderstate= 'DECLINED' ";
	$sql .= "WHERE id =" . $orderid;

	return eF_executeQuery($sql);
	
	
}

function db_deleteOrder($orderid){
	
	$sql="DELETE FROM  module_vlabs_shoppingcart_order_summary WHERE orderid =".$orderid;
	eF_executeQuery($sql);
	$sql="DELETE FROM  module_vlabs_shoppingcart_order WHERE id =".$orderid;
	eF_executeQuery($sql);
	
}


//Packages
function db_getPackages()
{

    $sql = "SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE type ='PACKAGE'";
    $result = eF_executeQuery($sql);	
    return $result;
	
}



function db_getPackageSummary($packageid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE packageid = ' . $packageid . ';';
	$result = eF_executeQuery($sql);
	return $result;
	
}

function refactored_db_getPackageSummary($packageid)
{
    $sql = 'SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE packageid = ' . $packageid . ';';
    $result = eF_executeQuery($sql);

    $packagesummary_array =array();

    foreach($result as $r){
        $p_array = array("id"=>$r['id'],
           "packageid"=>$r['packageid'],
            "itemid"=>$r['itemid'],
            "quantity"=>$r['quantity'],
            "price"=>$r['price']);
        array_push($packagesummary_array,$p_array);
    }


    return $packagesummary_array;

}

function db_getElegibleItemsForPackage($packageid)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_store_inventory ';
	$sql .= 'WHERE type IN ("PACKAGE ITEM","ITEM") and active=1 and ';
	$sql .= 'billable =(SELECT billable FROM module_vlabs_shoppingcart_store_inventory WHERE id = ' . $packageid . ') and ';
	$sql .= 'not exists (SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE itemid = module_vlabs_shoppingcart_store_inventory.id and packageid = ' . $packageid . ');';
	$result = eF_executeQuery($sql);
   $elegibleItems_array = array();
   foreach($result as $r){

       $item_array = array("id"=>$r['id'],
           "name"=>$r['name'],
           "description"=>$r['description'],
           "quantity"=>$r['quantity'],
           "active"=>$r['active'],
           "creationdate"=>$r['creationdate'],
           "lastmodification"=>$r['lastmodification'],
           "unlimited"=>$r['unlimited'],
           "referenceid"=>$r['referenceid'],
           "type"=>$r['type'],
           "billable"=>$r['billable']);

       array_push($elegibleItems_array,$item_array);

   }
    //echo "elegibleItems_array is: ";
    //var_dump($elegibleItems_array);


	return $elegibleItems_array;
	
}

function db_addPackage($packagename,$packagedesc ,$active , $billable)
{
	$sql = 'INSERT INTO module_vlabs_shoppingcart_store_inventory'
	. '(name, description, quantity, price, unlimited, active, referenceid, billable, type, creationdate, lastmodification)'
	. 'VALUES ("' . $packagename . '","' . $packagedesc . '",null,0.0,false, '.$active.',null,'
	. $billable . ',"PACKAGE","' . date(DATE_ATOM) . '","' . date(DATE_ATOM) . '");';
	
	return eF_executeQuery($sql);
}


function db_modifyPackage($packageid,$packagename,$packagedesc ,$active , $billable){
	$sql = 'UPDATE module_vlabs_shoppingcart_store_inventory SET ';
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

	return eF_executeQuery($sql);
}

function db_deletePackage($packageid)
{
	
	$sql = 'DELETE FROM module_vlabs_shoppingcart_store_inventory WHERE id= ' . $packageid . '';
	return eF_executeQuery($sql);
	
}

function db_getPackageStatus($packageId)
{
	$sql = "SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE id =".$packageId;
    $result = eF_executeQuery($sql);
    $result_active ="";
    foreach($result as $r){
        $result_active = $r["active"];
    }
    return $result_active; //jh original  return $result[$packageId]->active;
	
}


function db_changePackageStatus($packageId)
{
	$status = db_getPackageStatus($packageId);

	if($status==0)
		$newstatus=1;
	else
		$newstatus=0;
		
		
	$sql = "UPDATE module_vlabs_shoppingcart_store_inventory SET active = ".$newstatus ." WHERE id= " . $packageId;
	return eF_executeQuery($sql);
}


function db_setPackageTotal($packageid, $total)
{
	$sql = "UPDATE module_vlabs_shoppingcart_store_inventory SET price = ".$total ." WHERE id= " . $packageid;
	return eF_executeQuery($sql);
}

function db_addItemToPackage($itemid,$itemqty ,$packageid , $price)
{
	$sql = 'INSERT INTO module_vlabs_shoppingcart_package_summary (itemid, quantity, packageid, price) ';
	$sql .='VALUES (' . $itemid . ',' . $itemqty . ',' . $packageid . ',' . $price . ')';
	return eF_executeQuery($sql);
}


function db_updatePackageTotal($packageid)
{
	$items = db_getPackageItems($packageid);
	$total = 0;
	foreach ($items as $item)
	{
		$total +=($item['price'] * $item['quantity']);
	}
	
	
	return db_setPackageTotal($packageid, $total);
	
}

function db_getPackageItems($packageid)
{
    //jh refactored!  : )

	$sql = "SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE packageid = ".$packageid;
	return  packageItem_array(eF_executeQuery($sql));
	
}


function db_getPackageItem($id)
{
    //jh refactored !  : )
	$sql = "SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE id = ".$id;

    $items = eF_executeQuery($sql);

	return  array_pop(packageItem_array($items));


}

function packageItem_array($result){

    $psummary_array = array();
    foreach($result as $i) {
        $p_array = array("id"=>$i['id'],
            "packageid"=>$i['packageid'],
            "itemid"=>$i['itemid'],
            "quantity"=>$i['quantity'],
            "price"=>$i['price']);

        array_push($psummary_array , $p_array);
    }

    return $psummary_array;
}

function db_getPackageItemByPackageAndItem($packageId, $itemId)
{

    //jh refactored !  : )
	$sql = "SELECT * FROM module_vlabs_shoppingcart_package_summary WHERE packageid = ".$packageId." and itemid = ".$itemId;
	//print_r($sql);
    $result = eF_executeQuery($sql);

	return  array_pop(packageItem_array($result));
	
}

function db_deletePackageItem($id, $packageid)
{
	
	$sql  = 'DELETE FROM module_vlabs_shoppingcart_package_summary ';
	$sql .= 'WHERE id = ' . $id;

	if(eF_executeQuery($sql))
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

	//echo '<script type="text/javascript">alert("In db_getInventory")</script>';
	$sql = "SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE type IN ('ITEM','PACKAGE ITEM') ORDER BY lastmodification";
	 
	//$result  = eF_getTableData('module_vlabs_shoppingcart_store_inventory','*','','');
	$result = eF_executeQuery($sql);

	//print_r("db_getInventory data:");
	//print_r($result);
    //return eF_executeQuery($sql);
	return $result;
}

 
function db_getItemsByReference($reference)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE referenceid = "' . $reference . '" and active=1 and type IN ("ITEM");';
	$result =  eF_executeQuery($sql);
    //jh Refactored db call : )
    $item_array = array();
    foreach($result as $r) {

        $i_array = array("id" => $r['id'],
            "name" => $r['name'],
            "description" => $r['description'],
            "quantity" => $r['quantity'],
            "active" => $r['active'],
            "creationdate" => $r['creationdate'],
            "lastmodification" => $r['lastmodification'],
            "unlimited" => $r['unlimited'],
            "referenceid" => $r['referenceid'],
            "type" => $r['type'],
            "billable" => $r['billable']);
        array_push($item_array,$i_array);
    }


    return $item_array;
	
}


function db_getPackageItemsByReference($reference)
{
	$sql = 'SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE referenceid = "' . $reference . '" and active=1 and type IN ("PACKAGE ITEM");';
	$result = eF_executeQuery($sql);

    $item_array = array();
    foreach($result as $r) {

        $i_array = array("id" => $r['id'],
            "name" => $r['name'],
            "description" => $r['description'],
            "quantity" => $r['quantity'],
            "active" => $r['active'],
            "creationdate" => $r['creationdate'],
            "lastmodification" => $r['lastmodification'],
            "unlimited" => $r['unlimited'],
            "referenceid" => $r['referenceid'],
            "type" => $r['type'],
            "billable" => $r['billable']);
        array_push($item_array,$i_array);
    }


    return $item_array;
	
}

	

function db_getItem($id)
{
	


	$sql = "SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE id =" . $id . ";";
	$items  = eF_executeQuery($sql);
	//return $items[$id];
/* jh 7/8/2015 NOTE:  this was done during the begining of this quest
   and believe it or not, I was in the right track.  This was the right
  aproach to handle the dbCalls instead of doing a foreach in every function
  that calls it.  But as time was of the escense this can be done in a PHASE 2.

	$itemid = "";	
	$itemname ="";
	$itemtype = "";
	$itemdescription = "";

	foreach ($items as $i){
	$itemid = $i['id'];
	$itemname = $i['name'];
	$itemtype = $i['type'];
	$itemdescription = $i['description'];
	}
*/
	//echo '<script type="text/javascript">alert("In db_getItem, itemid= '. $itemid  . ' itemname= '.$itemname . '")</script>';
	
//	return array($itemid,$itemname,$itemtype,$itemdescription);

	return $items;
}

function refactored_db_getItem($id)
{

    if($id!=null) {

        $sql = "SELECT * FROM module_vlabs_shoppingcart_store_inventory WHERE id =" . $id . ";";
        $result = eF_executeQuery($sql);

        return dbitem_array($result);
    } else {
        return null;
    }

}

function dbitem_array($result){

    if($result!=null) {
        $item_array = array();

        foreach ($result as $r) {

            $item_array = array("id" => $r['id'],
                "name" => $r['name'],
                "description" => $r['description'],
                "quantity" => $r['quantity'],
                "active" => $r['active'],
                "creationdate" => $r['creationdate'],
                "lastmodification" => $r['lastmodification'],
                "unlimited" => $r['unlimited'],
                "referenceid" => $r['referenceid'],
                "type" => $r['type'],
                "billable" => $r['billable']);
        }

        return $item_array;
    }   else {
        return $result;
    }

}

function db_getItemByName($name)
{
	
	$sql = "SELECT * from module_vlabs_shoppingcart_store_inventory WHERE name = '" . str_replace("'", "''",$name) . "';";
    $item = eF_executeQuery($sql);

    $items_array = array();
    foreach($item as $i) {
        $i_array = array("id"=>$i['id'],
            "name"=>$i['name'],
            "description"=>$i['description'],
            "type"=>$i['type'],
            "billable"=>$i['billable'],
            "active"=>$i['active'],
            "creationdate"=>$i['creationdate']);

            array_push($items_array , $i_array);
    }

    return array_pop($items_array);

}


function db_addItem($itemname,$itemdesc, $itemid, $itemprice, $billable,$referenceid, $type, $active)
{

	$sql = 'INSERT INTO module_vlabs_shoppingcart_store_inventory(';
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
	
	return eF_executeQuery($sql);
	
}


function db_getUserByEmail($email)
{
	$sql = "SELECT * FROM users WHERE LOWER(email) = '" . strtolower($email) . "'";
    return eF_executeQuery($sql);	
	
}


function db_getUserById($userid)
{
	$sql = "SELECT * FROM users WHERE login = '".$userid."'";
    return eF_executeQuery($sql);	
}


function refactored_db_getUserById($userid)
{
    $sql = "SELECT * FROM users WHERE id =".$userid;
    return user_array(eF_executeQuery($sql));
}

function db_getOrderById($orderid)
{

    $sql = "SELECT * FROM module_vlabs_shoppingcart_order WHERE id =" . $orderid;
    return eF_executeQuery($sql);
    
	
}


//Pre assignments


function db_addPreasssignment($id, $courseid, $itemid, $quantity, $active){
	$sql = "INSERT INTO module_vlabs_shoppingcart_preassignment (id, courseid, itemid, quantity, assignmentdate, lastmodification, active)".
			"VALUES ('".$id."',".$courseid.",".$itemid.",".$quantity.",'".date(DATE_ATOM)."','".date(DATE_ATOM) . "',".$active.")";
	//print_r($sql);
	return eF_executeQuery($sql);
	
}

function db_modifyPreasssignment($id, $courseid, $itemid, $quantity, $active){
	$sql = "UPDATE module_vlabs_shoppingcart_preassignment SET courseid=".$courseid.", itemid= ".$itemid.", quantity=".$quantity.", lastmodification='".date(DATE_ATOM) . "', active=".$active." WHERE id='".$id."'";
	return eF_executeQuery($sql);
	
}

function db_cancelPreasssignment($id){
	$sql = "UPDATE module_vlabs_shoppingcart_preassignment SET active=0 WHERE id='".$id."'";
	return eF_executeQuery($sql);
}

function db_deletePreassignment($id){
	$sql ="DELETE FROM module_vlabs_shoppingcart_preassignment WHERE id='".$id."'";
	return eF_executeQuery($sql);
}

function db_getPreassignmentsByCourse($courseid){
	$sql = "SELECT * FROM module_vlabs_shoppingcart_preassignment WHERE courseid =".$courseid;
	$result = eF_executeQuery($sql);	
	return $result;
}

function db_getPreassignments(){
	$sql = "SELECT * FROM module_vlabs_shoppingcart_preassignment";

	return preassignment_array($result = eF_executeQuery($sql));
}

function preassignment_array_byId($result){

    $result_array = array();
    if($result!=null) {
        foreach ($result as $r) {

            $result_array = array("id" => $r['id'],
                "courseid" => $r['courseid'],
                "itemid" => $r['itemid'],
                "quantity" => $r['quantity'],
                "assignmentdate" => $r['assignmentdate'],
                "lastmodification" => $r['lastmodification'],
                "active" => $r['active']);
        }

        return $result_array;
    }
    else {
        return $result;
    }


}

function preassignment_array($result){

    $result_array = array();
    if($result!=null) {
        foreach ($result as $r) {

            $array_item = array("id" => $r['id'],
                "courseid" => $r['courseid'],
                "itemid" => $r['itemid'],
                "quantity" => $r['quantity'],
                "assignmentdate" => $r['assignmentdate'],
                "lastmodification" => $r['lastmodification'],
                "active" => $r['active']);
            array_push($result_array,$array_item);
        }

        return $result_array;
    }
    else {
        return $result;
    }

}



function db_getPreassignmentById($id){
    //jh refactored!  see preassignment_array()  : )
	$sql = "SELECT * FROM module_vlabs_shoppingcart_preassignment WHERE id='".$id."'";
	return preassignment_array_byId(eF_executeQuery($sql));
}

function db_getPreassignment($courseid, $itemid){
	$sql = "SELECT * FROM module_vlabs_shoppingcart_preassignment WHERE courseid =".$courseid." and itemid=".$itemid." and active = 1";	
	//print_r($sql);
	return preassignment_array(eF_executeQuery($sql));
}


function db_getCoursesByUser($userid){
	/*jh for now just return all courses
	$sql = "SELECT * FROM courses WHERE id in ".
				"(SELECT instanceid FROM module_vlabs_context WHERE contextlevel = 50 AND id in ".
					"(SELECT contextid FROM module_vlabs_role_assignments WHERE userid =".$userid." and roleid = 5))";
	*/
    $sql = "SELECT * FROM courses";

	return courses_array(eF_executeQuery($sql));
	
}

function db_getCourses(){
	
	$sql = "SELECT * FROM courses";
	return eF_executeQuery($sql);
	
}

function courses_array($result){

    $c_array = array();
    if($result!=null){
        foreach($result as $r){
            $r_array = array("id"=>$r['id'],
            "name"=>$r['name'],
            "active"=>$r['active'],
            "description"=>$r['description']);
        array_push($c_array,$r_array);
        }
    return $c_array;
    }else{
        return $result;
    }
}

function db_getCourseById($courseid){
	//echo '<script type="text/javascript">alert("in db.php db_getCourseById courseid ='. $courseid .'")</script>';
	$sql = "SELECT * FROM courses WHERE id =".$courseid;  //originally module_vlabs_course
	$result  = courses_array(eF_executeQuery($sql));
	return $result;
	
}

function db_getEnrollments(){
	
	//$enrollments = array();
/* jh NOTE:  check with Dr. Sadjadi how he wants to integrate this with efront
	$sql = "SELECT * FROM moodle.module_vlabs_role_assignments WHERE roleid = 5 and 
		contextid IN (SELECT id FROM moodle.module_vlabs_context WHERE contextlevel = 50) ORDER BY id";


	$result  = eF_executeQuery($sql);

	foreach ($result as $e){
		$sql = "SELECT * FROM module_vlabs_context WHERE id = $e->contextid ORDER BY id";
		$context = eF_executeQuery($sql);
		
		$enrollment = array(
			"enrollmentId"=>$e->id,
			"courseId"=>$context->instanceid,
			"userId"=>$e->userid
		);
		array_push($enrollments, $enrollment);


	}
	
	return $enrollments;
	*/
    return null;
}

function db_getAdministrators(){
	
	$sql = "SELECT id FROM users WHERE user_type='administrator'";
   return eF_executeQuery($sql);
	
}

function db_getAdministrators_new(){

    $sql = "SELECT * FROM users WHERE user_type='administrator'";
    return eF_executeQuery($sql);

}

function db_getUserTimeZone($userId){
		//jh modified to use efront timezone info
   	//jh original: $sql = "SELECT data FROM module_vlabs_user_info_data WHERE userid = ".$userId." and fieldid = 4";
	$sql = "SELECT timezone FROM users WHERE login = '".$userId."'";
	return user_array(eF_executeQuery($sql));
	
}

function user_array($result){

    $u_array = array();

    if($result!=null){
        foreach($result as $r) {
            $u_array = array("id" => $r['id'],
            "login" => $r['login'],
            "timezone" => $r['timezone'],
            "name" => $r['name'],
            "surname" => $r['surname'],
            "email" => $r['email']);
        array_push($u_array, $u_array);
        }
        return $u_array;
    }else{
        return $result;
    }


}

function db_setUserTimeZone($userId, $timeZoneId){
   	try {
   		
   		$sql = "UPDATE module_vlabs_user_info_data SET data ='".$timeZoneId."' WHERE userid = ".$userId." and fieldid = 4";
	    eF_executeQuery($sql,false);
	    
    } catch (Exception $e) {
        echo $e->getMessage();
    }	
}

function db_getUserName($userId){
   	try {
   		
   		$sql = "SELECT username FROM module_vlabs_user WHERE id = ".$userId;
	    $username = eF_executeQuery($sql);
        return $username;
    } catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }	
}

function db_getModules(){

    $sql = "SELECT * FROM module_vlabs_shoppingcart_dbadmin";
    $modules_array = array();
    $modules = eF_executeQuery($sql);
    if($modules!=null){
        foreach($modules as $m){
            $m_array = array($m['id'],
                $m['module'],
                $m['description']);
        array_push($modules_array,$m_array);
        }
        return $modules_array;
    }else{

    }
    return $modules;
}




?>
