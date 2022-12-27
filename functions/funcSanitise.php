<?php 
function funcSanitise($data_arr){
	if(!is_array($data_arr)){
		$data_arr = trim($data_arr);
		$data_arr = stripslashes($data_arr);
		$data_arr = htmlspecialchars($data_arr);
	}
	return $data_arr;
}	
?>