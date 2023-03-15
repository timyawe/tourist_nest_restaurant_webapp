<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);
$clean_data = array_map('funcSanitise', $json_data);
$userID = $clean_data['userID'];
if(json_decode(updateActivityLog('Logout', 'Logged out successfully', $userID))->status == 1){
	echo 1;
}
?>