<?php

//defining variables for connection
$host="";
$dbname="";
$dbuname= "";
$dbpass="";

require_once "functions.php";

try {
   $conn= new PDO("mysql:host=$host; dbname=$dbname;", $dbuname, $dbpass);
} catch (PDOException $th) {
    mysql_fatal_error();
    //die("fialed to connect to user database: ".$th->getMessage());
}



?>
