<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

$sql = "SELECT Product_No, Description FROM products join items_bought on Product_no = items_bought.ProductNo";

$sql_res_records = json_decode(dbConn($sql, array(), 'select'));

if($sql_res_records->status === 1){
	echo json_encode($res_records->message = $sql_res_records->message);
}
?>
