<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$usrNm = $clean_data['usrNm'];
$pwd = $clean_data['pwd'];

$sql = "SELECT ID, FirstName, UserType, AccessLevel FROM Users WHERE UserName = BINARY '$usrNm' AND Password = '$pwd' LIMIT 1";

echo dbConn($sql, array(), 'select');

?>