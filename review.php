<?php 

session_start();

if ($_POST['action'] == "review") {

	if($_POST['pass'] == 'QHW4Rqanb5'){
		$response = 'true';
		$_SESSION['userid'] = 'reviewer0@anonymous.esyn';
	} else if($_POST['pass'] == 'gqUbqk8LZJ'){
		$response = 'true';
		$_SESSION['userid'] = 'reviewer1@anonymous.esyn';
	} else if($_POST['pass'] == 'MI1gfAefO9'){
		$response = 'true';
		$_SESSION['userid'] = 'reviewer2@anonymous.esyn';
	} else if($_POST['pass'] == '2VTTOsiyFo'){
		$response = 'true';
		$_SESSION['userid'] = 'reviewer3@anonymous.esyn';
	} else if($_POST['pass'] == 'GzXwWakm0n'){
		$response = 'true';
		$_SESSION['userid'] = 'reviewer4@anonymous.esyn';
	} else {
		$response = 'false';
	}

	header('Content-type: application/json');
	echo $response;

}

?>