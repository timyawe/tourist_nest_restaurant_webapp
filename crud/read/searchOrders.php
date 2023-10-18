<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data = json_decode($_GET['_data'], true);

if($json_data['station'] != "All"){
	$station = $json_data['station'];
}

if(isset($json_data['search_date'])){
	$_date = $json_data['search_date'];
}

if(array_key_exists('from_date', $json_data) && array_key_exists('to_date', $json_data)){
	$from_date = $json_data['from_date'];
	$to_date = $json_data['to_date'];
}

if(array_key_exists('from_period', $json_data) && $json_data['from_period'] != ''){
	$from_period = $json_data['from_period'];
}else{
	$from_period = "00:00:00";
}

if(array_key_exists('to_period', $json_data) && $json_data['to_period'] != ''){
	$to_period = $json_data['to_period'];
}else{
	$to_period = "00:00:00";
}

if($json_data['to'] != ''){
	$to = $json_data['ordDelvPnt'];
}

if($json_data['item_id'] != ''){
	$item_id = $json_data['item_id'];
}

//Generate where clause
if(isset($from_date)){
	$where_clause = " `time` >= '$from_date $from_period' AND `time` <= '$to_date $to_period' ";
}else{
	$where_clause = " date(`time`) = '$_date' ";
}

if(isset($to)){
	$where_clause .= " AND `To` = '$to' ";
}

if(isset($station)){
	$where_clause .= "AND station = '$station' "; 
}

if(isset($item_id)){
	$where_clause .= " AND ProductNo = '$item_id' ";
	$search_sql = "SELECT ordNo,station,`to`,`time`, `status`,`bill`,payment FROM ordersextended left join orderdetails on ordNo = OrderNo where $where_clause group by ordNo  ORDER BY time DESC";
}else{
	$search_sql = "SELECT * FROM OrdersExtended WHERE $where_clause ORDER BY `time` DESC";
}

//echo $search_sql;

$res_records = new stdClass();
$res_conn = json_decode(dbConn($search_sql, array(), 'select'));
if($res_conn->status == 1){
	foreach($res_conn->message as $v){
		$v->bill = number_format($v->bill);
		$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->time)) != date('d/m/Y')){
			$v->time = date("d/m/Y h:i A", strtotime($v->time));
		}else{
			$v->time = date("h:i A", strtotime($v->time));
		}
	}
	
	$res_records->status = 1;
	$res_records->message = $res_conn->message;
	//$res_records->sql = $reportsql;
	echo json_encode($res_records);
}else{
	$res_records->status = 2;
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}

?>
