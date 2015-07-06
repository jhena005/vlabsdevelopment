
<?php

require_once("../../config.php");
require_once("lib.php");

function log($log)
{
    //Insert new log to the database
    //$date = new date(DATE_ATOM);

    $date = new date("yyyy-mm-dd HH:ii:ss");
    $sql = "INSERT INTO  mdl_shoppingcart_log (description, date) VALUES ('".$log."','".$date."');";
    execute_sql($sql);

}


function get_log()
{
    $sql = "SELECT * FROM mdl_shoppingcart_log;";
    return get_records_sql($sql);

}




?>