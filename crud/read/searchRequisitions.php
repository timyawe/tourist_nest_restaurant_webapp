<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

//print_r($_GET['_data']);
$json_data = json_decode($_GET['_data'], true);

if($json_data['station'] != "All"){
	$station = $json_data['station'];
}

$category = $json_data['reqscategory'];

if(isset($json_data['search_date'])){
	$_date = $json_data['search_date'];
}

/*if(array_key_exists('search_date', $json_data)){
	$_date = $json_data['search_date'];
}*/

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

if($json_data['item_id'] != ''){
	$item_id = $json_data['item_id'];
}

//Generate where clause
$where_clause = " WHERE category = '$category' ";

if(isset($station)){
	$where_clause .= "AND station = '$station' "; 
}


if(isset($from_date)){
	$where_clause .= "AND _date >= '$from_date $from_period' AND _date <= '$to_date $to_period' ";
}else{
	$where_clause .= "AND date(_date) = '$_date' ";
}

if(isset($item_id)){
	$where_clause .= " AND ProductNo = '$item_id' ";
}

$search_sql = "SELECT reqNo, _date, category, station, `status`, staff, `type`, amount, ifnull(givenstatus,1) isFullyGiven FROM purchaseordersextended 
				left join (select purchaseno, givenstatus from internalrequisition_given_ext left join purchaseorder on internalrequisition_given_ext.purchaseno = purchaseorder.Purchase_No where givenstatus = 0 and RequisitionType = 'Internal') as given on reqno = given.purchaseno
				left join purchasedetails on reqNo = purchasedetails.PurchaseNo
			$where_clause GROUP BY reqNo ORDER BY _date DESC";

//echo $search_sql;
$res_records = new stdClass();
$res_conn = json_decode(dbConn($search_sql, array(), 'select'));
if($res_conn->status == 1){
	foreach($res_conn->message as $v){
		//$reqTotal += $v->amount;
		$v->amount = number_format($v->amount);
		//$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->_date)) != date('d/m/Y')){
			$v->_date = date("d/m/Y h:i A", strtotime($v->_date));
		}else{
			$v->_date = date("h:i A", strtotime($v->_date));
		}
	}	
	
	$res_records->status = 1;
	$res_records->message = $res_conn->message;
	//$res_records->sql = $search_sql;
	echo json_encode($res_records);
}else{
	$res_records->status = 2;
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}

?>
