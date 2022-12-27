<?php
function createUpdateSql($arr, $table, $criteria_field, $criteria_v){
	$update_sql = "UPDATE `$table` SET ";
	foreach($arr as $v){
		if($v != $arr[array_key_last($arr)]){//requires version >=7.3
			$update_sql .= "$v = ?,";
		}else{
			$update_sql .= "$v = ?";
		}
	}
	$update_sql .= " WHERE $criteria_field = '$criteria_v'";
	return $update_sql;
}
?>