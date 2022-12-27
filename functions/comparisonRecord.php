<?php
function comparisonRecord($table, $field, $criteria_v){
	$sql = "SELECT * FROM $table WHERE $field = '$criteria_v'";
	$res_obj = json_decode(dbConn($sql, array(), "select"))->message[0];
	$res_arr = json_decode(json_encode($res_obj),true);//turns object to assoc array
	return $res_arr;
}
?>