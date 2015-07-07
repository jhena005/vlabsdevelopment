<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

/*
 * orders.php
 *
 * This class contains methods that reload the interface which displays orders,
 * and methods to approve or decline orders.
 * Methods that are concerned to saving new orders are located
 * on checkouthandler.php
 *
 */


require_once((dirname(__FILE__)).'/db/db.php');



//require_once('checkout/google_checkout/lib/googlerequest.php');
//require_once('checkout/google_checkout/lib/googleitem.php');

require_once('packages.php');
require_once('transactions.php');
require_once('mailing.php');



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
print_r($config_values);

$merchant_id = $config_values['CONFIG_MERCHANT_ID'];  
$merchant_key = $config_values['CONFIG_MERCHANT_KEY'];  
$server_type = $config_values['CONFIG_SERVER_TYPE'];  
$currency = $config_values['CONFIG_CURRENCY'];

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
if ($action == "reloadOrders") {

	//echo '<script type="text/javascript">alert("In reloadOrders")</script>';
	
	if (isset($_POST['userid'])) {
		$userid = $_POST['userid'];
	} else {
		$userid = "";
	}
	//echo '<script type="text/javascript">alert("user id is: '.$userid .'")</script>';
	//call to the db
	$userdata = db_getUserById($userid);
	//echo '<script type="text/javascript">alert("after db_getUserById")</script>';
	$timeZoneId = "";
	$user = "";
	foreach ($userdata as $u){
		$user = $u['id'];
		$timeZoneId = $u['timezone'];
	}
	$orders = db_getOrdersByUser($user);
	//echo '<script type="text/javascript">alert("after db_getOrdersByUser")</script>';


	//echo '<script type="text/javascript">alert("before userdate foreach loop")</script>';


	//	$timeZoneId = db_getUserTimeZone($userid)->data; //jh this info is stored in the users table in efront no need to invoke this function.
	// $compatibleTimezone = substr($timeZoneId,10);  //jh timezone in efront already has the right format.
	date_default_timezone_set($timeZoneId);
   //echo '<script type="text/javascript">alert("after date_default_timezone_set,  timeZoneId is: '.$timeZoneId.'")</script>';
	$formattedOrders= array();

			foreach ($orders as $order) {
			//echo '<script type="text/javascript">alert("In foreach loop")</script>';
			$purchaseDate = date(DATE_ATOM, ($order['purchasedate']/1000));
					
			$o = array($order['id'],
			$order['ordernumber'],
			$userid,
			$purchaseDate,
			$order['lastmodification'] ,
			$order['fulfillmentorderstate'],
			$order['financialorderstate'] ,
			$order['total']
			);

			array_push($formattedOrders, $o);
		}

		
    echo json_encode($formattedOrders);

	//Reload all orders of all users. Only used for administartor view
} else if ($action == "reloadOrdersAll") {	//jh original:  $action == "reloadOrdersAll"
		
	//$adminId = "admin"; //jh 
	//$adminId = $_SESSION["userid"];
	//echo "adminId is: " . $adminId;
	//$timeZoneId = db_getUserTimeZone($adminId)->data;  jh
	//$compatibleTimezone = substr($timeZoneId,10); jh 
	//date_default_timezone_set($compatibleTimezone);  jh

	//call to the db
	//$currentUser = $this -> getCurrentUser();
	//$currentUser -> getRole($this -> getCurrentLesson());
	//echo "current user is: ".$currentUser;

	$orders = db_getOrders();

	//echo "TESTING mysql_result";
	//var_dump($orders);  //jh this displays the array contents
	//echo $orders=>['fields']=>['id'];
	//$row = mysqli_fetch_array($orders, MYSQLI_NUM);
	//$finfo = $orders->fetch_fields();
	//echo $finfo;
	//echo "END TESTING mysql_result";
	//echo $orders

	$formattedOrders= array();

//	if (is_array($orders)) {


		foreach($orders as $row) {
//			$user = db_getUserById($order->userid); jh
			$purchaseDate = date(DATE_ATOM, ($row['purchasedate']/1000)); 
			
			$o = array($row['id'],
			$row['ordernumber'],
			"admin",
			$purchaseDate,
			$row['lastmodification'] ,
			$row['fulfillmentorderstate'],
			$row['financialorderstate'] ,
			$row['total']
			);

			array_push($formattedOrders, $o);
	
		}
//	}
		
    echo json_encode($formattedOrders);
//	 echo("{'id':'1,'ordernumber':'11','username':'johann','purchasedDate':'1','lastmodification':'1','fulfillmentorderstate':'test','financialorderstate':'test','total':'1'}");

	//Show details of every order which contain what items were purchased,
	//quantities, and price
} else if ($action == "reloadOrderItems") {



	if (isset($_POST['orderid'])) {
		$orderid = $_POST['orderid'];
	} else {
		$orderid = "";
	}
	//echo '<script type="text/javascript">alert("In orders.php reloadOrderItems, orderid= '.$orderid.'")</script>';
	
	$ordertotal= 0;
	$orderItems = db_getOrderItems($orderid);
	$order = db_getOrderById($orderid);
	
	

	$formattedOrderItems= array();
//	if (is_array($orderItems)) {
	 
		foreach ($orderItems as $orderitem) {
			$subtotal = $orderitem['quantity'] * $orderitem['unitprice'];						
			$description = ord_getItemDescription($orderitem['itemid'], $orderid); //jh this needs a lot of work, it's making a soap call
			//$description = "needs work";
			$item = db_getItem($orderitem['itemid']);
	
			$itemid = "";
			$itemname = "";
			$itemtype = "";
			$itemdescription = "";

			foreach ($item as $i){
				$itemid = $i['id'];
				$itemname = $i['name'];
				$itemtype = $i['type'];
				$itemdescription = $i['description'];
			}

		
			$oi = array(
				$orderitem['id'],
			    $itemid,
				$itemname,
				$itemtype,
				$itemdescription,
				$orderitem['quantity'],
				$orderitem['unitprice'] ,
				$subtotal,
				$orderitem['cancelled'],
				$description	
			);
			
			$ordertotal += $subtotal;
			array_push($formattedOrderItems, $oi);
		}
//	}
    
	$result = array('orderItems' => $formattedOrderItems, "orderTotal" => $ordertotal);
	echo json_encode($result);

	//Approves the order with id orderid sent via POST
} else if ($action == "approveOrder") {
	
	if (isset($_POST['orderid'])) {
		$orderid = $_POST['orderid'];
	} else {
		$orderid = "";
	}
	
	if (isset($_POST['userid'])) {
		$userlogin = $_POST['userid'];
	} else {
		$userlogin = "";
	}
	

	$adminId = $userlogin; //jh original $_SESSION["userid"];
	$userdata = db_getUserById($userlogin);
	//echo '<script type="text/javascript">alert("after db_getUserById")</script>';
	$timeZoneId = "";

	foreach ($userdata as $u){
		$timeZoneId = $u['timezone'];
	}

	date_default_timezone_set($timeZoneId);
	
	//Get order details
	$dbOrder = db_getOrderById($orderid);
	$userid = "";
	$dbOrder_ordernumber = "";
	foreach($dbOrder as $o){
		$dbOrder_ordernumber = $o['ordernumber'];
		$userid = $o['userid'];
   }

	$user= db_getUserById($userid);
	$user_name = "";
	foreach($user as $u){
		$user_name = $u['name'];
	}
	
	//This array will help to create a detailed email of the items that could not be approved
	$failedItems = array();
	//call web service
	$orderItemsSuccess = saveTransaction($orderid, false);
	
	//Iterate through the response
	foreach($orderItemsSuccess as $ois){
		
		//If item could not be saved in web service, it should be deleted form the order summary
		if(!$ois["success"]){
			array_push($failedItems, $ois["id"]);
			db_cancelOrderItem($orderid, $ois["id"]);
		}
		
	}
	//Get active order items
	$items = db_getActiveOrderItems($orderid);

	//get cancelled order items and save the names on a string for a detailed message
	$itemsCancelled = db_getCancelledOrderItems($orderid);	
	$cancelledItemsNames = "";
	foreach ($itemsCancelled as $ic){
		$item = db_getItem($ic->itemid);
		$item_name = "";
		foreach($item as $i){
			$item_name = $i['name'];
		}
		$cancelledItemsNames .= " ".$item_name.",";
	}	
	$cancelledItemsNames = substr($cancelledItemsNames, 0, -1);
	$cancelledItemsNames.=".";
	$message = "The following items has not been approved in the order : ".$cancelledItemsNames	;
	
	$success  = true;
	
	//if there are non active order items decline order, otherwise approve it
	if($items)
	{
		db_approveOrder($orderid);
		//TODO:email content
		$body= '<p>Order '.$dbOrder_ordernumber.' has been approved.</p>';
		if($itemsCancelled){
			$body.='<p>'.$message.'</p>';
		}
		// sms: 5/19/2011
		// sendEmail($user, 'Order Approved', $body);
					
	}else{
		db_declineOrder($orderid);
		$success = false;
		//TODO:email content
		$body= '<p>Order '.$dbOrder_ordernumber.' has been declined. '.$message.'</p>';
		// sms: 5/19/2011
		// sendEmail($user, 'Order Declined', $body);
	}

	//Get order updated
	$order = db_getOrderById($orderid);
	$order_ordernumber = "";
	$order_lastmodification = "";
	$order_fulfillmentorderstate = "";
	$order_financialorderstate = "";
	$order_total = "";
	$order_purchasedate = "";
	foreach($order as $o){
		$order_ordernumber = $o['ordernumber'];
		$order_lastmodification = $o['lastmodification'];
		$order_fulfillmentorderstate = $o['fulfillmentorderstate'];
		$order_financialorderstate = $o['financialorderstate'];
		$order_total = $o['total'];
		$order_purchasedate = $o['purchasedate'];
	}

	$o = array("id"=>$order->id,
			"ordernumber"=>$order_ordernumber,
			"username"=>$user->username,
			"purchasedate"=>date(DATE_ATOM, ($order_purchasedate/1000)),
			"lastmodification"=>$order_lastmodification ,
			"fulfillmentorderstate"=>$order_fulfillmentorderstate,
			"financialorderstate"=>$order_financialorderstate ,
			"total"=>$order_total
			);	
			
	$response = array("success"=>$success, "message"=>$message, "cancelled"=>$itemsCancelled, "order"=>$o);
	echo json_encode($response);
		
	//Declines order with id orderid sent via POST
} else if ($action == "declineOrder") {

	if (isset($_POST['orderid'])) {
		$orderid = $_POST['orderid'];
	} else {
		$orderid = "";
	}
	
	if (isset($_POST['userid'])) {
		$userlogin = $_POST['userid'];
	} else {
		$userlogin = "";
	}


	$adminId = userlogin;//jh original =$_SESSION["userid"];
	$userdata = db_getUserById($userlogin);

	$timeZoneId = "";

	foreach ($userdata as $u){
		$timeZoneId = $u['timezone'];
	}

	date_default_timezone_set($timeZoneId);

	//jh not needed on efront $compatibleTimezone = substr($timeZoneId,10);
	//jh not needed on efront date_default_timezone_set($compatibleTimezone);
	
	//Get order details
	$dbOrder = db_getOrderById($orderid);

	$dbOrder_userid = "";
	$dbOrder_ordernumber = "";
	foreach($dbOrder as $dbO){
		$dbOrder_userid = $dbO['userid'];
		$dbOrder_ordernumber = $dbO['ordernumber'];
	}

	//Update order in database
	$success = db_declineOrder($orderid);

	//Send email
	$user= db_getUserById($dbOrder_userid);
	$body= '<p>Order '.$dbOrder_ordernumber.' has been declined.<p>';
	//sendEmail($user, 'Order Declined', $body);  jh NOTE: come back to this !!!
	
	//Get order details
	$order = db_getOrderById($orderid);
	$order_ordernumber = "";
	$order_purchasedate = "";
	$order_lastmodification = "";
	$order_fulfillmentorderstate ="";
	$order_financialorderstate = "";
	$order_total = "";
	$order_id = "";

	foreach($order as $o){
		$order_ordernumber = $o['ordernumber'];
		$order_purchasedate = $o['purchasedate'];
		$order_lastmodification = $o['lastmodification'];
		$order_fulfillmentorderstate = $o['fulfillmentorderstate'];
		$order_financialorderstate = $o['ninancialorderstate'];
		$order_total = $o['total'];
		$order_id = $o['id'];
	}


	$o = array("id"=>$order_id,
			"ordernumber"=>$order_ordernumber,
			"username"=>$user_username,
			"purchasedate"=>date(DATE_ATOM, ($order_purchasedate/1000)),
			"lastmodification"=>$order_lastmodification ,
			"fulfillmentorderstate"=>$order_fulfillmentorderstate,
			"financialorderstate"=>$order_financialorderstate ,
			"total"=>$order_total
			);	
	
	$response = array("success"=>$success, "order"=>$o);
	echo json_encode($response);
		
}else if ($action == "cancelOrder") {

	if (isset($_POST['orderid'])) {
		$orderid = $_POST['orderid'];
	} else {
		$orderid = "";
	}

	if (isset($_POST['userid'])) {
		$userlogin = $_POST['userid'];
	} else {
		$userlogin = "";
	}
	
	$adminId = $userlogin; //jh original = $_SESSION["userid"];

	$userdata = db_getUserById($userlogin);

	$timeZoneId = "";

	foreach ($userdata as $u){
		$timeZoneId = $u['timezone'];
	}

	date_default_timezone_set($timeZoneId);

	//initialize refund
	$refundAmount = 0;
	
	$dbOrder =  db_getOrderById($orderid);
	$dbOrder_payment = "";
	$dbOrder_id = "";
	$dbOrder_refund= "";
	$dbOrder_ordernumber = "";
	$dbOrder_userid = "";

	foreach($dbOrder as $dbO){
		$dbOrder_payment = $dbO['payment'];
		$dbOrder_id = $dbO['id'];
		$dbOrder_refund= $dbO['refund'];
		$dbOrder_ordernumber = $dbO['ordernumber'];
		$dbOrder_userid = $dbO['userid'];
	}
	
	$dbOrderItems = db_getOrderItems($orderid);

	$assignmentsResponse = cancelTransaction($orderid);

	//print_r($assignmentResponse);
	//print_r($dbOrderItems);
	
	$i=0;	
	foreach ($dbOrderItems as $dbOrderItem){
		$ar = $assignmentsResponse[$i++];
		$subtotal = $dbOrderItem['quantity']*$dbOrderItem['unitprice'];
		$partialRefund = ($subtotal*$ar->percentageReturned)/100;	
		$refundAmount = $refundAmount + $partialRefund;	
	}
	
	//Google checkout orders
	if($dbOrder_payment){

		if($refundAmount>0){
			
			db_setOrderRefund($dbOrder_id, $dbOrder_refund+$refundAmount);
			
			//Refund total or partial item price 
			/*jh NOTE:  Discussed with Dr. Sadjadi on 7/6/2015 that we will not use the google request methods for now.
			$gresponse = $Grequest->SendRefundOrder($dbOrder_ordernumber,$refundAmount,
										"Order has been refunded by the store administrator.".
										"Contact the administrator for further details.");
			
			if($gresponse[0]==200){
				//If the refund amount is not the total of the orderItem, 
				//item cannot be cancelled

				$gresponse  = $Grequest->SendCancelOrder($dbOrder_ordernumber, 
								"Order has been cancelled by the store.".
				 				"Contact the administrator for further details.");
						
				if($gresponse[0]==200){
					db_cancelGoogleCheckoutOrder($dbOrder->id);
					$success = true;
				}else{
					$success = false;
					$message = "Google checkout has not allowed to cancel the order ".$dbOrder->ordernumber.".";
				}


			}else{
				$success = false;
				$message = "Google checkout has not allowed to refund the quantity of ".$refundAmount." to the order ".$dbOrder->ordernumber.".";
			}

			end jh */
			$message = "Refunds not available through this system, please contact the administrator for further details.";				
		}else{
			$success = false;
			$message = "Order could not be cancelled because all items have been consumed by the buyer.";
						
		}
	}else{
		$success = db_cancelOrder($orderid);	
	}

	if($success){
		$body= '<p>Order '.$dbOrder_ordernumber.' has been cancelled.<p>';
	}else{
		$body= '<p>The cancellation of your order has not been completed. The reason is:<cite>'.$message.'</cite><p>';
		
	}
	//Send email
	$user= db_getUserById($dbOrder_userid);
	$user_name = "";
	foreach($user as $u){
		$user_name = $u['name'];
	}
	// sms: 5/19/2011
	// sendEmail($user, 'Order['.$dbOrder->ordernumber.'] Cancellation', $body);

	
	//Get updated order info
	$order = db_getOrderById($orderid);
	$order_id = "";
	$order_ordernumber = "";
	$order_purchasedate = "";
	$order_fulfillmentorderstate = "";
	$order_financialorderstate = "";
	$order_total = "";
	$order_lastmodification = "";
	foreach($order as $o){
		$order_id = $o['id'];
		$order_ordernumber = $o['ordernumber'];
		$order_purchasedate = $o['purchasedate'];
		$order_fulfillmentorderstate = $o['fulfillmentorderstate'];
		$order_financialorderstate = $o['financialorderstate'];
		$order_total = $o['total'];
		$order_lastmodification = $o['lastmodification'];
	}



	$o = array("id"=>$order_id,
			"ordernumber"=>$order_ordernumber,
			"username"=>$user_username,
			"purchasedate"=>date(DATE_ATOM, ($order_purchasedate/1000)),
			"lastmodification"=>$order_lastmodification ,
			"fulfillmentorderstate"=>$order_fulfillmentorderstate,
			"financialorderstate"=>$order_financialorderstate ,
			"total"=>$order_total
			);	
			
	$response = array("success"=>$success, "message"=>$message, "order"=>$o);		
	echo json_encode($response);

	
}else if ($action == "cancelOrderItem") {

	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else {
		$id = "";
	}

    if (isset($_POST['userid'])) {
        $userlogin = $_POST['userid'];
    } else {
        $userlogin = "";
    }

	//initilize refund
	$refundAmount=0;

	$dbOrderItem = db_getOrderItemById($id);
    $dbOrderItem_orderid = "";
    $dbOrderItem_itemid = "";
    $dbOrderItem_quantity = "";
    $dbOrderItem_cancelled = "";

    foreach($dbOrderItem as $dbOI){
        $dbOrderItem_orderid = $dbOI['orderid'];
        $dbOrderItem_itemid = $dbOI['orderid'];
        $dbOrderItem_quantity = $dbOI['quantity'];
        $dbOrderItem_cancelled = $dbO['cancelled'];
    }

	$dbOrder = db_getOrderById($dbOrderItem_orderid);
    $dbOrder_ordernumber ="";
    $dbOrder_id = "";
    $dbOrder_payment = "";
    $dbOrder_userid = "";
    foreach($dbOrder as $dbO){
        $dbOrder_ordernumber = $dbO['ordernumber'];
        $dbOrder_id = $dbO['id'];
        $dbOrder_payment = $dbO['payment'];
        $dbOrder_userid = $dbO['userid'];
    }

	$dbItem = db_getItem($dbOrderItem_itemid);
    $dbItem_id = "";
    $dbItem_referenceid = "";
    $dbItem_name = "";

    foreach($dbItem as $dbI){
        $dbItem_id = $dbI['id'];
        $dbItem_referenceid = $dbI['id'];
        $dbItem_name = $dbI['name'];
    }
	$success = true;
	$message="";
	
	$assignmentsRequest = array();
	
	if($dbItem->type=="PACKAGE"){
		$packageItems = db_getPackageItems($dbItem_id);
		foreach ($packageItems as $pi){
			$item = db_getItem($pi['itemid']);
            $item_referenceid = "";
            foreach($item as $i){
                $item_referenceid = $i['referenceid'];
            }

			$assignment = array("creditTypeId"=>$item_referenceid,
								"quantity"=>$pi['quantity'],
								"purchaseId"=>$dbOrder_ordernumber."".$dbItem_id,
								"active"=>!$pi['cancelled']
								);
			array_push($assignmentsRequest, $assignment);			
		}
		
		$assignmentsResponse = ws_cancelQuotaAssignment($assignmentsRequest); //jh NOTE: come back to this !!! 7/7/2015
		
//		print_r($assignmentsResponse);
	
		$i=0;		
		foreach ($packageItems as $pi){
			$percentageReturned = $assignmentsResponse[$i++]->percentageReturned; //jh NOTE: this is tied to the above call!!
			$subtotal = $pi['quantity']*$dbOrderItem_quantity*$pi['price'];
			$partialRefund = ($subtotal * $percentageReturned)/100;	
			$refundAmount = $refundAmount + $partialRefund;		
			
//			echo "subtotal ".$subtotal;
//			echo "partial refund ".$partialRefund;
//			echo "refund subtotal ".$refundAmount;
		}
		
//		echo "refund total ".$refundAmount;
	}else{
		$assignment = array("creditTypeId"=>$dbItem->referenceid,
							"quantity"=>$dbOrderItem_quantity,
							"purchaseId"=>$dbOrder_ordernumber,
							"active"=>!$dbOrderItem_cancelled
							);	
		array_push($assignmentsRequest, $assignment);
		
		$assignmentResponse = ws_cancelQuotaAssignment($assignmentsRequest);
		
//		print_r($assignmentResponse);
		
		$percentageReturned = $assignmentResponse->percentageReturned;
		$subtotal = $dbOrderItem->quantity*$dbItem->price;	
		$refundAmount = ($subtotal * $percentageReturned)/100;
		
//		echo "subtotal ".$subtotal;
//		echo "refund ".$refundAmount;
	}


	//Google checkout order items
	if($dbOrder_payment){
		
		if($refundAmount>0){
            /*jh NOTE:  Discussed with Dr. Sadjadi on 7/6/2015 that we will not use the google request methods for now.

            $itemToCancel = array();
            //create a google item
            $gitem = new GoogleItem($dbItem->name, // Item name
            $dbItem->description, // Item description
            $dbOrderItem->quantity, // Quantity
            $dbItem->price); // Unit price

            //set item unique id
            $gitem->SetMerchantItemId($dbItem->id);
            array_push($itemsToCancel, $gitem);

            //Refund total or partial item price


            $gresponse = $Grequest->SendRefundOrder($dbOrder->ordernumber,$refundAmount,
                                        "Item has been refunded by the administrator of the quota store.".
                                        "Contact the administrator for further details.");

            if($gresponse[0]==200){
                //If the refund amount is not the total of the orderItem,
                //item cannot be cancelled

                db_setOrderRefund($dbOrder->id, $dbOrder->refund+$refundAmount);

                if($percentageReturned==1){
                    $gresponse = $Grequest->SendCancelItems($dbOrder->ordernumber, $itemsToCancel,
                                        "Item has been cancelled by the administrator of the quota store.".
                                        "Contact the administrator for further details.");

                    if($gresponse[0]==200){
                        db_cancelOrderItem($dbOrder->id, $dbItem->id);
                        $success = true;

                    }else{
                        $success = false;
                        $message = "Google could not cancel item ". $dbItem->name;
                    }
                }else{
                    db_cancelOrderItem($dbOrder->id, $dbItem->id);
                    $success = true;
                }
            }else{
                $success = false;
                $message = "Google could not refund the amount of ".$refundAmount." to the order ".$dbOrder->ordernumber.".";
            }

         $message = "Refunds not available through this system, please contact the administrator for further details.";
        jh end*/
        }else{
            $success = false;
            $message = "Item ". $dbItem->name." could not be cancelled because it has been consumed by the buyer.";


        }


    }else{
        $success = db_cancelOrderItem($dbOrder_id, $dbItem_id);
        if($success){
            $body= '<p>The item '.$dbItem_name.' from order '.$dbOrder_ordernumber.' has been cancelled.<p>';
        }else{
            $message = "Item could not be cancelled in database";
            $body= '<p>The cancellation of item '.$dbItem_name.' from order '.$dbOrder_ordernumber.' has not been completed.<p>';
        }

    }

    $itemsCancelled = db_getCancelledOrderItems($dbOrder_id);
    $items = db_getOrderItems($dbOrder_id);

    if(count($items) == count($itemsCancelled)){
        if($dbOrder_payment){

            /* jh as mentioned in above NOTE, google events will be omited for now
            db_cancelGoogleCheckoutOrder($dbOrder_id);
            $gresponse  = $Grequest->SendCancelOrder($dbOrder->ordernumber,
                                    "Order has been cancelled due to a partial or complete refund.".
                                     "Contact the administrator for further details.");

            */
        }else{
            db_cancelOrder($dbOrder_id);
            $body= '<p>Order '.$dbOrder_ordernumber.' has been cancelled.<p>';
            $user= db_getUserById($dbOrder_userid);
            // sms: 5/19/2011
            // sendEmail($user, 'Order['.$dbOrder->ordernumber.'] Cancellation', $body);

        }

    }

        $orderitem = db_getOrderItemById($id);
        $orderitem_quantity = "";
        $orderitem_unitprice = "";
        $orderitem_itemid = "";
        $orderitem_cancelled = "";

     foreach($oderitem as $oi){
         $orderitem_quantity = $oi['quantity'];
         $orderitem_unitprice = $oi['unitprice'];
         $orderitem_itemid = $oi['itemid'];
         $orderItem_cancelled = $oi['cancelled'];
     }


    $subtotal = $orderitem_quantity * $orderitem_unitprice;
    $item = db_getItem($orderitem_itemid);
    $item_id = "";
    $item_name = "";
    $item_type = "";
    $item_description = "";

    foreach($item as $i){
        $item_id = $i['id'];
        $item_name = $i['name'];
        $item_type = $i['type'];
        $item_description = $i['description'];
    }

    $user= db_getUserById($dbOrder_userid);


    $description = ord_getItemDescription($item_id, $dbOrder_id);

    $oi = array(
        "id"=>$orderitem_id,
        "itemid"=>$item_id,
        "name"=>$item_name,
        "type"=>$item_type,
        "description"=>$item_description,
        "quantity"=>$orderitem_quantity,
        "price"=>$orderitem_unitprice ,
        "subtotal"=>$subtotal,
        "cancelled"=>$orderitem_cancelled,
        "description"=>$description
    );

    if($success){
        $body= '<p>The item '. $dbItem_name.' from order '.$dbOrder_ordernumber.' has been cancelled.<p>';
    }else{
        $body= '<p>The item '. $dbItem_name.' from order '.$dbOrder_ordernumber.' has not been cancelled. The reason is: <cite>'.$message.'</cite><p>';

    }

    // sms: 5/19/2011
    // sendEmail($user, 'Item Cancellation from order['.$dbOrder->ordernumber.']', $body);

    $response = array("success"=>$success, "orderitem"=>$oi, "message"=>$message);
    echo json_encode($response);

}

function ord_getItemDescription($itemid, $orderid){

    //Test with ordernumber:  IA4f310f1212c9b




    $order = db_getOrderById($orderid);  //jh candidate for removal since this info was already obtained in calling section:  reloadOrderItems.  maybe is better to pass these individual values as arguments???
    //printr($order);


    $order_userid = "";
    foreach($order as $o)
    {
        $order_userid = $o['userid'];
    }

    $item = db_getItem($itemid);  //jh again candidate for removal, this was already obtained in calling section. maybe is better to pass these individual values as arguments???
    $itemtype="";
    foreach($item as $i)
    {
        $itemtype=$i['type'];
    }

//	echo '<script type="text/javascript">alert("In orders.php ord_getItemDescription  after db_getItem itemid= '.$itemid .' orderid= ' . $orderid .'")</script>';

    $timeZoneId = 'GMT-05:00 US/Eastern'; //jh original was(need input from the Professor): db_getUserTimeZone($order_userid)->data;
    $description = "";

    if($itemtype=="PACKAGE"){

//	echo '<script type="text/javascript">alert("In orders.php ord_getItemDescription in if package section")</script>';
        $items = array();
        $packageItems = db_getPackageItems($itemid);

        foreach($packageItems as $packageItem){
            //jh here the logic is getting the items
            $item = db_getItem($packageItem['itemid']);
            //$item->quantity = $packageItem->quantity; //replaced by foreach loop below.  I cannot use object->field because getting back  a mixed mysqli array

            foreach($item as $i)
            {
                $i['quantity'] = $packageItem['quantity'];
            }


            array_push($items, $item);
//			echo '<script type="text/javascript">alert("in orders.php $packageItems foreach loop i->quantity='. $packageItem['quantity'].'")</script>'; //jh remove this

        }

    //	echo '<script type="text/javascript">alert("in orders.php after $packageItems foreach loop")</script>';
    //	echo "items array with item array elements";
    //	var_dump($items);
        $description .= "<ul>";

        foreach ($items as $item){
            //jh we also need to do a foreach loop for each $item
            $itemname="";
            $itemquantity="";
            $item_referenceid="";
            //check array size first.
    //		echo '<script type="text/javascript">alert("in orders.php after $packageItems foreach loop first items foreach loop item size is:'.sizeof($item). '")</script>';

            foreach($item as $i)
            {
                //echo "items sub loop, size of item is: " . sizeof($i);
                $itemname=$i['name'];
                $itemquantity=$i['quantity'];
                $item_referenceid=['referenceid'];
            }

    //		echo '<script type="text/javascript">alert("in orders.php after $packageItems after foreach items, item before soap call")</script>';

            $creditType = ws_getCreditTypeById($item_referenceid); //jh here another getCreditTypeById. Replace with Ajax call
            //jh CAREFUL!!!! NEED TO TEST IF creditType returned is null else it will look ugly

            //echo '<script type="text/javascript">alert("in orders.php after $packageItems about to dump creditType")</script>';
            //echo "credit type var_dump ";
            //var_dump($creditType);
            /* jh reference info, can be deleted

            INSERT INTO module_vlabs_quotasystem_credit_type (id, name, resource, course_id, policy_id, active, assignable, update_ts) VALUES (84, 'KES-VL-NoExp', 'VIRTUAL LAB', 47, 30, true, true, '2012-02-07 06:44:48.508811-05');

            DROP TABLE IF EXISTS `module_vlabs_quotasystem_credit_type`;
            CREATE TABLE module_vlabs_quotasystem_credit_type (
                 id integer NOT NULL,
                 name character varying(45) NOT NULL,
                 resource character varying(45) NOT NULL,
                 course_id integer NOT NULL,
                 policy_id integer,
                 active boolean NOT NULL,
                 assignable boolean NOT NULL,
                 update_ts timestamp DEFAULT now() ON UPDATE now()
             end jh reference info, can be deleted */

				
			$course = db_getCourseById($creditType->courseId); //jh needs to be put back once credittype ajax call is available
			

			
			$course_name = "";
			foreach($course as $c)
			{
				$course_name = $c['name'];
			}
							
			$description .= "<li>";
			$description .= "<strong>".$itemname."(".$itemquantity."):</strong> ";
			$description .= "This item allows students enrolled in the course ".$course_name." to use the resource ".$creditType->resource." for ";	
			$description .= ord_getPolicyDescription($creditType->policyId, $timeZoneId);			
			$description .= "</li>";
		}

		$description .= "</ul>";
		
						
	}else{
		
			$item_referenceid="";
			foreach($item as $i)
			{
				$item_referenceid=$i['referenceid'];
			}		

		$creditType = ws_getCreditTypeById($item_referenceid);
		//echo "creditType vardump: ";
		//var_dump($creditType);
		$course = db_getCourseById($creditType->courseId);
		$course_name="";
		foreach($course as $c)
		{
			$course_name=$c['name'];
		}
	//	$description .=  "This item allows students enrolled in the course ".$course->shortname."  to use the resource ".$creditType->resource." for ";	//jh original code needs retrofitting, efront course table does not have a shortname field
		$description .=  "This item allows students enrolled in the course ".$course_name."  to use the resource ".$creditType->resource." for ";
		//echo "description data: " . $description;
		$description .= ord_getPolicyDescription($creditType->policyId, $timeZoneId);
		
	}


//		$description .=  "Else section Needs a lot of Work (orders.php ord_getItemDescription) ";	//jh remove
//		$description .= "You have 1 millisecond left of Quota";  //jh remove

	return $description;
	

}


function ord_getPolicyDescription($policyId, $timeZoneId){

	$policy = ws_getPolicyById($policyId, $timeZoneId);	
	//echo "policy vardump: ";  //jh remove
   //var_dump($policy);		  //jh remove		
	$policyType = $policy->policyType;
	$absolute = $policy->absolute;
	
	$compatibleTimezone = substr($timeZoneId,10);
	date_default_timezone_set($compatibleTimezone);
	
		
	if($policyType =="NOEXPIRATION"){
		$description .= $policy['quotaInPeriod']." minutes. ";
		$description .= "This item does not expire. ";
	
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
