<?php
	
	require_once('db/db.php');

	
	function sendEmailToAdministrators($subject, $body){

		//Set headers for emails
		$headers = "From: quota_store\r\n";
		//$headers .= "Reply-To: " . strip_tags($_POST['req-email']) . "\r\n";
		//$headers .= "CC: susan@example.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		$admin = user_array(eF_executeQuery("select * from users where login='admin'")); //is fixed, the below lines must be removed.

		// This is just a temporary fix.
			$content = '<html>';
			$content .='	<body>';
			
			$salutation = '<p>Dear '.$admin['name'].' '.$admin['surname'].'</p>';
			
			$footer = '<p><em>This is an automated message by the Quota Store.</em></p>';
		                      	                      
			$content .= $salutation . $body . $footer;
			
			$content .='	</body>';
			$content .='</html>';
	
			$myEmail = 'sadjadi@cs.fiu.edu';

          /*
          echo "about to send email to Dr. Sadjadi: " . $myEmail . PHP_EOL;
          echo "subject: " .PHP_EOL;
          var_dump($subject);
          echo "content: ".PHP_EOL;
          var_dump($content);
          echo "headers: ".PHP_EOL;
          var_dump($headers);
			mail($myEmail, $subject, $content, $headers);
	       */

		// TODO This fucntion does not return any admins. It needs to be fixed.
		$admins = db_getAdministrators_new();
		
		foreach ($admins as $admin){
			
			$content = '<html>';
			$content .='	<body>';
			
			$salutation = '<p>Dear '.$admin['name'].' '.$admin['surname'].'</p>';
			
			$footer = '<p><em>This is an automated message by the Quota Store.</em></p>';
		                      	                      
			$content .= $salutation . $body . $footer;
			
			$content .='	</body>';
			$content .='</html>';

            /*
            echo "about to send email to admin: " . $admin['email'] .PHP_EOL;
            echo "subject: " .PHP_EOL;
            var_dump($subject);
            echo "content: ".PHP_EOL;
            var_dump($content);
            echo "headers: ".PHP_EOL;
            var_dump($headers);
            */

			mail($admin['email'], $subject, $content, $headers);
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
