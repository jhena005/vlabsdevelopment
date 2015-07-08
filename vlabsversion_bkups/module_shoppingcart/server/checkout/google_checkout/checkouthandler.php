<?php

/**
 * Copyright (C) 2007 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/* This is the response handler code that will be invoked every time
 * a notification or request is sent by the Google Server
 *
 * To allow this code to receive responses, the url for this file
 * must be set on the seller page under Settings->Integration as the
 * "API Callback URL'
 * Order processing commands can be sent automatically by placing these
 * commands appropriately
 *
 * To use this code for merchant-calculated feedback, this url must be
 * set also as the merchant-calculations-url when the cart is posted
 * Depending on your calculations for shipping, taxes, coupons and gift
 * certificates update parts of the code as required
 *
 */
chdir("..");
require_once('lib/googleresponse.php');
require_once('lib/googlemerchantcalculations.php');
require_once('lib/googleresult.php');
require_once('lib/googlerequest.php');
require_once('lib/googleitem.php');

require_once('../db/db.php');
require_once('../transactions.php');

class Order {

    public $email;
    public $id;
    public $purchasedate;
    public $fulfillmentorderstate;
    public $financialorderstate;
    public $ordernumber;
    public $lastmodification;

}

define('RESPONSE_HANDLER_ERROR_LOG_FILE', 'google_checkout/googleerror.log');
define('RESPONSE_HANDLER_LOG_FILE', 'google_checkout/googlemessage.log');

$config_file = "google_checkout/google.conf";
$comment = "#";

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

$merchant_id = $config_values['CONFIG_MERCHANT_ID'];  
$merchant_key = $config_values['CONFIG_MERCHANT_KEY'];  
$server_type = $config_values['CONFIG_SERVER_TYPE'];  
$currency = $config_values['CONFIG_CURRENCY'];

$Gresponse = new GoogleResponse($merchant_id, $merchant_key);

$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $currency);
//serial-number
//Setup the log file
$Gresponse->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, RESPONSE_HANDLER_LOG_FILE, L_ALL);
$Grequest->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, RESPONSE_HANDLER_LOG_FILE, L_ALL);


//How to grab the serial number!!!!
/* $serial = $_POST["serial-number"];
  $Gresponse->log->LogResponse("serial:" . $serial);

  if ($serial != null) {
  $Grequest->SendNotificationRequest($serial);
  $Gresponse->SendAck($serial);
  return;
  } */


// Retrieve the XML sent in the HTTP POST request to the ResponseHandler
$xml_response = isset($HTTP_RAW_POST_DATA) ?
        $HTTP_RAW_POST_DATA : file_get_contents("php://input");



if (get_magic_quotes_gpc ()) {
    $xml_response = stripslashes($xml_response);
}

list($root, $data) = $Gresponse->GetParsedXML($xml_response);

$Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);

/* $status = $Gresponse->HttpAuthentication();
  if(! $status) {
  die('authentication failed');

  } */

/* Commands to send the various order processing APIs
 * Send charge order : $Grequest->SendChargeOrder($data[$root]
 *    ['google-order-number']['VALUE'], <amount>);
 * Send process order : $Grequest->SendProcessOrder($data[$root]
 *    ['google-order-number']['VALUE']);
 * Send deliver order: $Grequest->SendDeliverOrder($data[$root]
 *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
 *    <send_mail>);
 * Send archive order: $Grequest->SendArchiveOrder($data[$root]
 *    ['google-order-number']['VALUE']);
 *
 */

switch ($root) {
    case "request-received": {
            break;
        }
    case "error": {
            break;
        }
    case "diagnosis": {
            break;
        }
    case "checkout-redirect": {
            break;
        }
    case "merchant-calculation-callback": {
            // Create the results and send it
            $merchant_calc = new GoogleMerchantCalculations($currency);

            // Loop through the list of address ids from the callback
            $addresses = get_arr_result($data[$root]['calculate']['addresses']['anonymous-address']);
            foreach ($addresses as $curr_address) {
                $curr_id = $curr_address['id'];
                $country = $curr_address['country-code']['VALUE'];
                $city = $curr_address['city']['VALUE'];
                $region = $curr_address['region']['VALUE'];
                $postal_code = $curr_address['postal-code']['VALUE'];

                // Loop through each shipping method if merchant-calculated shipping
                // support is to be provided
                if (isset($data[$root]['calculate']['shipping'])) {
                    $shipping = get_arr_result($data[$root]['calculate']['shipping']['method']);
                    foreach ($shipping as $curr_ship) {
                        $name = $curr_ship['name'];
                        //Compute the price for this shipping method and address id
                        $price = 12; // Modify this to get the actual price
                        $shippable = "true"; // Modify this as required
                        $merchant_result = new GoogleResult($curr_id);
                        $merchant_result->SetShippingDetails($name, $price, $shippable);

                        if ($data[$root]['calculate']['tax']['VALUE'] == "true") {
                            //Compute tax for this address id and shipping type
                            $amount = 15; // Modify this to the actual tax value
                            $merchant_result->SetTaxDetails($amount);
                        }

                        if (isset($data[$root]['calculate']['merchant-code-strings']
                                        ['merchant-code-string'])) {
                            $codes = get_arr_result($data[$root]['calculate']['merchant-code-strings']
                                            ['merchant-code-string']);
                            foreach ($codes as $curr_code) {
                                //Update this data as required to set whether the coupon is valid, the code and the amount
                                $coupons = new GoogleGiftcerts("true", $curr_code['code'], 10, "debugtest");
                                $merchant_result->AddGiftCertificates($coupons);
                            }
                        }
                        $merchant_calc->AddResult($merchant_result);
                    }
                } else {
                    $merchant_result = new GoogleResult($curr_id);
                    if ($data[$root]['calculate']['tax']['VALUE'] == "true") {
                        //Compute tax for this address id and shipping type
                        $amount = 15; // Modify this to the actual tax value
                        $merchant_result->SetTaxDetails($amount);
                    }
                    $codes = get_arr_result($data[$root]['calculate']['merchant-code-strings']
                                    ['merchant-code-string']);
                    foreach ($codes as $curr_code) {
                        //Update this data as required to set whether the coupon is valid, the code and the amount
                        $coupons = new GoogleGiftcerts("true", $curr_code['code'], 10, "debugtest");
                        $merchant_result->AddGiftCertificates($coupons);
                    }
                    $merchant_calc->AddResult($merchant_result);
                }
            }
            $Gresponse->ProcessMerchantCalculations($merchant_calc);
            break;
        }
    case "new-order-notification": {

            $email = $data[$root]['buyer-billing-address']['email']['VALUE'];
            $date = $data[$root]['timestamp']['VALUE'];
            $ordernumber = $data[$root]['google-order-number']['VALUE'];
            $financialstate = $data[$root]['financial-order-state']['VALUE'];
            $fulfillmentstate = $data[$root]['fulfillment-order-state']['VALUE'];
            $total = $data[$root]['order-total']['VALUE'];
            	
			$purchasedate = date(DATE_ATOM,strtotime($date));
			$Gresponse->log->LogResponse("Default timezone ".date_default_timezone_get());
			$Gresponse->log->LogResponse("Date with default timezone ".$purchasedate);
          	
            $neworder = new Order();
            $neworder->email = $email;
            $neworder->purchasedate = $purchasedate;
            $neworder->lastmofification = $date;
            $neworder->ordernumber = $ordernumber;
            $neworder->financialorderstate = $financialstate;
            $neworder->fulfillmentorderstate = $fulfillmentstate;
                                 
            db_addGoogleCheckoutOrder($neworder->email, $neworder->ordernumber, 
            			$neworder->purchasedate, $neworder->fulfillmentorderstate,
            			$neworder->financialorderstate);


            $dborder = db_getOrderByOrderNumber($neworder->ordernumber);
                      
			$total = 0;
            //Get shopping cart information
            $items = get_arr_result($data[$root]['shopping-cart']['items']['item']);
            foreach ($items as $item) {
                $itemname = $item['item-name']['VALUE'];
                $itemdescription = $item['item-description']['VALUE'];
                $currency = $item['unit-price']['currency'];
                $unitprice = $item['unit-price']['VALUE'];
                $quantity = $item['quantity']['VALUE'];

                //Get item from store inventory
                $sql_getitem = 'SELECT * from mdl_shoppingcart_store_inventory WHERE name = "' . $itemname . '" and description = "' . $itemdescription . '"';
                $dbitem = db_getrecord($sql_getitem);
                //Insert order summary for this item
                db_addOrderItem( $dborder->id, $dbitem->id, $quantity, $unitprice);
                $total += $quantity * $unitprice;
            }
            
            db_modifyOrderTotal($dborder->id, $total);
            
            $user = db_getUserByEmail($email);
           
            $Gresponse->SendAck(); //$data[$root]['serial-number']

            break;
        }
    case "authorization-amount-notification": {

            break;
        }
    case "order-state-change-notification": {

            $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
            $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];
            $ordernumber = $data[$root]['google-order-number']['VALUE'];
            $date = $data[$root]['timestamp']['VALUE'];
            
            $dborder = db_getOrderByOrderNumber($ordernumber);
            $financialStateHasChanged = true;
            $fulfillmentStateHasChanged = true;
            
            if($dborder->financialorderstate == $new_financial_state){
            	$financialStateHasChanged = false;	
            }
            
            if($dborder->fulfillmentorderstate == $new_fulfillment_order){
            	$fulfillmentStateHasChanged = false;
            }
            

            if($financialStateHasChanged || $fulfillmentStateHasChanged)
            {
            	db_modifyGoogleCheckoutOrder($new_financial_state,$new_fulfillment_order,$date,$ordernumber);
            	$dborder = db_getOrderByOrderNumber($ordernumber);
            }

            if($financialStateHasChanged){
	                switch ($new_financial_state) {
	                	case 'REVIEWING': {
	                		break;
	                	}
	                	case 'CHARGEABLE': {
	                		//$Grequest->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
	                		//$Grequest->SendChargeOrder($data[$root]['google-order-number']['VALUE'],'');
	                		break;
	                	}
	                	case 'CHARGING': {
	                		break;
	                	}
	                	case 'CHARGED': {
	                		
	                		$refundAmount = 0;
	             		
	                		$orderItemsSuccess = saveTransaction($dborder->id, true);
	                		
	                		$Gresponse->log->LogResponse("items count ".count($orderItemsSuccess));
	                		
	                		$itemsToCancel = array();
	                		
	                		//Iterate through the response
	                		foreach($orderItemsSuccess as $ois){
	                			
								$Gresponse->log->LogResponse("id ".$ois["id"]." - success: ".$ois["success"]);
								
	                			//If item could not be saved in web service, it should be deleted form the order summary
	                			if(!$ois["success"]){
                					                				
	                				//cancel order item
	                				db_cancelOrderItem($dborder->id, $ois["id"]);
	                				  				
	                				//get item from database
	                				$dbItem = db_getItem($ois["id"]);	

	                				$Gresponse->log->LogResponse("Get Order Item ".$dbItem->id." from ".$dborder->ordernumber);
	                				
	                				//get item details
	                				$dbOrderItem = db_getOrderItem($dborder->ordernumber, $dbItem->id);
	                				
	                				$Gresponse->log->LogResponse("Creating Google item ".$dbItem->name);
	                				$Gresponse->log->LogResponse("quantity: ".$dbOrderItem->quantity);
	                				$Gresponse->log->LogResponse("price: ".$dbItem->price);
	                				
	                				//create a google item
	                				$gitem = new GoogleItem($dbItem->name, // Item name
							        	$dbItem->description, // Item description
							        	$dbOrderItem->quantity, // Quantity
							        	$dbItem->price); // Unit price
									
							        $refundAmount += $dbOrderItem->quantity*$dbItem->price;
							        $Gresponse->log->LogResponse("refund subtotal: ".$refundAmount);
							        	        	
							        //set item unique id
							        $Gresponse->log->LogResponse("Set Merchant Id".$dbItem->id);
							        
						        	$gitem->SetMerchantItemId($dbItem->id);
						        	
						        	array_push($itemsToCancel, $gitem);
						        		        
	                			}

	                		}
	                		
	                		if(count($itemsToCancel)>0){
								
								//Compare total quantity of order items with cancel items to update order status
								$orderItems = db_getOrderItems($dborder->id);
																
								$Gresponse->log->LogResponse("From ".count($orderItems)." items, ".count($itemsToCancel)." will be cancelled");
																			
								$response = $Grequest->SendRefundOrder($dborder->ordernumber,$refundAmount,
									"Items could not be processed in Quota System. The most common reason ".
									"is that there were not enough resources to satisfy this request",
									"Contact the administrator for further details.");
								
								$response = $Grequest->SendCancelItems($dborder->ordernumber, $itemsToCancel, 
									"Items could not be processed in Quota System. The most common reason ".
									"is that there were not enough resources to satisfy this request");
	                		
	                			db_setOrderRefund($dborder->id, $dborder->refund+$refundAmount);
	                		}
	                		


	                		break;
	                	}
	                case 'PAYMENT_DECLINED': {
                	
                			$Gresponse->log->LogResponse("Canceling order ".$data[$root]['google-order-number']['VALUE']);
								
							$response = $Grequest->SendCancelOrder($data[$root]['google-order-number']['VALUE'],
								"Payment Declined","Contact Google Checkout for further details.");
								
							$Grequest->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
	                            "Sorry, your payment has been declined", true);
								
							$Gresponse->log->LogResponse("Response: ".array_to_json($response));
	                        break;
	                    }
	                case 'CANCELLED': {
	                		$Gresponse->log->LogResponse("Cancelled "+ $data[$root]['google-order-number']['VALUE']);
	                		
	                		$order = db_getOrderByOrderNumber($data[$root]['google-order-number']['VALUE']);
	                		cancelTransaction($order->id);
	                		
		                	$orderItems = db_getOrderItems($orderid);
							foreach ($orderItems as $orderItem){
								db_cancelOrderItem($orderid, $orderItem->itemid);
							}
      		
	                		$Grequest->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
	                            "Sorry, your order is cancelled by the store", true);
	                        break;
	                    }
	                case 'CANCELLED_BY_GOOGLE': {
	                		$Gresponse->log->LogResponse("Cancelled by Google "+ $data[$root]['google-order-number']['VALUE']);
	                		
	                		$order = db_getOrderByOrderNumber($data[$root]['google-order-number']['VALUE']);
	                		cancelTransaction($order->id);

	                		$orderItems = db_getOrderItems($orderid);
							foreach ($orderItems as $orderItem){
								db_cancelOrderItem($orderid, $orderItem->itemid);
							}
	                		
	                        $Grequest->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
	                            "Sorry, your order is cancelled by Google", true);
     
	                        break;
	                    }
	                default:
	                    break;
	            }            	
            }

			if($fulfillmentStateHasChanged){
			    switch ($new_fulfillment_order) {
	                case 'NEW': {
	                        break;
	                    }
	                case 'PROCESSING': {
	                        break;
	                    }
	                case 'DELIVERED': {
	                        break;
	                    }
	                case 'WILL_NOT_DELIVER': {
	                        break;
	                    }
	                default:
	                    break;
	            }
	
			}
			
			$Gresponse->SendAck();
            break;
        }
    case "charge-amount-notification": {
            //$Grequest->SendDeliverOrder($data[$root]['google-order-number']['VALUE'],
            //    <carrier>, <tracking-number>, <send-email>);
            //$Grequest->SendArchiveOrder($data[$root]['google-order-number']['VALUE'] );
            $Gresponse->SendAck();
            break;
        }
    case "chargeback-amount-notification": {
            $Gresponse->SendAck();
            break;
        }
    case "refund-amount-notification": {
            $Gresponse->SendAck();
            break;
        }
    case "risk-information-notification": {
            $Gresponse->SendAck();
            break;
        }
    default:
        $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
        break;
}
/* In case the XML API contains multiple open tags
  with the same value, then invoke this function and
  perform a foreach on the resultant array.
  This takes care of cases when there is only one unique tag
  or multiple tags.
  Examples of this are "anonymous-address", "merchant-code-string"
  from the merchant-calculations-callback API
 */

function get_arr_result($child_node) {
    $result = array();
    if (isset($child_node)) {
        if (is_associative_array($child_node)) {
            $result[] = $child_node;
        } else {
            foreach ($child_node as $curr_node) {
                $result[] = $curr_node;
            }
        }
    }
    return $result;
}

/* Returns true if a given variable represents an associative array */

function is_associative_array($var) {
    return is_array($var) && !is_numeric(implode('', array_keys($var)));
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
