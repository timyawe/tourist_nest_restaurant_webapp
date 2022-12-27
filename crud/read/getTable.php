<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$tblID = $clean_data['tblID'];

$sql = "SELECT * FROM `Tables` WHERE Table_ID = '$tblID'";

require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
echo dbConn($sql, array(), "select");
?>