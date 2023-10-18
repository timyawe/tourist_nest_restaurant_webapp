<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data = json_decode($_GET['_data'], true);
//print_r($json_data);

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

if($json_data['offerscategory'] != ''){
	$category = $json_data['offerscategory'];
	//echo $category;
}

if($json_data['item_id'] != ''){
	$item_id = $json_data['item_id'];
	//echo $category;
}

if(isset($from_date)){
	$where_clause = " `_date` >= '$from_date $from_period' AND `_date` <= '$to_date $to_period' ";
}else{
	$where_clause = " date(`_date`) = '$_date' ";
}

if(isset($category)){
	$where_clause .= " AND `RecipientCategory` = '$category' ";
}

if(isset($station)){
	$where_clause .= "AND station = '$station' "; 
}

if(isset($item_id)){
	$where_clause .= "AND ProductNo = '$item_id' "; 
}

$search_sql = "SELECT _date, RecipientCategory, station, staff, isDelivered, OffersExtended.isDeleted, OffersExtended.ID FROM OffersExtended left join offersdetails on OffersExtended.ID = OffersID WHERE $where_clause group by OffersExtended.ID ORDER BY _date DESC";

//echo $search_sql;
$res_records = new stdClass();
$res_conn = json_decode(dbConn($search_sql, array(), 'select'));
if($res_conn->status == 1){
	foreach($res_conn->message as $v){
		$v->_date = date("d/m/Y h:i A", strtotime($v->_date));
		$v->isDeleted = (int)$v->isDeleted;
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
