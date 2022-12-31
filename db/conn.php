<?php

function dbConn ($sql,$bind_args, $sqltype){
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$dbconn = new mysqli("127.0.0.1","root","","nest_restaurant");
	$json_response = new stdClass();

	if($dbconn->connect_error){
		$json_response->status = 0;
		$json_response->message = $dbconn->connect_error;
		//return json_encode($json_response);
	}else{
		if($sqltype === 'insert'){
			$p_stmt = $dbconn->prepare($sql);
			if($p_stmt){
				//$p_stmt->bind_param($bind_types, $id,$desc,$cap,$loc,$status);
				
				/*$bind_types = 'ssisi';
				$id="TS4";$desc="Test4";$cap=1;$loc="up";$status=1;

				$bind_args = array($bind_types,$id,$desc,$cap,$loc,$status);*/
				call_user_func_array(array($p_stmt, 'bind_param'), refVariables($bind_args));
				
				$p_stmt->execute();
				
				if($p_stmt->error === ""){
					$json_response->status = 1;
					$json_response->insertID = $p_stmt->insert_id;
					$json_response->message = "Succesfully Submitted";
				}else{
					$json_response->status = 0;
					$json_response->message = $p_stmt->error;
				}
			}else{
				$json_response->status = 0;
				$json_response->message = $p_stmt->error;
			}
			//return json_encode($json_response);
			
		}else if($sqltype === 'select'){
			$result = $dbconn->query($sql);
			if($result){
				if($result->num_rows > 0){
					$records = [];
					$idx = 0;
					while($row = $result->fetch_object()){
						$records[$idx] = $row;
						$idx++;
					}
					$json_response->status = 1;
					$json_response->numrows = $result->num_rows;
					$json_response->message = /*$result->fetch_object()*/$records;					
				}else{
					$json_response->status = 2;
					$json_response->numrows = $result->num_rows;
					$json_response->message = "No records";
				}
			}else{
				$json_response->status = 0;
				$json_response->message = $result->error;
			}
		}else if($sqltype === 'update'){
			$p_stmt = $dbconn->prepare($sql);
			if($p_stmt){
				
				call_user_func_array(array($p_stmt, 'bind_param'), refVariables($bind_args));
				
				$p_stmt->execute();
				
				if($p_stmt->error === ""){
					$json_response->status = 1;
					$json_response->message = "Updated Succesfully";
				}else{
					$json_response->status = 0;
					$json_response->message = $p_stmt->error;
				}
			}else{
				$json_response->status = 0;
				$json_response->message = $p_stmt->error;
			}
		}else if($sqltype === 'delete'){
			$result = $dbconn->query($sql);
			if($result){
				$json_response->status = 1;
				$json_response->message = "Deleted Succesfully";
			}else{
				$json_response->status = 0;
				$json_response->message = $result->error;
			}				
		}
	}
	return json_encode($json_response);
}

function refVariables($arr){//to maintain array variables as references
	$refs = array();//to hold the referenced variables
	foreach($arr as $k => $v){
		$refs[$k] = &$arr[$k];
	}
	return $refs;
}
?>