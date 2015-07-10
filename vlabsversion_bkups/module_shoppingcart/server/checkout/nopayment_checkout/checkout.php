<?php

require_once ('../../db/db.php');
require_once ('../../mailing.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
//require_once ('../../shoppingcart.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = "";
}


if ($action == "proceedToCheckout") {

    if (isset($_POST['cart'])) {
        $cart = $_POST['cart'];
    } else {
        $cart = "";
    }

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
    } else {
        $email = "";
    }


    $xml_obj = simplexml_load_string(stripslashes(urldecode($cart)));


    $total = 0;

    $output[] = '<table id="checkouttable">';
    $output[] = '<thead>';
    $output[] = '<tr>';
    $output[] = '<th >Item</th>';
    $output[] = '<th >Quantity</th>';
    $output[] = '</tr>';
    $output[] = '</thead>';

    $output[] = '<tbody>';


    $item_arr = $xml_obj->item;

    $arr_size = count($item_arr);

    if ($arr_size > 0) {


        foreach ($item_arr as $item) {

            $output[] = '<tr>';
            $output[] = '<td>' . $item->name . '</td>';
            $output[] = '<td>' . $item->quantity . '</td>';
            $output[] = '</tr>';
        }
    } else {
        $item = $item_arr->item;
        $output[] = '<tr>';
        $output[] = '<td>' . $item->name . '</td>';
        $output[] = '<td>' . $item->quantity . '</td>';
        $output[] = '</tr>';
    }
    $output[] = '</tbody>';
    $output[] = '</table>';

    $output2[] = '<div id="nopaymentform">';
    $output2[] = '	<form id="nopayment_form">';
    $output2[] = '		<label for="explanation"><strong>Explain in a short paragraph why you should be entitled to items in your cart:</strong></label>';
    $output2[] = '		<textarea id="explanation" name="payment-explanation" rows="5" ></textarea>';
    $output2[] = '	</form>';
    $output2[] = '	<button id="placeorderbtn">Place order</button>';
    $output2[] = '</div>';

	$response = array("tablecontent"=>join('',$output), "formcontent"=>join('',$output2));
    echo json_encode($response);
    
} else if ($action == "placeOrder") {
    $message = "";

    if (isset($_POST['cart'])) {
        $cart = $_POST['cart'];
    } else {
        $cart = "";
    }

    if (isset($_POST['userid'])) {
        $userid = $_POST['userid'];
    } else {
        $userid = "";
    }

    if (isset($_POST['payment-explanation'])) {
        $explanation = $_POST['payment-explanation'];
    } else {
        $explanation = "";
    }

    $xml_obj = simplexml_load_string(stripslashes(urldecode($cart)));

    $ordernumber = uniqid("IA",false);

    //Insert order in database
    if(!db_addNoPaymentOrder($userid, $ordernumber))
    {
    	$result = array("success"=>false, "message" => "Order could not be added");
    	echo json_encode($result);
    }

    
    //Get order id in database
	$dborder = db_getOrderByOrderNumber($ordernumber);	
    $total = 0;

    $output[] = '<table rules="all" style="border-color: #666;" cellpadding="10">';
    $output[] = '<thead>';
    $output[] = '<tr style="background: #eee;">';
    $output[] = '<th >Item</th>';
    $output[] = '<th >Quantity</th>';
    $output[] = '</tr>';
    $output[] = '</thead>';
    $output[] = '<tbody>';

    $item_arr = $xml_obj->item;
    $purchaseFailed = array();
     
    if (count($item_arr) > 0) {

    	foreach ($item_arr as $item) {

    		$output[] = '<tr>';
    		$output[] = '<td>' . $item->name . '</td>';
    		$output[] = '<td>' . $item->quantity . '</td>';
    		$output[] = '</tr>';

    		//Get item from store inventory ans save it in order summary
    		$dbitem = db_getItemByName($item->name);
    		$success = db_addOrderItem($dborder->id, $dbitem->id, $item->quantity, $item->price);

    		//Insert order summary for this item
    		if (!$success) {
    			array_push($purchaseFailed,$dbitem);
    		}

    	}

    	if(count($purchaseFailed)>0){
    		db_deleteOrder($dborder->id);
    	}
	
        
    } else {

        $item = $item_arr->item;
        $output[] = '<tr>';
        $output[] = '<td>' . $item->name . '</td>';
        $output[] = '<td>' . $item->quantity . '</td>';
        $output[] = '</tr>';

        //Get item from store inventory ans save it in order summary
        $dbitem = getItemByName($item->name);        
        $success = db_addOrderItem($dborder->id, $dbitem->id, $item->quantity, $item->price);

        //Insert order summary for this item
        if (!$success) {
        	array_push($purchaseFailed,$dbitem);
        }

    }
    $output[] = '</tbody>';
    $output[] = '</table>';

    //Send Email to customer
  
	$buyer = db_getUserById($dborder->userid);

    // Buyer Information: added by JAM - 06/18/2012	
    $profile_fields = profile_user_record($dborder->userid);
    
    $data = new object();
    $data->userfullname = fullname($buyer);
    $data->firstname = $buyer->firstname;
    $data->lastname = $buyer->lastname;
    $data->email = $buyer->email;
    $data->username = $buyer->username;
    $data->password = '';   //$buyer->password;
    $data->gender = $profile_fields->gender;
    $data->skypeid = $profile_fields->skypeid;
    $data->kaseyasalesrep = $profile_fields->kaseyasalesrep;
    $data->maintopic = $profile_fields->maintopic;
    $data->typebundle = $profile_fields->typebundle;
    $data->kaseyacustomerid = $profile_fields->kaseyacustomerid;
    $data->zone = $profile_fields->zone;
    $data->country = $buyer->country;
    $data->companyname = $profile_fields->companyname;
    $data->state = $profile_fields->state;
    $data->firstaccess = date("D M j G:i:s T Y", $buyer->firstaccess);
    $data->lastaccess = date("D M j G:i:s T Y", $buyer->lastaccess);
    
	
    $body= '<p>The order you recently placed is now pending for approval.'
            . 'You will receive an e-mail once the administrator has made a decision about your order.</p>
             <p>Please access your moodle account to review your order: http://ita-portal.cis.fiu.edu/moodle/mod/shoppingcart/view.php?id=156#ordersTab</p>' . join('', $output);
    $subject = 'Your order is pending for approval'; 
    // sms updated on June 7, 2011
	// sendEmail($buyer, $subject, $body);


    //Send email to Administrators  
    $body = '<p>A new order has been submitted by <strong>' . $buyer->firstname.' '.$buyer->lastname . '</strong>, which is waiting for your approval.</p>';
    $body .= '<p>First name: '.$data->firstname.'<br/>';
    $body .= 'Last name: '.$data->lastname.'<br/>';
    $body .= 'Gender: '.$data->gender.'<br/><br/>';
    $body .= 'Username: '.$data->username.'<br/>';
    $body .= 'Email address: '.$data->email.'<br/>';
    $body .= 'Skype or Google Talk ID: '.$data->skypeid.'<br/><br/>';
    $body .= 'Company Name: '.$data->companyname.'<br/>';
    $body .= 'City/town: '.$data->state.'<br/>';
    $body .= 'State: '.$data->state.'<br/>';
    $body .= 'Country: '.$data->country.'<br/>';
    $body .= 'Timezone: '.$data->zone.'<br/><br/>';
    $body .= 'Kaseya Customer ID: '.$data->kaseyacustomerid.'<br/>';
    $body .= 'Email or name of your Kaseya Sales Rep: '.$data->kaseyasalesrep.'<br/><br/>';
    $body .= 'The main topic of the course you are intending to enroll: '.$data->maintopic.'<br/>';
    $body .= 'The delivery type or the bundle: '.$data->typebundle.'<br/><br/>';
    $body .= 'First access: '.$data->firstaccess.'<br/>';
    $body .= 'Last access: '.$data->lastaccess.'</p>';
    $body .= '<p>Please access your moodle account to take further action: : http://ita-portal.cis.fiu.edu/moodle/mod/shoppingcart/view.php?id=156#ordersTab</p>' . join('', $output);
    $subject = 'New order from ' . $buyer->firstname.' '.$buyer->lastname;
    sendEmailToAdministrators($subject, $body);

    //Build Thank you page

    $output2[] = "<p>Thank you " . $buyer->username . ".</p>";
    $output2[] = '<p>You will receive an e-mail to '. $buyer->email. ' with further details.</p>';


    $content = join('', $output2);

    
     if(count($purchaseFailed)>0){
     	$message = "The following items could not be processed: \n";
     	foreach ($purchaseFailed as $pf){
     		$message .= $pf->name."\n";
     	}
     	
  		$result = array("success"=>false, "orderId" => $dborder->id, "message"=>$message);
    
     }else{
      	$result = array("success"=>true, "orderId" => $dborder->id, "content" => $content,"message"=>"Succesful!");
     	
     }

    //Remove Items from shopping cart
    deletePurchasedItems($dborder->id);   
    echo json_encode($result);
}


function deletePurchasedItems($orderid){
	
	session_start();
	$user = $_SESSION['userid'];
	
    if($orderid){
    	$summary = db_getOrderItems($orderid);
    	foreach($summary as $s){
    		$item = db_getItem($s->itemid);
    		$prefix = "i-";
    		if($item->type=="PACKAGE")
    			$prefix="p-";
    			
    		$_SESSION[$user.'cart'] = deleteItem($prefix."".$item->id, $_SESSION[$user.'cart']);	
    	}
    	
    }
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