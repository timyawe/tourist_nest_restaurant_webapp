<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$usrNm = $clean_data['usrNm'];
$pwd = $clean_data['pwd'];

$sql = "SELECT ID, FirstName, UserType, AccessLevel FROM Users WHERE UserName = BINARY '$usrNm' AND Password = '$pwd' LIMIT 1";

$dbAccess = json_decode(dbConn($sql, array(), 'select'));
$res = new stdClass();

if($dbAccess->status == 1){
	//updateActivityLog('Login', 'Logged in succesfully', $dbAccess->message[0]->ID);
	$res->status = 1;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}else{
	//updateActivityLog('Login', 'Login Failure', $dbAccess->message[0]->ID);
	$res->status = $dbAccess->status;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}

?>