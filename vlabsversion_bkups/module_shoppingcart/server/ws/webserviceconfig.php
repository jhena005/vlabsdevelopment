<?php

//define('WSDL_QS', 'http://localhost:8080/axis2/services/QuotaSystem?wsdl');
//define('LOCATION_QS', 'http://localhost:8080/axis2/services/QuotaSystem?wsdl');



define('WSDL_QS', 'http://vlabs.cis.fiu.edu:6060/axis2/services/QuotaSystem?wsdl');
define('LOCATION_QS','http://vlabs.cis.fiu.edu:6060/axis2/services/QuotaSystem?wsdl');

define('WSDL_VL', 'http://vlabs.cis.fiu.edu:6060/axis2/services/VirtualLabs?wsdl');
define('LOCATION_VL','http://vlabs.cis.fiu.edu:6060/axis2/services/VirtualLabs?wsdl');

ini_set("soap.wsdl_cache_enabled", "0");



function ws_getItemStartAndEndDates($policy, $timeZoneId){
	$policyType = $policy->policyType;
	$absolute = $policy->absolute;
	
	$compatibleTimezone = substr($timeZoneId,10);
	date_default_timezone_set($compatibleTimezone);
	
	
	if($absolute){
		$startDateTs = strtotime ( $policy->startDate );
	}else{
		$startDateTs = strtotime ("now");
	}
	
	$startDate =  date(DATE_ATOM, $startDateTs);
			
	if($policyType =="NOEXPIRATION"){
		$daysFromStart = 1000;
		
	}else if($policyType =="FIXED"){		
		$daysFromStart = $policy->daysInPeriod;
	
	}else{
		$daysFromStart = $policy->numberOfPeriods * $policy->daysInPeriod;										
	}
		
	$endDate = date(DATE_ATOM ,strtotime ( '+ '.$daysFromStart.' day' , $startDateTs ) );
	
	return $dates = array('startDate'=>$startDate, 'endDate'=>$endDate);
}


function ws_isResourceAvailable($itemId, $qty) {
	
	session_start(); 
	$userId = $_SESSION["userid"]; 
	$item = refactored_db_getItem($itemId);
	
	$result = true;
	
	if($item['type']=="PACKAGE"){
		$packageItems = db_getPackageItem($item['id']);
		
		foreach ($packageItems as $packageItem){
			$item = refactored_db_getItem($id);
			if(!ws_isResourceAvailable($item['id'], $packageItem['quantity']*$qty)){
				return false;
			}
		}		
	}else{
		
		$timeZoneId = db_getUserTimeZone($userId)['data'];
		$item = refactored_db_getItem($itemId);
	    $creditType = ws_getCreditTypeById($item['referenceid']);
	    $course = db_getCourseById($creditType->courseId);
	    $policy = ws_getPolicyById($creditType->policyId, $timeZoneId);
	    $dates = ws_getItemStartAndEndDates($policy, $timeZoneId);
	    $startDate = $dates['startDate'];
	    $endDate = $dates['endDate'];
	    $quota = $policy->quotaInPeriod * $qty;
	
	    try {
	        $params = array('course' => $course->fullname, 
	        				'resourceType' => $creditType->resource, 
	        				'start' => $startDate, 
	        				'end' => $endDate,
	        				'quota'=>$quota);
	        
			$client = new SoapClient(WSDL_VL, array('location' => LOCATION_VL));
	       $response = $client->isResourceAvailable($params);
			$result = $response->success;
	        
	    } catch (Exception $e) {
			$result = false;
	
	    } catch (SoapFault $soapfault) {
			$result = false;
	    }
	}
    
    return $result;
}


function ws_getPolicyById($policyId, $timeZoneId){

	try {
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
		//echo '<script type="text/javascript">alert("In webserviceconfig.php")</script>';
		//var_dump($client);
		$policy = $client->getPolicyById(array("policyId"=>$policyId, "timeZoneId"=>$timeZoneId));

    } catch (Exception $e) {
		$policy = null;

    } catch (SoapFault $soapfault) {    	
		$policy = null;

    }

	return $policy;
	
}


function ws_assignQuota($assignments, $userId, $payment, $rollback) {

    try {
    	if($payment) $p = true; else $p =false;
    	if($rollback) $r = true; else $r=false;
        $params = array('userId' => $userId, 'payment' => $p, 'rollback' => $r, 'assignment' => $assignments);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $response = $client->assignQuota($params);
		$result = $response->assignment;
        
    } catch (Exception $e) {
		$result = $assignments;

    } catch (SoapFault $soapfault) {
		$result = $assignments;
    }
    
    return $result;
}

function ws_cancelQuotaAssignment($assignments) {
	
    try {
    	if($payment) $p = true; else $p =false;
    	if($rollback) $r = true; else $r=false;
        $params = array('assignment' => $assignments);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $result = $client->cancelQuotaAssignments($params);
        $response = $result->assignment;
        
    } catch (Exception $e) {
		$response = $assignments;

    } catch (SoapFault $soapfault) {
		$response = $assignments;
    }
    

    return $response;
}


function ws_assignQuotaToCourse($assignments) {

    try {
        $params = array ('assignment' => $assignments);
  
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $response = $client->assignQuotaToCourse($params);
		$result = $response->assignment;
        
    } catch (Exception $e) {
		$result = $assignments;

    } catch (SoapFault $soapfault) {
		$result = $assignments;
    }
    
    return $result;
}

function ws_modifyCourseQuota($assignments) {

    try {
        $params = array ('assignment' => $assignments);
        
        //print_r($assignment);
        
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $response = $client->modifyCourseQuota($params);
		$result = $response->assignment;
        
    } catch (Exception $e) {
		$result = $assignments;

    } catch (SoapFault $soapfault) {
		$result = $assignments;
    }

    return $result;
}

function ws_cancelCourseQuota($assignments) {

    try {
        $params = array ('assignment' => $assignments);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $response = $client->cancelCourseQuota($params);
		$result = $response->assignment;
        
    } catch (Exception $e) {
    	$result = $assignments;

    } catch (SoapFault $soapfault) {
		$result = $assignments;
    }

    return $result;
}



function ws_getCreditTypeById($creditTypeId)
{
	try {
        $client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
        $creditType = $client->getCreditTypeById($creditTypeId);
        
    } catch (Exception $e) {
		$creditType = null;

    } catch (SoapFault $soapfault) {
		$creditType = null;

    }
  
	return $creditType;
}


function ws_getCreditTypesByCourse($courses_arr)
{
		$params = array('courseId' => $courses_arr);
		$client = new SoapClient(WSDL_QS, array('location' => LOCATION_QS));
		
		try {
			$response = $client->getCreditTypesByCourse($params);
			
		} catch (Exception $e) {
			
			$response->creditType = array();
			
		}
		
		
		return $response;
	
}





?>
