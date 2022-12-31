<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$sql = "INSERT INTO `Tables` VALUES (?,?,?,?,?)";

$bind_types = "ssisi";

array_unshift($clean_data, $bind_types, genPK('Tables', 'Table_ID', 'TBL'));

echo dbConn($sql,$clean_data, "insert");


?>