<?php
	
	require_once('db/db.php');

	
	function sendEmailToAdministrators($subject, $body){

		//Set headers for emails
		$headers = "From: quota_store\r\n";
		//$headers .= "Reply-To: " . strip_tags($_POST['req-email']) . "\r\n";
		//$headers .= "CC: susan@example.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		// TODO once the db_getAdministrators() is fixed, the below lines must be removed. 
		// This is just a temporary fix.
			$content = '<html>';
			$content .='	<body>';
			
			$salutation = '<p>Dear '.$admin->firstname.' '.$admin->lastname.'</p>';
			
			$footer = '<p><em>This is an automated message by the Quota Store.</em></p>';
		                      	                      
			$content .= $salutation . $body . $footer;
			
			$content .='	</body>';
			$content .='</html>';
	
			$myEmail = 'sadjadi@cs.fiu.edu';
			mail($myEmail, $subject, $content, $headers);	
	
		// TODO This fucntion does not return any admins. It needs to be fixed.
		$admins = db_getAdministrators();
		
		foreach ($admins as $admin){
			
			$content = '<html>';
			$content .='	<body>';
			
			$salutation = '<p>Dear '.$admin->firstname.' '.$admin->lastname.'</p>';
			
			$footer = '<p><em>This is an automated message by the Quota Store.</em></p>';
		                      	                      
			$content .= $salutation . $body . $footer;
			
			$content .='	</body>';
			$content .='</html>';
	
			mail($admin->email, $subject, $content, $headers);	
		}
	
	}
	
	function sendEmail($recipient,$subject, $bodyHTML){	
				
		//Set headers for emails
		$headers = "From: quota_store \r\n";
		//$headers .= "Reply-To: " . strip_tags($_POST['req-email']) . "\r\n";
		//$headers .= "CC: susan@example.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";		
		$salutation = '<p>Dear '.$recipient->firstname.' '.$recipient->lastname.',</p>';
		$footer = '<p><em>This is an automated message by the  Quota Store.</em></p>';
		$content = '<html>';
		$content .='	<body>';	                      	                      
		$content .= 	$salutation . $bodyHTML . $footer;		
		$content .='	</body>';
		$content .='</html>';			
		mail($recipient->email, $subject, $content, $headers);	

	}


?>