<?php
require_once('db/db.php');
require_once ('ws/webserviceconfig.php');

/* * *
 * Saves the items in an order with $orderid in the QuotaSystem database.
 * To do this it calls addCredits with transaction type "PURCHASED QITH NO PAYMENT"
 *
 */


function saveTransaction($orderid, $payment) {

	//Get order from database
    $order = db_getOrderById($orderid);   

    //Get buyer info from database
    $userId = "";
	 $order_ordernumber = "";
	 foreach($order as $o){
		$userId = $o['userid'];
		$order_ordernumber = $o['ordernumber'];
	 }

    //Get order items
    $orderitems = db_getOrderItems($orderid);

 	//Initialize array that will hold all order items ids and if the web service saved them succesfully
    $orderItemsSuccess = array();
    
    //Initialize array that will hold items that will be set through the web service with rollback false
    $itemsArr = array();
    
    //Initialize index of order items array
    $itemIndex = 0;
    
    
     $itemsIndexArr = array();
    
    //print_r($orderitems);
    
    //Iterate thorugh $orderItems obtained from db
    foreach ($orderitems as $orderitem) {
 
    	//Get item from database
		
        $item = db_getItem($orderitem['itemid']);
		  $item_id = "";
		  $item_type = "";
		  $item_referenceid ="";
			foreach($item as $i){
				$item_referenceid = $i['referenceid'];
				$item_type = $i['type'];				
				$item_id = $i['id'];
			}
        
        //print_r($item);
        
        //Initialize item success assuming it will be true
        $orderItemsSuccess[$itemIndex] = array("id"=>$item_id, "success"=>false);
        
        //Get item type to see if it is a package or not
        $type = $item_type;
        //echo $item->name."-".$item->type;
        
        if ($type == "ITEM") {
			//Save item in itemsArr to send the request to the web service
            $quantity = $orderitem['quantity'];
            array_push($itemsArr, array("creditTypeId"=>$item_referenceid, 
							            "quantity"=>$quantity, 
							            "purchaseId"=>$order_ordernumber, 
							            "active"=>false));
        	array_push($itemsIndexArr, 	$itemIndex);
        	
        } else if ($type == "PACKAGE") {
        	
        	$orderItemsSuccess[$itemIndex]["success"]=true;

        	//Get package items
            $packageitems = db_getPackageItems($item_id); 
				$packageitem_itemid="";
				$packageitem_quantity="";
				foreach($packageitems as $p){
					$packageitem_quantity = $p['quantity'];
					$packageitem_itemid = $p['itemid'];
				}
				
            
            //Initialize array to send a ws request for package items only with rollback true
            $packageItemsArr = array();

            //Initialize the response
            $success = true;
            
            //Save items in request array
            foreach ($packageitems as $packageitem) {

                $item = db_getItem($packageitem_itemid);
					 $item_referenceid = "";
					 foreach($item as $i){
						$item_referenceid = $i['referenceid'];
					 }

                $quantity = $orderitem['quantity']*$packageitem_quantity;   
                array_push($packageItemsArr, array("creditTypeId"=>$item_referenceid, 
									                "quantity"=>$quantity, 
									                "purchaseId"=>$order_ordernumber."".$orderitem['itemid'], 
									                "active"=>false));                      
            }
            
            //Send request for package
            $response = ws_assignQuota($packageItemsArr, $userId, $payment, true);
               
            //print_r($response);
            
            //Check if an item failed to be saved, if it did set success to False
            foreach ($response as $r) {
            	
            	if(!$r->active){    	
	            	$orderItemsSuccess[$itemIndex]["success"]=$r->active;
	            	break;
            	}
            }          
        }

        //echo "Item success = ".$orderItemsSuccess[$itemIndex]["success"];
		$itemIndex++;
    }
    
    
    //print_r($orderItemsSuccess);
    //print_r($itemsIndexArr);
    
    //echo "Single Items :";
    //print_r($itemsArr);
    
    //Send request for order items that were not packages. if there are any
    $successIndex = 0;
	$assignments =  array();
    if(count($itemsArr)>0){
    	$response = ws_assignQuota($itemsArr, $userId,$payment, false);
    	
    	if(is_array($response)){
    		$assignments = array_merge($assignments,$response);
    	}else{
    		array_push($assignments, $response);
    	}
    	
    	
    	//print_r("assign quota response ".$assignments);
    	
    	$i = 0;    	 
    	//Go thorugh the response to check which item failed
    	foreach ($assignments as $assignment) {
    		
    		$index = $itemsIndexArr[$i++];
    		
    		//print_r("index ".$index);
    		
    		$orderItemsSuccess[$index]["success"]=$assignment->active;
    	}
    	     	
    }

    //echo "OrderItemsSuccess :";
    //print_r($orderItemsSuccess);
    return $orderItemsSuccess;
}


function cancelTransaction($orderid){
	
	//Get orde details
	$order = db_getOrderById($orderid);
	$user = db_getUserById($order->userid);
	$orderItems = db_getOrderItems($orderid);
		
	//Prepare request for the quota system cancel call
	$assignments = array();
	foreach($orderItems as $orderItem){
		$item = db_getItem($orderItem->itemid);
		$type = $item->type;
				
		if ($type == "ITEM") {
			$assignment = array("creditTypeId"=>$item->referenceid,
							"quantity"=>$orderItem->quantity,
							"purchaseId"=>$order->ordernumber,
							"active"=>!$orderItem->cancelled);
					
			array_push($assignments, $assignment);	
        	
        }else if ($type == "PACKAGE") {
        	
        	//Get package items
            $packageitems = db_getPackageItems($item->id); 
            
            //Initialize array to send a ws request for package items only with rollback true
            $packageItemsArr = array();

            //Save items in request array
            foreach ($packageitems as $packageitem) {
            	
                $item = db_getItem($packageitem->itemid);
                $quantity = $orderItem->quantity*$packageitem->quantity;   
              	$assignment = array( "creditTypeId"=>$item->referenceid, 
                					 "quantity"=>$quantity, 
                					 "purchaseId"=>$order->ordernumber."".$orderItem->itemid, 
                					 "active"=>!$orderItem->cancelled);                      
            
            	array_push($assignments, $assignment);	
            }            
        	
        }				
		
	}
			
	//call web service	
	$response = ws_cancelQuotaAssignment($assignments);	
	$assignmentsResponse = array();	
	if(!is_array($response)){
		array_push($assignmentsResponse, $response);
	}else{
		$assignmentsResponse = array_merge($assignmentsResponse,$response);
	}

	return $assignmentsResponse;
	
}




?>
