<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

$pdtNo = $_GET['item'];

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT ordNo,station,`to`,`time`, `status`,`bill`,payment FROM nest_restaurant.ordersextended left join orderdetails on ordNo = OrderNo where Station = '$station' AND  ProductNo = '$pdtNo' AND `time` > curdate() - INTERVAL 1 DAY + INTERVAL 19 HOUR group by ordNo  ORDER BY time DESC";
}else{
	$sql = "SELECT ordNo,station,`to`,`time`, `status`,`bill`,payment FROM nest_restaurant.ordersextended left join orderdetails on ordNo = OrderNo where ProductNo = '$pdtNo' AND `time` > curdate() - INTERVAL 1 DAY + INTERVAL 19 HOUR group by ordNo  ORDER BY time DESC";
}

$conn_res = json_decode(dbConn($sql, array(), 'select'));
if($conn_res->status === 1){
	foreach($conn_res->message as $v){
		$v->bill = number_format($v->bill);
		$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->time)) != date('d/m/Y')){
			$v->time = date("d/m/Y h:i A", strtotime($v->time));
		}else{
			$v->time = date("h:i A", strtotime($v->time));
		}
	}
	$res_records->status = $conn_res->status;
	$res_records->message = $conn_res->message;
	echo json_encode($res_records);
}else{
	$res_records->status = $conn_res->status;
	$res_records->message = $conn_res->message;
	echo json_encode($res_records);
}

?>
