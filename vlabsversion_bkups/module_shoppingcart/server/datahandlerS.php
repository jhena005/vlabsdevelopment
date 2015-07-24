<?php


require_once((dirname(__FILE__)).'/db/db.php');
/*
echo "RAW POST DATA is:" . PHP_EOL ;
//var_dump($HTTP_RAW_POST_DATA);

$file = fopen("/modules/php_outfile.sql","w");
fwrite($file,$HTTP_RAW_POST_DATA);
fclose($file);
*/

ini_set('display_errors',1);

/* jh In production this are the values
ini_set('display_errors',0);
ini_set('log_errors',1);
*/

    //echo "before file open";
    $fileName = "php_outfileS.sql";

    $fp = fopen(G_ROOTPATH .'www/modules/'.$fileName, "w+");
    if ( !$fp ) {
     //echo $php_errormsg;
    }else {
        echo "file open successful";
        fwrite($fp, $HTTP_RAW_POST_DATA);
        fclose($fp);
    }

$output = shell_exec('mysql -u '.G_DBUSER.' -p'.G_DBPASSWD.' -D efront  <'. G_ROOTPATH .'www/modules/'.$fileName);


    // send success JSON
/*
$sourcePath = $_FILES['filename']['tmp_name'];       // Storing source path of the file in a variable

echo "_FILES is:" . PHP_EOL ;
var_dump($_FILES);
echo "sourcePath is: " . $sourcePath;

$targetPath = "/modules/".$_FILES['file']['name']; // Target path where file is to be stored
move_uploaded_file($sourcePath,$targetPath) ;    // Moving Uploaded file
*/


?>
