<?php
function updateActivityLog($activity, $description, $userID){
	$sql = "INSERT INTO ActivityLog (Activity, Description, UserID) VALUES (?,?,?)";
	return dbConn($sql, array('ssi', $activity, $description, $userID), 'insert');

}
?>