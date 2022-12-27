<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$sql = "INSERT INTO `Tables` VALUES (?,?,?,?,?)";
//$pkey_sql = "SELECT Table_ID FROM `Tables`";

$bind_types = "ssisi";

/* Generate custom primary key */
/*$rows = json_decode(dbConn($pkey_sql, array(), "select"))->numrows;
$rows_incr = $rows +1;
if(strlen($rows_incr) === 2 || strlen($rows_incr) === 1){
	$paded_key = str_pad($rows_incr, 3, "00", STR_PAD_LEFT);
}
if(isset($paded_key)){$pkey = "TBL".$paded_key;}else{$pkey = "TBL".$rows_incr;}*/
//echo $pkey;
array_unshift($clean_data, $bind_types, genPK('Tables', 'Table_ID', 'TBL'));

echo dbConn($sql,$clean_data, "insert");


?>