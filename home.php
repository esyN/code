  <?php

session_start();

$userid = $_SESSION['userid'];

if(!isset($_SESSION['userid'])){
  header("location: /login.php");
}

?>

<!DOCTYPE html>
<?php include("header-styles.php"); ?>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="assets/ico/favicon.png">

    <title>My esyN</title>

    <!-- // <script src="src/jquery-2.1.0.min.js"></script> -->
    <script src="src/bootstrap.min.js"></script>
      <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">



    <!-- Custom styles for this template -->
    <!-- <link href="css/main.css" rel="stylesheet"> -->
    <link href="css/myesyn.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="assets/css/font-awesome.min.css"> -->

  


  <!-- // <script src="assets/js/modernizr.custom.js"></script> -->
  

  
    <link href='http://fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=EB+Garamond' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
      <script src="assets/js/respond.min.js"></script>


    <![endif]-->

       <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<script src ="analytics.js"></script>
<meta name="google-site-verification" content="wq-MzHE_599sKpmAIAZkURalSvRGOIzhDnC81xb6mtY" />

  <style>
  .toSlide {
    display: none;
    font-size: 11        px;


  }
  </style>

  </head>
  <body id="myesyn">
  <?php include("header-menu.php"); ?>







<?php //do we need this block?

  $getProjectID = $_GET['projectid'];
  $getHistoryID = $_GET['historyid'];

  if (empty($getProjectID)) {
    $getProjectID = -1;
  }

  if (empty($getHistoryID)) {
    $getHistoryID = -1;
  }

  $arr = array(
      'projectid' => $getProjectID,
      'historyid' => $getHistoryID
    );

  $options = addslashes(json_encode($arr));
  //$esynOps = 
?>

<script type="text/javascript">
   var esynOps = JSON.parse("<?php echo $options; ?>");

</script>







<!-- <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
  <li class="active" data-toggle="tab"><a href="#myprojects"><span class="glyphicon glyphicon-user"></span> My Projects</a></li>
  <li data-toggle="tab"><a href="#publishedprojects"><span class="glyphicon glyphicon-globe"></span> Published Projects</a></li>
</ul>
<div id="my-tab-content" class="tab-content">
      <div class="tab-pane active" id="myprojects">
          <h1>Red</h1>
          <p>red red red red red red</p>
      </div>
      <div class="tab-pane" id="publishedprojects">
          <h1>Orange</h1>
          <p>orange orange orange orange orange</p>
      </div>
</div> -->

<div id="content">
<br />
<br />
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active"><a href="#myprojects" data-toggle="tab"><span class="glyphicon glyphicon-user"></span> My Projects</a></li>
        <li><a href="#projectssharedwithme" data-toggle="tab"><span class="glyphicon glyphicon-eye-open"></span> Projects Shared with Me</a></li>
        <li><a href="#publishedprojects" data-toggle="tab"><span class="glyphicon glyphicon-globe"></span> My Published Networks</a></li>
        <li class="tab-right"><a id="logout" href="manager.php?action=logout" ><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
    </ul>
    <div id="my-tab-content" class="tab-content">
        <div class="tab-pane active" id="myprojects">
          <h1>My Projects</h1>
          <p>These are the projects that you own. You have full access to edit, delete, share, or publish them.</p>

          <?php

          $mysqli = new mysqli("localhost", "XXX", "XXXX", "XXX");

          /* check connection */
          if (mysqli_connect_errno()) {
              printf("Connect failed: %s\n", mysqli_connect_error());
              exit();
          }


          $query4 = "
          SELECT networks.projectid, networks.type, networks.label, SubMax.time, SubMax.historyid, SubMax.public
          FROM networks
          INNER JOIN ( Select max(graphs.time) as time, projectid, public, historyid FROM graphs Group by projectid ORDER BY graphs.time DESC ) SubMax
          ON networks.projectid = SubMax.projectid WHERE networks.owner='" . $userid . "'
          ORDER BY SubMax.time DESC";


          if ($result = $mysqli->query($query4)) {



            /* Build our HTML table */

            $table = '<table class="table table-hover table-boredered table-striped">';
            $table .= '<thead><tr>';
            $table .= '<th>Project Name</th>';
            $table .= '<th>Type</th>';
            $table .= '<th>Last Edited</th>';
            $table .= '<th>Controls</th>';
            $table .= '</tr></thead><tbody>';


              /* fetch associative array */
              while ($row = $result->fetch_assoc()) {

                $date = new DateTime($row['time']);
                $now = new DateTime();

                $interval = $date->diff($now);

                $projectid = $row["projectid"];

                $historyQuery = "SELECT * FROM graphs WHERE projectid={$projectid} ORDER BY time DESC LIMIT 10 OFFSET 1";

                

                $table .= '<tr>';
                $table .= '<td><a href="builder.php?projectid=' . $row["projectid"] . '&type=' . $row["type"] .'"">' . $row["label"] . '</a>';


              if ($historyResult = $mysqli->query($historyQuery)) {

                if (intval($historyResult->num_rows) > 0) {

                    $table .= '<div class="toSlide" id="history' . $projectid . '"><ol>';

                    while ($historyRow = $historyResult->fetch_assoc()) {


                      $historydate = new DateTime($historyRow['time']);
                      $historyinterval = $historydate->diff($now);

                      if ($historyinterval->d == 1) {
                        $table .= '<li><a href="builder.php?projectid=' . $historyRow["projectid"] . '&type=' . $row["type"] . '&historyid=' . $historyRow["historyid"] . '">' .  $historydate->format('F jS \a\t g:i A T') . '  (' . $historyinterval->d . ' day ago)</a></li>';
                      } else {
                        $table .= '<li><a href="builder.php?projectid=' . $historyRow["projectid"] . '&type=' . $row["type"] . '&historyid=' . $historyRow["historyid"] .'">' . $historydate->format('F jS \a\t g:i A T') . '  (' . $historyinterval->d . ' days ago)</li>';
                      }



                    }

                    $table .= '</ol>';
                } else {
                  $table .= '<div class="toSlide" id="history' . $projectid . '">There are no previous versions of this project to display.';
                }

                }
                if($row['type'] == "PetriNet"){
                  $table .= '<td>Petri Net</td>';
                } else {
                  $table .= '<td>Graph</td>';
                }
                


$date = new DateTime($row['publishdate']);


      $tempArr['publishdate'] = $date->format('d/m/y');


                $table .= '</td>';
                //$table .= '<td>' . $row["time"] . '</td>';
                if ($interval->d == 1) {
                  $table .= '<td>' . $date->format('d/m/y') . '</td>';
                } else {
                  $table .= '<td>' . $date->format('d/m/y') . '</td>';
                }

                //escape single quotes that would break quoting the project name as a function parameter
                  $escaped =  addslashes($row["label"]);
                 $table .= '<td>

                  
                  <button class="btn btn-danger btn-xs btn-fix" onclick="setupDeletePrivate('.$projectid.",'".$escaped."'".')">Delete</button>  
                   <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal" onclick="showEditor(' . $projectid . ')">Edit Properties</button>
                   <button class="btn btn-success btn-xs" onclick="toggleHistory(' . $projectid . ')">Show History</button>



                 

                 <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#publishModal" onclick="setPublishId('.$projectid.')" >Publish</button>

                 </td> ';
                  // printf ("%s (%s)\n", $row["id"], $row["label"]);
                  $table .= '</tr>';

                  // Now build a table row for the history
              }

            $table .= '</table>';

              /* free result set */
              $result->free();
          }



          echo $table;

          /* close connection */
          $mysqli->close();

          ?>
        </div>







        <!-- !!!!!!!!!! PROJECTS SHARED WITH ME PROJECTS -->


        <div class="tab-pane" id="projectssharedwithme">
          <h1>Projects Shared With Me</h1>
          <p>These are projects that others have privately shared with you. Depending on the permissions granted to you by the owner, you may be able to edit them.</p>

          <?php

          $mysqli = new mysqli("localhost", "XXX", "XXXX", "XXX");

          /* check connection */
          if (mysqli_connect_errno()) {
              printf("Connect failed: %s\n", mysqli_connect_error());
              exit();
          }

          $query = "SELECT historyid, projectid, label, time, public, data FROM graphs ORDER by historyid ASC";

          $query2 = "
          SELECT graphs.historyid, graphs.projectid, graphs.label, graphs.time, graphs.public, graphs.data, users.groupid, users.id
          FROM graphs
          INNER JOIN users
          ON users.groupid = graphs.projectid
          WHERE users.id='joshkh@gmail.com'
          ORDER BY graphs.projectid ASC, graphs.historyid ASC";

          $query3 = "
          # Select the most recent version of all projects for a given user

          SELECT graphs.projectid, networks.type, graphs.historyid, networks.label, graphs.time, graphs.public
          FROM graphs
          INNER JOIN
          networks
          ON
          networks.projectid = graphs.projectid
          INNER JOIN
          permissions
          ON
          permissions.projectid = graphs.projectid
          INNER JOIN
          ( Select max(graphs.time) as LatestDate, projectid FROM graphs Group by projectid ) SubMax
          ON
          graphs.time = SubMax.LatestDate and graphs.projectid = SubMax.projectid
          WHERE permissions.userid='" . $userid . "' AND networks.owner<>'" . $userid . "'
          ORDER BY graphs.time DESC";


          if ($result = $mysqli->query($query3)) {



            /* Build our HTML table */

            $table = '<table class="table table-hover table-boredered table-striped">';
            $table .= '<thead><tr>';
            $table .= '<th>Project Name</th>';
            $table .= '<th>Type</th>';
            $table .= '<th>Last Edited</th>';

            $table .= '</tr></thead><tbody>';


              /* fetch associative array */
              while ($row = $result->fetch_assoc()) {

                $date = new DateTime($row['time']);
                $now = new DateTime();

                $interval = $date->diff($now);

                $projectid = $row["projectid"];

                


                $table .= '<tr>';
                // $table .= '<td><a href="../builder/network_builder/index.php?projectid=' . $row["projectid"] . '"">' . $row["label"] . '</a></td>';
                $table .= '<td><a href="builder.php?projectid=' . $row["projectid"] . '&type=' . $row["type"] .'"">' . $row["label"] . '</a></td>';
                if($row['type'] == "PetriNet"){
                  $table .= '<td>Petri Net</td>';
                } else {
                  $table .= '<td>Graph</td>';
                }
                if ($interval->d == 1) {
                  $table .= '<td>' . $date->format('d/m/y') . '</td>';
                } else {
                  $table .= '<td>' . $date->format('d/m/y') . '</td>';
                }



                  // printf ("%s (%s)\n", $row["id"], $row["label"]);
                  $table .= '</tr>';
              }

            $table .= '</table>';

              /* free result set */
              $result->free();
          }



          echo $table;

          /* close connection */
          $mysqli->close();

          ?>
        </div>

        <div class="tab-pane" id="publishedprojects">
          <h1>My Published Projects</h1>
          <p>These are projects you have made public. Anybody will be able to view these projects, and make their own copies.</p>

          <?php

          $mysqli = new mysqli("localhost", "XXX", "XXXX", "XXXX");

          /* check connection */
          if (mysqli_connect_errno()) {
              printf("Connect failed: %s\n", mysqli_connect_error());
              exit();
          }

          $userid = $_SESSION['userid'];
          $queryPublished = "SELECT label, type, publishdate, publishid FROM published WHERE owner='{$userid}' ORDER by publishdate ASC";



          if ($result = $mysqli->query($queryPublished)) {




            /* Build our HTML table */

            $table = '<table class="table table-hover table-boredered table-striped">';
            $table .= '<thead><tr>';
            $table .= '<th>Project Name</th>';
            $table .= '<th>Type</th>';
            $table .= '<th>Published Date</th>';
            $table .= '<th>Controls</th>';

            $table .= '</tr></thead><tbody>';


              /* fetch associative array */
              while ($row = $result->fetch_assoc()) {

                $date = new DateTime($row['publishdate']);
                $now = new DateTime();
                $type = $row['type'];

                $interval = $date->diff($now);

                $publishid = $row["publishid"];

                


                $table .= '<tr>';
                  // $table .= '<td><a href="../builder/network_builder/index.php?projectid=' . $row["projectid"] . '"">' . $row["label"] . '</a></td>';
                  $table .= '<td><a href="builder.php?publishedid=' . $row["publishid"] . '&type=' . $row["type"] .'"">' . $row["label"] . '</a></td>';

                  if($row['type'] == "PetriNet"){
                  $table .= '<td>Petri Net</td>';
                } else {
                  $table .= '<td>Graph</td>';
                }
                  //$table .= '<td>' . $row["time"] . '</td>';
                  if ($interval->d == 1) {
                    $table .= '<td>' . $date->format('d/m/y') . '</td>';
                  } else {
                    $table .= '<td>' . $date->format('d/m/y') . '</td>';
                  }
                  //escape single quotes that would break quoting the project name as a function parameter
                  $escaped =  addslashes($row["label"]);
                  $table .= '<td><button class="btn btn-xs btn-danger" onclick="setupDeletePublic(' . $publishid .",'".$escaped."'". ')">
        <i class="glyphicon glyphicon-trash"></i> Delete
    </button></td>';

                    // printf ("%s (%s)\n", $row["id"], $row["label"]);
                $table .= '</tr>';
              }

            $table .= '</table>';

              /* free result set */
              $result->free();
          } else {
            echo "ERROR";
          }



          echo $table;

          /* close connection */
          $mysqli->close();

          ?>
        </div>
    </div>
</div>


<!-- Confirmation Modal -->
<!-- Modal Dialog delete private -->
<div class="modal fade" id="confirmDelete" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Delete permanently</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the following project?</p>
        <div id="privateProjectName"></div>
        <form>
        <input name="privateid" id="deletePrivateId" type="hidden" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm" onclick="deletePrivate()">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Dialog -->
<div class="modal fade" id="confirmDeletePublic" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Delete permanently</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the following published project?</p>
        <div id="publicProjectName"></div>

        <form>
        <input name="publicid" id="deletePublicId" type="hidden" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm" onclick="deletePublic()">Delete</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Edit Network Properties</h4>
      </div>
      <div class="modal-body" id="editForm">






<input id="textprojectid" name="textlabel" type="hidden" value="-1">


<!-- Text input-->
<div class="control-group">
  <label class="control-label" for="textlabel">Project Name</label>
  <div class="controls">
    <input id="textlabel" name="textlabel" type="text" placeholder="" class="input-xlarge">
    
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="textlabel">Owner</label>
  <div class="controls">
    <input id="textowner" name="textlabel" type="text" placeholder="" class="input-xlarge" disabled>
    
  </div>
</div>


<!-- Select VIEWERS -->
<label class="control-label" for="selectviewers">Viewers</label>
<div class="input-group">
      <input type="text" class="form-control" id="textaddviewer">
      <span class="input-group-btn">
        <button class="btn btn-default" type="button" id="buttonaddviewer">Add</button>
      </span>
</div><!-- /input-group -->

<div class="control-group">
  
  <div class="controls">
    <select id="selectviewers" name="selectviewers" class="input-large fullwidth" multiple="multiple">
      <option>Option one</option>
      <option>Option two</option>
    </select>
  </div>
</div>

<div class="control-group">
  <button id="buttonremoveviewers" class="btn btn-danger">Remove Viewer(s)</button>
</div>



<label class="control-label" for="selecteditors">Editors</label>
<div class="input-group">
      <input type="text" class="form-control" id="textaddeditor">
      <span class="input-group-btn">
        <button class="btn btn-default" type="button" id="buttonaddeditor">Add</button>
      </span>
</div><!-- /input-group -->



<!-- Select EDITORS -->
<div class="control-group">

  <div class="controls">
    <select id="selecteditors" name="selecteditors" class="input-large fullwidth" multiple="multiple">
      <option>Option one</option>
      <option>Option two</option>
    </select>
  </div>
</div>

<div class="control-group">
  <button id="buttonremoveeditors" class="btn btn-danger">Remove Editor(s)</button>
</div>



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveChanges" onclick="saveEditorValues()">Save changes</button>
      </div>
    </div>
  </div>
</div> <!-- end of edit properties modal -->


<!--     <button class="btn btn-xs btn-danger" type="button" id="confirmDelete" data-toggle="modal" data-target="#confirmDelete" data-title="Delete User" data-message="Are you sure you want to delete this user ?">
        <i class="glyphicon glyphicon-trash"></i> Delete
    </button> -->

<!-- publish modal -->
<div class="modal fade" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Publish</h4>
      </div>
      <div class="modal-body" id="publishForm">


        <input id="textPublishId" name="textlabel" type="hidden" value="-1">


        <!-- Text input-->
        <div class="control-group">
          <label class="control-label" for="textOwnerName">Username (required, 50 characters max)</label>
          <div class="controls">
              <textarea id="textOwnerName" name="textOwnerName" class="form-control" maxlength="50"></textarea>            
          </div>
        </div>

        <div class="control-group">
          <label class="control-label" for="textDescription">Project Description (optional, 1200 characters max)</label>
          <div class="controls">
              <textarea id="textDescription" name="textDescription" class="form-control" maxlength="1200" rows="3"></textarea>            
          </div>
        </div>

        <div class="control-group">
          <label class="control-label" for="textlabel">Tags (optional, 5 max, 20 characters each)</label>
          <div class="controls">
            <input id="tag0" name="[textlabel]" type="text" placeholder="" class="input-xlarge">
            <input id="tag1" name="[textlabel]" type="text" placeholder="" class="input-xlarge">
            <input id="tag2" name="[textlabel]" type="text" placeholder="" class="input-xlarge">
            <input id="tag3" name="[textlabel]" type="text" placeholder="" class="input-xlarge">
            <input id="tag4" name="[textlabel]" type="text" placeholder="" class="input-xlarge">

            
          </div>
        </div>




      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="publishProject" onclick="publish()" >Publish</button>
      </div>
    </div>
  </div>
</div> 

<!-- end publish modal -->

<script type="text/javascript">

 $('#confirmDelete').confirmation('show');

    jQuery(document).ready(function ($) {
        $('#tabs').tab();
    });
</script>


  <script>
// Execute navigator.id.logout(); when the user clicks "Sign Out"

$("#logout").click(function() {
  navigator.id.logout();
});


$("#buttonaddeditor").click(function() {

  $("#selecteditors").append('<option value="' + $('#textaddeditor').val() + '">' + $('#textaddeditor').val() + '</option>');
  $('#textaddeditor').val(null);
  $('#textaddeditor').focus();
});


$("#buttonaddviewer").click(function() {



  $("#selectviewers").append('<option value="' + $('#textaddviewer').val() + '">' + $('#textaddviewer').val() + '</option>');
  $('#textaddviewer').val(null);
  $('#textaddviewer').focus();


});  


$("#buttonremoveviewers").click(function() {
  $("#selectviewers").find('option:selected').remove();
});

$("#buttonremoveeditors").click(function() {
  $("#selecteditors").find('option:selected').remove();
});






  function showEditor(val) {

   $.ajax({
      type: "POST",
      url: "manager.php",
      data: { action: "describe", projectid: val}
    })
      .done(function( msg ) {

        // Set up our network LABEL:
        $("#textlabel").val(msg.label);

        $("#textprojectid").val(msg.projectid);

        $("#textowner").val(msg.owner);

        // Populate our VIEWERS list:
        var options = "";

        for (var i = 0; i < msg.viewers.length; i++) {
          options += '<option value="' + msg.viewers[i] + '">' + msg.viewers[i] + '</option>';
        }
        $("#selectviewers").html(options);

        // Populate our EDITORS list:
        // var options = '<option value="' + msg.owner + '">' + msg.owner + '</option>';
        var options = '';

        for (var i = 0; i < msg.editors.length; i++) {
          options += '<option value="' + msg.editors[i] + '">' + msg.editors[i] + '</option>';
        }
        $("#selecteditors").html(options);

        console.log(JSON.stringify(msg, null, 2));


        //alert( "Data Saved: " + msg.label );
    });


    //      alert(val);
  }


function setPublishId(val){
  $("#textPublishId").val(val);
}




  function saveEditorValues() {

    console.log("saving...");

    var viewers = new Array();
    var editors = new Array();
    var projectid = $("#textprojectid").val();
    var label = $("#textlabel").val();

    $("#selectviewers option").each(function() {
        viewers.push($(this).val());
    });

    var editors = new Array();

    $("#selecteditors option").each(function() {
        editors.push($(this).val());
    });

    var jsonViewers = JSON.stringify(viewers);
    var jsonEditors = JSON.stringify(editors);


     $.ajax({
      type: "POST",
      url: "manager.php",
      data: { action: "set", projectid: projectid, viewers: jsonViewers, editors: jsonEditors, label: label}
    })
      .done(function( msg ) {

      //window.location.href = "home.php"; //i think this was supposed to refresh the page, but it doesn't work
    location.reload();

    }).fail(function(xhr, status){console.log("edit.fail: ", xhr, status)});

  }

  function toggleHistory(projectid) {

      if ( $("#history" + projectid).is( ":hidden" ) ) {
        $("#history" + projectid).slideDown( "slow" );
      } else {
        $("#history" + projectid).slideUp();
      }

  }


function publish() {

  console.log("saving...");

  var projectid = $("#textPublishId").val();
  var description = $("#textDescription").val();
  var tags = new Array();
  var ownername = $('#textOwnerName').val();

  $('input[name="[textlabel]"]').each(function() {
        tags.push($(this).val());
    });


  console.log('saving id:' + projectid + ' with description: ' + description + ' and tags: ' + tags)

  $.ajax({
    type: "POST",
    url: "manager.php",
    data: { action: "publish", projectid: projectid, description: description, tags: tags, ownername: ownername}

  }).done(function() {
      console.log("message");

    //window.location.href = "home.php"; //i think this was supposed to refresh the page, but it doesn't work
    console.log('done, reloading');
    location.reload();

  }).fail(function(xhr, status){console.log("publish.fail: ", xhr, status)});

}

function deletePublished(publishid) {

  console.log("deleting...", publishid);




   $.ajax({
    type: "POST",
    url: "manager.php",
    data: { action: "deletePublished", publishid: publishid}
  })
    .done(function( msg ) {
      //console.log("message", msg);

    //window.location.href = "home.php"; //i think this was supposed to refresh the page, but it doesn't work
    location.reload();


  });

}

//new functions to delete public and private projects with a confirmation popup
//both work by setting values in a hidden form in a modal

function setupDeletePublic(id,name){
  $('#deletePublicId').prop('value',id)
  $('#publicProjectName').text(name)
  $('#confirmDeletePublic').modal('show')
}

function deletePublic(){
  var publishid = $('#deletePublicId').val()
   $.ajax({
    type: "POST",
    url: "manager.php",
    data: { action: "deletePublished", publishid: publishid}
  })
    .done(function( msg ) {
      //console.log("message", msg);

    //window.location.href = "home.php"; //i think this was supposed to refresh the page, but it doesn't work
    location.reload();


  });
}

function setupDeletePrivate(id,name){
  $('#deletePrivateId').prop('value',id)
  $('#privateProjectName').text(name)
  $('#confirmDelete').modal('show')
}

function deletePrivate(){
var projectid = $('#deletePrivateId').val()

$.ajax({
    type: "POST",
    url: "manager.php",
    data: { action: "delete", projectid: projectid}
  })
    .done(function( msg ) {
      //console.log("message", msg);

    //window.location.href = "home.php"; //i think this was supposed to refresh the page, but it doesn't work
    location.reload();


  });
}

  </script>











  </body>
</html>