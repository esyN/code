<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


// Start our session:
session_start();


// Build a global response
$globalresponse = "";

if ($_GET['action'] == "logout") {
	session_destroy();
	$response = json_encode(array("success" => true, "message" => "User has been successfully logged out."));
	//leave($response);
	header("Location: http://www.esyn.org/");
	die();
}

$query = "";

$mysqli = new mysqli("localhost", "XXX", "XXXX", "XXX");

// checkDB();

function checkDB() {
	/* check connection */
	if ($mysqli->connect_errno) {
	    printf("Connect failed: %s\n", $mysqli->connect_error);
	    return false;
	} else {
		return true;
	}
}




// Determine if we are asking for the most recent version or a point in history

// First, we need to determine if an action is set:
// FIX FOR LATER
// if (empty($_GET['action'])) {
// 	echo "ERROR";
// 	exit();
// }

if ($_POST['action'] == "describe") {

	describe($_POST['projectid'], true);

}

if ($_POST['action'] == "describePublished") {

	describePublished();
}

if ($_POST['action'] == "getPublishedDescription") {

	getPublishedDescription();
}


if ($_POST['action'] == "set") {

	set();

}

if ($_GET['action'] == "checkstorage") {

	getUserStorage();

}

if ($_POST['action'] == "publish") {

	publish();

}

if ($_POST['action'] == "deletePublished") {

	deletePublished();

}

if ($_GET['action'] == "viewpublished") {

	viewPublished();
	//echo "done";

}

if ($_GET['action'] == "getPublishedProjects") {

	getPublishedProjects();
	//echo "done";

}



function set() {

	global $mysqli;

	// echo $_POST['myviewers'];

	// $str = json_decode($_POST['viewers'], true); 
	// 	echo json_encode($str);

	$viewers = array_unique(json_decode(stripslashes($_POST['viewers'])));
	$editors = array_unique(json_decode(stripslashes($_POST['editors'])));
	$projectid = $_POST['projectid'];
	$label = $_POST['label'];
	$label = $mysqli->real_escape_string($label); // Added to avoid SQL injections!

	// Clear our all of our viewers since we're not doing diffs (too much work). Then we will reload them.
	$query  = "DELETE FROM esyn.permissions WHERE projectid='{$projectid}'";
	echo "\nrunning query: \n" . $query;
	$result = $mysqli->query($query);

	// Now add our viewers:


	foreach ($viewers as $viewer) {

		if (!in_array($viewer, $editors)) {
			echo "next viewer: " . $viewer;

			$permissionsQuery = "INSERT INTO `esyn`.`permissions` (`userid`, `projectid`, `editor`) VALUES ('" . $viewer . "', '" . $projectid . "', 0)";
			echo "\nrunning query: \n" . $permissionsQuery;
			$result = $mysqli->query($permissionsQuery);
		}

	}

	// Now add our viewers:
	foreach ($editors as $editor) {

		echo "next editor: " . $editor;

		$permissionsQuery = "INSERT INTO `esyn`.`permissions` (`userid`, `projectid`, `editor`) VALUES ('" . $editor . "', '" . $projectid . "', 1)";
		echo "\nrunning query: \n" . $permissionsQuery;
		$result = $mysqli->query($permissionsQuery);

	}

	$labelUpdate = "UPDATE networks SET label='{$label}' WHERE projectid='{$projectid}'";
	echo "\nrunning query: \n" . $labelUpdate;
	$result = $mysqli->query($labelUpdate);

	exit();












	// $response;
	
	// // Remember to first ask if we have permission to view this chart!

	// $dataQuery = "SELECT graphs.projectid, graphs.historyid, graphs.label, graphs.public FROM graphs WHERE projectid={$pprojectid}";
	// $permissionsQuery = "SELECT permissions.userid, permissions.editor FROM permissions WHERE projectid={$pprojectid}";
	// $ownerQuery = "SELECT networks.owner, networks.projectid, networks.label FROM networks WHERE projectid={$pprojectid}";

	// // Loop through our permissions query results

	// if ($permissionsResult = $mysqli->query($permissionsQuery)) {

	// 	$editors = array();
	// 	$viewers = array();

	// 	while ($row = $permissionsResult->fetch_assoc()) {

	// 		if ($row['editor'] > 0) {
	// 			array_push($editors, $row['userid']);
	// 		} else {
	// 			array_push($viewers, $row['userid']);
	// 		}
	// 	}

	// 	$response['editors'] = $editors;
	// 	$response['viewers'] = $viewers;

	// }


	// // Loop through our permissions query results

	// if ($result = $mysqli->query($ownerQuery)) {
	// 	$row = $result->fetch_assoc();
	// 	$response['owner'] = $row['owner'];
	// 	$response['projectid'] = $row['projectid'];
	// 	$response['label'] = $row['label'];
	// }

	// if ($result = $mysqli->query($dataQuery)) {
	// 	$row = $result->fetch_assoc();
	// 	$response['visibility'] = $row['public'];
	// }


	// leave(json_encode($response));


}









///////////////////


function viewPublished() {

	// echo "view published running";
	global $mysqli;

	$response = "";

	$publishid = $_GET['publishedid'];

	$query = "SELECT * FROM published WHERE publishid='{$publishid}'";




		// Walk through our response and build our response:
	if ($result = $mysqli->query($query)) {

		// Update our hit counter by one:
		$updateCounterQuery = "UPDATE published SET views =views+1 WHERE publishid='{$publishid}'";
		$updateCounterResult = $mysqli->query($updateCounterQuery);

		while ($row = $result->fetch_assoc()) {

				$response = $row['data'];
				leaveWithoutJSON($response);
		}

	}

	

	


	$response = json_encode(array("success" => false, "message" => "The requested published network does not exist."));
	leave($response);

}









////////////////




if ($_GET['action'] == "view") {

	$response = "";

	$historyid = -1;

	if (empty($_GET['historyid'])) {

	} else {
		$historyid = intval($_GET['historyid']);
	}

	if ($historyid < 0) {
		$query = "SELECT * FROM graphs WHERE projectid=". $_GET['projectid'] . " ORDER by time DESC Limit 1";
	 } else {
		$query = "SELECT * FROM graphs WHERE projectid=". $_GET['projectid'] . " AND historyid=" . $historyid . " ORDER by historyid ASC Limit 1";
	}

		// Walk through our response and build our response:
	if ($result = $mysqli->query($query)) {

		while ($row = $result->fetch_assoc()) {

			// Determine if the graph is public:
			if ($row['public'] > 0) {
				// WE ARE PUBLIC
				$response = $row['data'];
				leaveWithoutJSON($response);
			} else {

				if (signedin()) {
					// Now we have to determine if we're allowed to see the graph:

					$projectid = $_GET['projectid'];
					$userid = $_SESSION['userid'];

					$ownerQuery = "SELECT * FROM networks WHERE projectid='{$projectid}' AND owner='{$userid}'";


					if ($permissionResult = $mysqli->query($ownerQuery)) {

						$response = "";

						while ($permissionRow = $permissionResult->fetch_assoc()) {
							$response = $row['data'];
							leaveWithoutJSON($response);
						}


					}

					// Have we been granted permission to see it?


					$permissionQuery = "SELECT * FROM permissions WHERE projectid=". $_GET['projectid'] . " AND userid='". $_SESSION['userid'] . "'";

					if ($permissionResult = $mysqli->query($permissionQuery)) {

						$response = "";

						while ($permissionRow = $permissionResult->fetch_assoc()) {
							$response = $row['data'];
							leaveWithoutJSON($response);
						}

						
						messageDenied();
					}
				} else {
					messageLogin();
				}



				

			}

			
		}



	}

	$response = json_encode(array("success" => false, "message" => "The requested history does not exist."));
	leave($response);

	//leave($response);

} 

function signedin() {


	if (empty($_SESSION['userid'])) {
		return false;
	} else {
		return true;
	}
}

function messageLogin() {
	$response = json_encode(array("success" => false, "message" => "You must be logged in to view this graph."));
	leave($response);
}

function messageDenied() {
	$response = json_encode(array("success" => false, "message" => "You do not have permission to complete this action."));
	leave($response);
}


////////////////////////////////////////////////////////
// ALL ACTIONS FROM THIS POINT ON REQUIRE AUTHENTICATION



if ($_POST['action'] == "save") {





	// Are we saving with a projectid already supplied?
	//
	// If there is no projectid then the user should be allowed to continue, but it will save as a new network graph.

	if (empty($_POST['projectid'])) {

		// Abort the save since the user is not logged in.
		if (empty($_SESSION['userid'])) {

			$response = json_encode(array("success" => false, "projectid" => $projectid, "action" => "save", "message" => "You must be logged in to save your network to the server."));
			leave($response);

		}



		// Set easier variables for string replacement.
		$data = $_POST['data'];
		$userid = $_SESSION['userid'];
		
		// Decode our label:


		$label = $_POST['label'];

		$label = urldecode($label); 
		$label = addslashes($label); //added by Dan

		$type = $_POST['type'];

		// Fetch our highest projectid
		$query = "SELECT MAX(projectid) AS projectid FROM graphs";
		$nextid = 0;

		if ($result = $mysqli->query($query)) {

			while ($row = $result->fetch_assoc()) {
				$nextid = $row['projectid'] + 1;
			}

		}

		// echo "Caching to projectid: " . $nextid;
		$escapedData = addslashes($data);


		// Save our 
		$dataquery = "INSERT INTO `esyn`.`graphs` (`historyid`, `data`, `projectid`, `time`, `public`) VALUES (NULL, '" . $escapedData . "', '" . $nextid . "', CURRENT_TIMESTAMP, '0')";
//		$newquery = "INSERT INTO `esyn`.`graphs` (`historyid`, `data`, `projectid`, `label`, `time`, `public`) VALUES (NULL, '" . $escapedData . "', '" . $nextid . "', '" . $label . "', CURRENT_TIMESTAMP, '0')";
		$newresult = mysqli_query($mysqli, $dataquery);

//INSERT INTO networks (projectid, label, owner, type) VALUES ('1919', '12345', 'joshkh@gmail.com', 'sdgsgsdg')

		
		$networkquery = "INSERT INTO networks (projectid, label, owner, type) VALUES ('{$nextid}', '{$label}', '{$userid}', '{$type}')";
		//$networkQuery = "INSERT INTO networks ('projectid', 'owner', 'label', 'type') VALUES ('{$nextid}', '{$userid}', '{$label}', '0')";

		//echo 'network query ' . $networkquery;
		$networkresult = mysqli_query($mysqli, $networkquery);
		//echo 'network query result ' . $networkresult;

		




		// $permissionsQuery = "INSERT INTO `esyn`.`permissions` (`userid`, `projectid`) VALUES ('" . $_SESSION['userid'] . "', '" . $nextid . "')";

		// $permissionsresult = mysqli_query($mysqli, $permissionsQuery);

		// echo "done executing: " . $permissionsQuery;

		$response = "";
		// echo "response: " . $response;


		if($newresult && $networkresult) {
		    $response = json_encode(array("success" => true, "projectid" => $nextid, "action" => "save", "message" => "Saved a new graph and name successfully."));
		    // leave($response);
		} else if($newresult){
			$response = json_encode(array("success" => false, "message" => "saving network data worked but saving the name did not.", "nwq" => $networkquery, "nwr" => $networkresult));
		} else {
		    $response = json_encode(array("success" => false, "message" => "Error saving new graph."));
		    // echo "response: " . $response;
		    //leave($response);
		}

		// $updateLabel = "
		// # Select the most recent version of all projects for a given user

		// INSERT INTO 'esyn'.'networks' ('projectid', 'label', 'owner')
		// VALUES ('" . $nextid . "','" . $label . "','" . $_SESSION['userid'] . "')";


		// $updateLabel = "INSERT INTO `esyn`.`networks` (`projectid`, `label`, `owner`) VALUES ('{$nextid}', '{$label}', '{$userid}')";
		// // echo $updateLabel;

		// $updateLabelResult = mysqli_query($mysqli, $updateLabel);

		//exit();


		leave($response);

	} else {

		// Now determine if we're allowed to save the changes to this projectid

		$response = "";

		$data = $_POST['data'];
		$label = $_POST['label'];

		$label = urldecode($label);
		$label = addslashes($label); //added by Dan
		$projectid = $_POST['projectid'];
		$userid = $_SESSION['userid'];

		$escapedData = addslashes($data);



		$allowed = false;
		
		$ownerQuery = "SELECT * FROM networks WHERE projectid='{$projectid}' AND owner='{$userid}'";


		if ($permissionResult = $mysqli->query($ownerQuery)) {

			$response = "";

			while ($permissionRow = $permissionResult->fetch_assoc()) {
				$allowed = true;
			}


		}

		if ($allowed == false) {


			$editorQuery = "SELECT * FROM permissions WHERE projectid='{$projectid}' AND userid='{$userid}' AND editor=1";


			if ($permissionResult = $mysqli->query($editorQuery)) {

				$response = "";

				while ($permissionRow = $permissionResult->fetch_assoc()) {
					$allowed = true;
				}


			}



		}

		if ($allowed == true) {


			
			// DELETE RECORDS MORE THAN 10 HISTORY ITEMS

			// Get our 11th record (since we allow 10 history + 1 current)
			$maxDateQuery = "SELECT time FROM graphs WHERE projectid='{$projectid}' ORDER BY time DESC LIMIT 9,1";

			// Run our query:
			if ($maxDateResult = $mysqli->query($maxDateQuery)) {

				// If we have more than 0 zero rows returned then we've reached 11
				if (intval($maxDateResult->num_rows) > 0) {
					$row = $maxDateResult->fetch_assoc();


					$deleteAfter = $row['time']; // The time of the 11th graph

					// Delete after that time
					$deleteQuery = "DELETE FROM graphs WHERE projectid='{$projectid}' AND time<'{$deleteAfter}'";

					$deleteResult = $mysqli->query($deleteQuery);
				}

			}

			$newquery = "INSERT INTO `esyn`.`graphs` (`historyid`, `data`, `projectid`, `label`, `time`, `public`) VALUES (NULL, '" . $escapedData . "', '" . $projectid . "', '" . $label . "', CURRENT_TIMESTAMP, '0')";

			$newresult = mysqli_query($mysqli, $newquery);

			if($newresult) {
			   	$response = json_encode(array("success" => true, "projectid" => $projectid, "action" => "save", "message" => "Saved existing graph successfully."));
			   
			} else {
			    $response.= json_encode(array("success" => false, "message" => "Error saving existing graph."));
			    // leave($response);
			}
		} else {
			messageDenied();
		}


		leave($response);


	}

}




if ($_POST['action'] == "delete") {

		// $response = "";

		// $projectid = $_POST['projectid'];

		// // Are we allowed to delete this?

		// $permissionQuery = "SELECT * FROM permissions WHERE projectid=". $_POST['projectid'] . " AND userid='". $_SESSION['userid'] . "'";

		// if ($permissionResult = $mysqli->query($permissionQuery)) {

		// 	$response = "";

		// 	while ($permissionRow = $permissionResult->fetch_assoc()) {
		// 		$deletequery = "DELETE FROM `esyn`.`graphs` WHERE projectid = '{$projectid}'";
		// 		echo $deletequery;
		// 		$response.= json_encode(array("success" => true, "message" => "Deleted graph."));
		// 		leave($response);
		// 	}

			
		// 	messageDenied();
		// }

		if (deleteNetworkProject($_POST['projectid'])) {
			echo "finished delete";
		} else {
			echo "error deleting";
		}

		goHome();



}



// Published a network graph if the user is the owner:
function publish() {

	global $mysqli;

	$userid = $_SESSION['userid'];
	$projectid = $_POST['projectid'];

	if (isUserNetworkOwner($userid, $projectid)) {

		// Get the description of our network:
		$network = describe($projectid, false);

		// Fill in some of our values:
		$label = addslashes($network['label']); //added by Dan
		$type = $network['type'];
		$owner = $userid;

		// Now fetch the network's data:
		$data = addslashes(getLatestNetworkData($projectid));

		

		$escapedDescription = addslashes($_POST['description']);
		$escapedOwnername = addslashes($_POST['ownername']);





		$tags = $_POST['tags'];

		// echo "TAGS: " . $_POST['tags'];
		

		// Construct our query:
		$publishQuery = "INSERT INTO published (label, type, owner, description, data, ownername) VALUES ('{$label}', '{$type}', '{$owner}', '{$escapedDescription}', '{$data}', '{$escapedOwnername}')";

		// TODO: Construct our query for the tag table


		// Inform the user of our success
		if ($result = $mysqli->query($publishQuery)) {
			$response = json_encode(array("success" => true, "projectid" => $projectid, "action" => "publish", "message" => "Your network graph has been published."));
			
			// Now add our tags if they exist:
			$publishid = $mysqli->insert_id;

			foreach ($tags as $tag) {

				if (!empty($tag)) {
					$tag = $mysqli->real_escape_string($tag);
					$tagQuery = "INSERT INTO publishtags (publishid, name) VALUES ('{$publishid}', '{$tag}')";
					$tagResult = $mysqli->query($tagQuery);
				}

			}

			



			leave($response);
		} else {
			$response = json_encode(array("success" => false, "projectid" => $projectid, "action" => "publish", "message" => "You are authorized to complete this action, however an error has occurred."));
			leave($response);
		}

	} else {
		$response = json_encode(array("success" => false, "projectid" => $projectid, "action" => "publish", "message" => "You are not authorized to complete this action."));
		leave($response);
	}


}

// Published a network graph if the user is the owner:
function deletePublished() {

	global $mysqli;

	$userid = $_SESSION['userid'];
	$publishid = $_POST['publishid'];

	if (isUserPublishedNetworkOwner($userid, $publishid)) {

		$query  = "DELETE FROM esyn.published WHERE publishid='{$publishid}'";
		
		if ($result = $mysqli->query($query)) {
			$response = json_encode(array("success" => true, "projectid" => $projectid, "action" => "deletepublished", "message" => "Your published network graph has been deleted."));
			$tagquery  = "DELETE FROM esyn.publishtags WHERE publishid='{$publishid}'";
			$tagresult = $mysqli->query($tagquery);

			leave($response);
		} else {
			$response = json_encode(array("success" => false, "projectid" => $projectid, "action" => "deletepublished", "message" => "You are authorized to complete this action, however an error has occurred."));
			leave($response);
		}

	} else {
		$response = json_encode(array("success" => false, "projectid" => $projectid, "action" => "deletepublished", "message" => "You are not authorized to complete this action."));
		leave($response);
	}


}








// Determines whether or not the user has shared rights of the object.
function describe($pprojectid, $leave) {

	global $mysqli;

	$response;

	
	// Remember to first ask if we have permission to view this chart!

	$dataQuery = "SELECT graphs.projectid, graphs.historyid, graphs.label, graphs.public FROM graphs WHERE projectid={$pprojectid}";
	$permissionsQuery = "SELECT permissions.userid, permissions.editor FROM permissions WHERE projectid={$pprojectid}";
	$ownerQuery = "SELECT networks.owner, networks.projectid, networks.label, networks.type FROM networks WHERE projectid={$pprojectid}";

	// Loop through our permissions query results

	if ($permissionsResult = $mysqli->query($permissionsQuery)) {

		$editors = array();
		$viewers = array();

		while ($row = $permissionsResult->fetch_assoc()) {

			if ($row['editor'] > 0) {
				array_push($editors, $row['userid']);
			} else {
				array_push($viewers, $row['userid']);
			}
		}

		$response['editors'] = $editors;
		$response['viewers'] = $viewers;

	}


	// Loop through our permissions query results

	if ($result = $mysqli->query($ownerQuery)) {
		$row = $result->fetch_assoc();
		$response['owner'] = $row['owner'];
		$response['projectid'] = $row['projectid'];
		$response['label'] = $row['label'];
		$response['type'] = $row['type'];
	}

	if ($result = $mysqli->query($dataQuery)) {
		$row = $result->fetch_assoc();
		$response['visibility'] = $row['public'];
	}

	$response['sharecount'] = getSharedCount();

	if ($leave) {

		leave(json_encode($response));
	} else {
		return $response;
	}


}

// Published Project
function describePublished() {

	global $mysqli;

	$ppublishedid = $_POST['publishedid'];

	$response;

	$dataQuery = "SELECT * FROM published WHERE publishid={$ppublishedid}";

	if ($result = $mysqli->query($dataQuery)) {
		$row = $result->fetch_assoc();
		$response['publishedid'] = $row['publishid'];
		$response['label'] = $row['label'];
		$response['type'] = $row['type'];
		$response['owner'] = $row['owner'];
		$response['publishdate'] = $row['publishdate'];
		$response['data'] = $row['data'];
		$response['description'] = $row['description'];
		$response['views'] = $row['views'];
	}

	leave(json_encode($response));



}



// Published Project
function getPublishedProjects() {

	

	global $mysqli;

	$response;

	$resultsArr = array();

	$dataQuery = "SELECT * FROM published";

	if ($result = $mysqli->query($dataQuery)) {

		while ($row = $result->fetch_assoc()) {

			$tempArr = array();



			$tempArr['publishedid'] = $row['publishid'];
			$tempArr['label'] = $row['label'];
			$tempArr['type'] = $row['type'];

			$date = new DateTime($row['publishdate']);


			$tempArr['publishdate'] = $date->format('d/m/y');


			$tempArr['description'] = $row['description'];
			$tempArr['ownername'] = $row['ownername'];
			$tempArr['views'] = $row['views'];


			$nextid = $row['publishid'];
			$tags = array();

			// Now get our tags!
			// Get the tags associated with the project:
            $tagQuery = "SELECT * FROM publishtags WHERE publishid='{$nextid}'";

            if ($tagResult = $mysqli->query($tagQuery)) {

              while ($tagRow = $tagResult->fetch_assoc()) {
                array_push($tags, $tagRow["name"]);
              }

            }

            $tempArr['tags'] = $tags;

			array_push($resultsArr, $tempArr);

		}
		
	}

	leave(json_encode($resultsArr));


}

// Published Project
function getPublishedDescription() {

	global $mysqli;

	$ppublishedid = $_POST['publishedid'];

	$response;

	$dataQuery = "SELECT * FROM published WHERE publishid={$ppublishedid}";

	if ($result = $mysqli->query($dataQuery)) {
		$row = $result->fetch_assoc();
		$response['publishedid'] = $row['publishid'];
		$response['description'] = $row['description'];
	}

	leave(json_encode($response));



}

// Determines whether or not the user has shared rights of the object.
function getUserStorage() {

	$puserid = $_SESSION['userid'];

	global $mysqli;

	// Default values to be overriden from DB:
	$maxnetworks = 10; 
	$currentnetworks = 0;
	$cancreate = true;

	// Get our value of maximum networks from the database:
	$configQuery = "SELECT value FROM config WHERE config.key='maxnetworks'";

	if ($result = $mysqli->query($configQuery)) {
		$row = $result->fetch_assoc();
		$maxnetworks = intval($row['value']);
	}

	// Get the number of networks created by our user:

	$userQuery = "SELECT projectid FROM networks WHERE networks.owner='{$puserid}'";

	if ($result = $mysqli->query($userQuery)) {
		$currentnetworks = intval($result->num_rows);
	}

	if ($currentnetworks >= $maxnetworks) {
		$cancreate = false;
	}

	$response = json_encode(array("cancreate" => $cancreate, "maxnetworks" => $maxnetworks, "currentnetworks" => $currentnetworks));
	leave($response);

}

// Determines whether or not the user has shared rights of the object.
function isUserNetworkAdmin($puserid, $pprojectid) {

	global $mysqli;

	$query = "SELECT * FROM permissions WHERE projectid='{$pprojectid}' AND userid='{$puserid}'";
	if ($result = $mysqli->query($query)) {
		if ($result->num_rows > 0) {
			return true;
		}
	}
	return false;

}

// Determines whether or not the user has shared rights of the object.
function getSharedCount($puserid, $pprojectid) {

	global $mysqli;

	$query = "SELECT * FROM permissions WHERE projectid='{$pprojectid}' AND userid<>'{$puserid}'";
	if ($result = $mysqli->query($query)) {
		return $result->num_rows;
	}
	return 0;

}


// Determines whether or not the user has ALL editing rights of the object.
function isUserNetworkOwner($puserid, $pprojectid) {

	global $mysqli;

	$query = "SELECT * FROM networks WHERE projectid='{$pprojectid}' AND owner='{$puserid}'";

	if ($result = $mysqli->query($query)) {
		if ($result->num_rows > 0) {
			return true;
		}
	}

	return false;

}

// Determines whether or not the user has ALL editing rights of the object.
function isUserPublishedNetworkOwner($puserid, $ppublishid) {

	global $mysqli;

	$query = "SELECT * FROM published WHERE publishid='{$ppublishid}' AND owner='{$puserid}'";

	if ($result = $mysqli->query($query)) {
		if ($result->num_rows > 0) {
			return true;
		}
	}

	return false;

}

// Determines whether or not the network is visible to the public (a.k.a. published)
// @param pprojectid The ID of the project
// @param historyid Optional parameter of the specific history ID
function isNetworkInstancePublic($pprojectid, $phistoryid) {

	global $mysqli;

	$query = "";

	if (empty($phistoryid) ) {
		$query = "SELECT * FROM graphs WHERE projectid='{$pprojectid}' AND public='1' ORDER by time DESC Limit 1";
	} else {
		$query = "SELECT * FROM graphs WHERE projectid='{$pprojectid}' AND historyid='{$phistoryid}' AND public='1'";
	}

	if ($result = $mysqli->query($query)) {
		if ($result->num_rows > 0) {
			return true;
		}
	}
	return false;

}

// Determines whether or not the network is visible to the public (a.k.a. published)
// @param pprojectid The ID of the project
// @param historyid Optional parameter of the specific history ID
function getLatestNetworkInstance($pprojectid) {

	global $mysqli;

	$query = "SELECT * FROM graphs WHERE projectid='{$pprojectid}' AND public='1' ORDER by time DESC Limit 1";


	if ($result = $mysqli->query($query)) {
		$row = $result->fetch_assoc($result);
		return $row['historyid'];
	}

	return -1;

}


// Determines whether or not the network is visible to the public (a.k.a. published)
// @param pprojectid The ID of the project
// @param historyid Optional parameter of the specific history ID
function getLatestNetworkData($pprojectid) {

	global $mysqli;

	$query = "SELECT * FROM graphs WHERE projectid='{$pprojectid}' ORDER by time DESC Limit 1";

	//echo $query;


	if ($result = $mysqli->query($query)) {
		$row = $result->fetch_assoc();
		return $row['data'];
	}

	return -1;

}

// WARNING: This will wipe a network from the entire database including public and private networks including their histories.
function deleteNetworkProject($pprojectid) {

	global $mysqli;

	logger("Running deleteNetworkProject with $pprojectid");

	// Only owners of a network can perform a delete.
	if (isUserNetworkOwner($_SESSION['userid'], $pprojectid)) {

		logger("User is the network project owner, returning true.");

		// We are allowed to continue:
		$query  = "DELETE FROM esyn.graphs WHERE projectid='{$pprojectid}'";
		echo "\nrunning query: \n" . $query;
		$result = $mysqli->query($query);

		$query  = "DELETE FROM esyn.networks WHERE projectid='{$pprojectid}'";
		echo "\nrunning query: \n" . $query;
		$result = $mysqli->query($query);

		$query  = "DELETE FROM esyn.permissions WHERE projectid='{$pprojectid}'";
		echo "\nrunning query: \n" . $query;
		$result = $mysqli->query($query);

		return true;

	} else {

		logger ("User is NOT the network project owner, returning false.");

		return false;
	}

}

function logger($string) {
	echo "{$string}\n";
}

function goHome() {
	header("Location: home.php");
	die();
}



function leave($response) {
	header('Content-type: application/json');
	echo $response;
	// $mysqli->close();
	exit();

}

function leaveWithoutJSON($response) {
	// header('Content-type: application/json');
	echo $response;
	// $mysqli->close();
	exit();

}





/* close connection */


?>