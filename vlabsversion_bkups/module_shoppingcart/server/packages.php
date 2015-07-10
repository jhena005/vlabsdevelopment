
<?php

require_once('db/db.php');
require_once ('ws/webserviceconfig.php');

header("Content-type: text/x-json");

if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	$action = "";
}




if ($action == "reloadPackages") {


	$packages = db_getPackages();

	$formattedPackages= array();

	//if (is_array($packages)) {
	//echo "TESTING mysql_result";
   //var_dump($packages);
	//echo "END TESTING mysql_result";

		foreach ($packages as $package) {

			$p = array($package['id'],
			$package['name'],
			$package['description'],
			$package['billable'],
			$package['active'],
			$package['price'],
			$package['creationdate']
			);

			array_push($formattedPackages, $p);
		}
		 
//	}

	echo json_encode($formattedPackages);


} else if ($action == "getPackageItems") {


	if (isset($_POST['packageId'])) {
		$packageId = $_POST['packageId'];
	} else {
		$packageId = "";
	}
	
	$package = db_getItem($packageId);
    $package_billable = "";
    $package_array = array();

    if($package!=null) {
        foreach ($package as $p) {
            $package_billable = $p['billable'];
        }
        array_push($package_array, array("billable"=>$package_billable));
    }else{
        array_push($package_array , array("billable"=>"0"));
    }

	$summary = db_getPackageSummary($packageId);
	$formattedPackageItems = array();
	$packageTotal = 0;

		foreach ($summary as $packageDetail) {
			
			$subtotal = $packageDetail['quantity'] * $packageDetail['price'];
			$total += $subtotal;

			$item = db_getItem($packageDetail['itemid']);

			if($item!=null)
			{
                foreach($item as $i) {
                    $packageItem = array($packageDetail['id'],
                        $i['id'],
                        $i['name'],
                        $i['description'],
                        $packageDetail['quantity'],
                        $i['price'],
                        $packageDetail['price'],
                        $subtotal
                    );

                    array_push($formattedPackageItems, $packageItem);
                }
			}

	}

	echo json_encode(array("package"=>$package_array, "items"=>$formattedPackageItems));

}else if($action=="getPkgItem"){

	
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else {
		$id = "";
	}

	
	$packageDetail = db_getPackageItem($id);
    //echo "packageDetail is: ";
    //var_dump($packageDetail);
    //echo "packageDetail->itemid is: " . $packageDetail['itemid'];
	$item = refactored_db_getItem($packageDetail['itemid']);
	//echo "refactored_db_getItem is: ";
    //var_dump($item);
	$subtotal = $packageDetail['quantity'] * $packageDetail['price'];
				
	if($item!=null && $packageDetail!=null)
	{
		$packageItem = array("id"=>$packageDetail['id'],
		"itemid"=>$item['id'],
		"name"=>$item['name'],
		"description"=>$item['description'],
		"quantity"=>$packageDetail['quantity'],
		"price"=>$item['price'],
		"newprice"=>$packageDetail['price'],
		"subtotal"=>$subtotal
		);
	}
	
	echo json_encode(array("item"=>$packageItem));
			
} else if ($action == "addPackage") {

	if (isset($_POST['description'])) {
		$packagedesc = $_POST['description'];
	} else {
		$packagedesc = "";
	}

	if (isset($_POST['name'])) {
		$packagename = $_POST['name'];
	} else {
		$packagename = "";
	}


	if (isset($_POST['billable'])) {
		$billable = $_POST['billable'];
	} else {
		$billable = "";
	}

	if (isset($_POST['active'])) {
		$active = $_POST['active'];
	} else {
		$active = "";
	}

	if (db_addPackage($packagename,$packagedesc ,$active , $billable)) {
		$package = db_getItemByName($packagename); //jh refactored db call : )
		
		$p = array("id"=>$package['id'],
				"name"=>$package['name'],
				"description"=>$package['description'],
				"billable"=>$package['billable'],
				"active"=>$package['active'],
				"price"=>$package['price'],
				"creationdate"=>$package['creationdate']
				);	

		$result = array('success' => true, 'package' => $p);
	} else {
		$result = array('success' => false, 'message' =>"Package could not be added.".$sql);
	}

	echo json_encode($result);
} else if ($action == "editPackage") {

	if (isset($_POST['packagedesc'])) {
		$packagedesc = $_POST['packagedesc'];
	} else {
		$packagedesc = "";
	}

	if (isset($_POST['packagename'])) {
		$packagename = $_POST['packagename'];
	} else {
		$packagename = "";
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

	if (isset($_POST['id'])) {
		$packageid = $_POST['id'];
	} else {
		$packageid = "";
	}

	if (isset($_POST['active'])) {
		$active = $_POST['active'];
	} else {
		$active = "";
	}
	if ($active == 'true')
	$active = 1;
	else
	$active=0;




	if (isPackageBeingUsed($packageid)) {
		$result = array('success' => false, 'message' => 'Package cannot be deleted since it is being referenced in existing orders');
	} else {


		if (db_modifyPackage($packageid,$packagename,$packagedesc ,$active , $billable)) {

          //$p_array = array();
			$package = refactored_db_getItem($packageid);
          /*
			if(package!=null){
				foreach($package as $p) {
                    $p_array = array("id" => $p['id'],
                        "name" => $p['name'],
                        "description" => $p['description'],
                        "billable" => $p['billable'],
                        "active" => $p['active'],
                        "price" => $p['price'],
                        "creationdate" => $p['creationdate']
                    );
                }
			}
	        */
			$result = array('success' => true, 'package' => $package);
		} else {
			$result = array('success' => false, 'message' => "Package could not be modified");
		}
	}
	
	echo json_encode($result);

} else if ($action == "deletePackage") {

	if (isset($_POST['id'])) {
		$packageid = $_POST['id'];
	} else {
		$packageid = "";
	}
	
	if (isPackageBeingUsed($packageid)) {
		$result = array('success' => false, 'message' => 'Package cannot be deleted since it is being referenced in existing orders');
	} else {

		if (db_deletePackage($packageid)) {

			$result = array('success' => true);
		} else {
			$result = array('success' => false, 'message' => "Package could not be deleted");
		}
	}


	echo json_encode($result);
} else if ($action == "getElegibleItems") {


	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = "";
	}
	
	$package = db_getItem($packageid);
   $package_billable = "";
   foreach($package as $p){
       $package_billable = $p['billable'];
   }
	$billable = $package_billable;
	$billable = $billable==1;


	$output[] = '';

	
	$result = db_getElegibleItemsForPackage($packageid);

	if ($result == null)
		$result = array();

	if ($itemid != "") {
		$itemtoinclude = refactored_db_getItem($itemid);
		if ($itemtoinclude != null)
		$result = array_merge($result, $itemtoinclude);
	}

	$priceArr = array();
	
	foreach ($result as $item) {
		
		array_push($priceArr, array("id"=>$item['id'], "price"=>$item['price']));
		
		
		if($billable)
			$output[] = '<option value="' . $item->id . '">' . $item->name . '- $' . $item->price . '</option>';
		else 
			$output[] = '<option value="' . $item->id . '">' . $item->name . '- Not billable </option>';
	}




	$content = join('', $output);
	$response = array("content" => $content,"billable" => $billable, "prices"=>$priceArr);
	echo json_encode($response);
	
} else if ($action == "getPackage") {


	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = "";
	}
	
	$package = db_getItem($packageid);
	$packageItems = db_getPackageItems($packageid);
	$empty = !(is_array($packageItems)); 
	
	if(package!=null){
		
		$p = array("id"=>$package->id,
			"name"=>$package->name,
			"description"=>$package->description,
			"billable"=>$package->billable,
			"active"=>$package->active,
			"price"=>$package->price,
			"empty"=>$empty
			);		
		
	}



	echo json_encode($p);
	
}else if ($action == "addPkgItem") {

	if (isset($_POST['itemqty'])) {
		$itemqty = $_POST['itemqty'];
	} else {
		$itemqty = 0;
	}


	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
	} else {
		$itemid = 0;
	}


	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = 0;
	}

	if (isset($_POST['price'])) {
		$price = $_POST['price'];
	} else {
		$price = 0.0;
	}
	
	if (!isPackageBeingUsed($packageid)) {
		if (db_addItemToPackage($itemid,$itemqty ,$packageid , $price)) {
			db_updatePackageTotal($packageid);
			$packageDetail = db_getPackageItemByPackageAndItem($packageid, $itemid);		
			$item = refactored_db_getItem($packageDetail['itemid']);
				
			$subtotal = $packageDetail['quantity'] * $packageDetail['price'];
						
			if($item!=null && $packageDetail!=null)
			{
				$packageItem = array("id"=>$packageDetail['id'],
				"itemid"=>$item['id'],
				"name"=>$item['name'],
				"description"=>$item['description'],
				"quantity"=>$packageDetail['quantity'],
				"price"=>$item['price'],
				"newprice"=>$packageDetail['price'],
				"subtotal"=>$subtotal
				);
			}
		
			$result = array('success' => true, 'item'=>$packageItem);
		} else {
			$result = array('success' => false, 'message' => "Item could not be added succesfully");
		}
	}else{
		$result = array('success' => false, 'message' => 'Package item cannot be added since package is being reference in existing orders');
	}

	echo json_encode($result);
	
	
} else if ($action == "modifyPkgItem") {
	
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else {
		$id = "";
	}
	
	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = 0;
	}

	if (isset($_POST['itemqty'])) {
		$itemqty = $_POST['itemqty'];
	} else {
		$itemqty = 0;
	}


	if (isset($_POST['itemid'])) {
		$itemid = $_POST['itemid'];
	} else {
		$itemid = 0;
	}

	if (isset($_POST['price'])) {
		$price = $_POST['price'];
	} else {
		$price = 0.0;
	}



	if (!isPackageBeingUsed($packageid)) {
		$sql = 'UPDATE module_vlabs_shoppingcart_package_summary ';
		$sql .= 'SET quantity = ' . $itemqty . ', ';
		$sql .= 'price = ' . $price . ' ';
		$sql .= 'WHERE id  = ' . $id ;

		
		if (db_execute($sql)) {			
			db_updatePackageTotal($packageid);			
			$packageDetail = db_getPackageItem($id);	//refactored db call : )
			$item = refactored_db_getItem($packageDetail['itemid']);

			
			$subtotal = $packageDetail['quantity'] * $packageDetail['price'];
						
			if($item!=null && $packageDetail!=null)
			{
				$packageItem = array("id"=>$packageDetail['id'],
				"itemid"=>$item['id'],
				"name"=>$item['name'],
				"description"=>$item['description'],
				"quantity"=>$packageDetail['quantity'],
				"price"=>$item['price'],
				"newprice"=>$packageDetail['price'],
				"subtotal"=>$subtotal
				);
			}

			$result = array('success' => true, 'item' => $packageItem);
		} else {
			$result = array('success' => false, 'message' => 'Item could not be modified succesfully');
		}
	} else {
		$result = array('success' => false, 'message' => 'Package item cannot be edited since package is being reference in existing orders');
	}

	echo json_encode($result);
} else if ($action == "deletePkgItem") {


	if (isset($_POST['id'])) {
		$id = $_POST['id'];
	} else {
		$id = "";
	}

	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = "";
	}

	if (!isPackageBeingUsed($packageid)) {

		if (db_deletePackageItem($id, $packageid)) {
			if(db_updatePackageTotal($packageid)) {
                $result = array('success' => true);
            }
		} else {
			$result = array('success' => false, 'message' => "Error executing database operation");
		}
	} else {

		$result = array('success' => false, 'message' => 'Package item cannot be deleted since package is being reference in existing orders');
	}


	echo json_encode($result);
	
} else if ($action == "isPkgBillable") {


	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = 0;
	}

	$sql = "SELECT billable FROM module_vlabs_shoppingcart_store_inventory WHERE id = " . $packageid;
	$result = eF_ExecuteQuery($sql);
    $result_billable ="";
    foreach($result as $r){
        $result_billable = $r['billable'];
    }
	echo json_encode($result_billable=="1");
	
}else if ($action == "changeStatus") {


	if (isset($_POST['packageid'])) {
		$packageid = $_POST['packageid'];
	} else {
		$packageid = 0;
	}

	$success = db_changePackageStatus($packageid);
	
	$response = array('success' => $success);
	
	echo json_encode($response);
}

function getPackagesWithItems($items) {

	$values = "(";
	foreach ($items as $item) {
		$values.=$item['id'] . ",";
	}

	$values = substr($values, 0, -1);

	$values.=")";


	$sql = "SELECT DISTINCT * FROM module_vlabs_shoppingcart_store_inventory WHERE active = 1 and id IN ";
	$sql .= "(SELECT DISTINCT packageid FROM module_vlabs_shoppingcart_package_summary WHERE itemid IN " . $values . ")";
	
	$packages = eF_executeQuery($sql);
	
	$filteredPackages = array();

	foreach($packages as $p){
		
		$addPackage = true;
		$summary = refactored_db_getPackageSummary($p['id']);

		
		foreach($summary as $s){
			$elegible = false;

			foreach ($items as $item) {
				if($s['itemid'] == $item['id']){
					$elegible = true;
					break;
				}
			}
			if(!$elegible){
				$addPackage = false;
				break;
			} 
			
		}
		if($addPackage){
			array_push($filteredPackages, $p);
		}
		
	}

	return $filteredPackages;
}

function isPackageBeingUsed($packageid) {
	$sql = "SELECT * FROM module_vlabs_shoppingcart_order_summary ";
	$sql .= "WHERE itemid = " . $packageid;

	$order_items = db_getrecords($sql);
    $counter = 0;
    foreach($order_items as $o){
        $counter++;
    }

	if ($counter > 0)
	    return true;
	else
	    return false;
}



?>
