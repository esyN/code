<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<?php  include("header-styles.php"); ?>
<?php 
  session_start();

  $getProjectID = $_GET['projectid'];
  $getHistoryID = $_GET['historyid'];
  $getNetworkType = $_GET['type'];
  $getPublishedID = $_GET['publishedid'];

  if(empty($getPublishedID)) {
    $getPublishedID = -1;
  }

  if (empty($getProjectID)) {
    $getProjectID = -1;
  }

  if (empty($getHistoryID)) {
    $getHistoryID = -1;
  }

  if (empty($getNetworkType)){
    $getNetworkType = 'Graph'; //default to a binary network
  }


  $arr = array(
      'projectid' => $getProjectID,
      'historyid' => $getHistoryID,
      'type' => $getNetworkType,
      'publishedid' => $getPublishedID,
      'loggedin' => isset($_SESSION['userid'])
    );

  $esynOpts = addslashes(json_encode($arr));

  $appVersion = 'src/app_'.$getNetworkType.'.js';

  //build a table of the users projects
  //this should be replaced by an action in manager.php
  $userid = $_SESSION['userid'];

  $mysqli = new mysqli("localhost", "XXXX", "XXXX", "XXXX");

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

    $table = '<table class="table table-hover table-boredered table-striped">';
    $table .= '<thead><tr>';
    $table .= '<th>Project Name</th>';
    $table .= '<th>Controls</th>';
    $table .= '</tr></thead><tbody>';
    /* fetch associative array */
    while ($row = $result->fetch_assoc()) {
      if($row["type"] == $getNetworkType){
        $table .= '<tr><td>';
        $table .= $row["label"];
        $table .= '</td>';
        $table .= '<td><button class="btn btn-success" onclick="mergeSavedProject('."'".$row[projectid]."','false'".')">Import</button></td>';
        $table .= '</tr>';
      }
    }
    $table .= '</tbody></table>';
  }
?>

<script type="text/javascript">
   var esynOpts = JSON.parse("<?php echo $esynOpts; ?>");
</script>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>esyN</title>
<!-- JQuery is now imported in header-style.php. Change back to this version if 2.1.0 causes issues -->
<!--<script src="./src/jquery-1.10.2.js"></script>-->
<!--
<script type="text/javascript" src="./src/cytoscape.js-2.0.3/cytoscape_dan.js"></script>
-->
<script type="text/javascript" src="./src/cytoscape.js-2.0.3/cytoscape.min.js"></script>

<!-- enable file download -->
<script src="src/Blob.js/Blob.js"></script>
<script src="src/FileSaver.js"></script>
<!-- file upload -->
<script src="src/jquery.csv-0.71.min.js"></script>
<!-- UI dialogs -->
<script src="src/bootstrap.min.js"></script>
<script src="src/bootbox.min.js"></script> <!-- bootbox makes bootstrap modals easier to use -->

<!-- InterMine library and search scripts -->
<script src="src/im.js"></script>
<script src="src/getInteractions.js"></script>

<!-- underscore -->
<script type="text/javascript" src="src/underscore-1.6.0-min.js"></script>

<!-- Style for page -->
<link rel="stylesheet" type="text/css" href="css/builder.css">

<script src ="analytics.js"></script>
<meta name="google-site-verification" content="wq-MzHE_599sKpmAIAZkURalSvRGOIzhDnC81xb6mtY" />


<!-- Storage -->
<?php include('include-storage.php');?>

<!-- Public projects -->
<script src="src/angular.min.js"></script>
<script src="src/controllers4_builder.js"></script>
</head>

<body id="<?php echo $getNetworkType ?>">
<!-- include the menu -->
<?php include("header-menu.php"); ?>

<div id="container">
<div id="tools">
  <!-- Nav tabs -->
  <ul class="nav nav-stacked">
  <?php
    //if it's a Model, show the menu items for coarse places
    if($getNetworkType == "PetriNet"){
      echo '<li class="active"><a href="#contains" >Contains</a></li>';
      echo '<li><a href="#isa" >Contained by</a></li>';
    }
  ?>
    <li><a href="#interactions" >Interactions</a></li>
    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Network<span class="caret"></span></a>
      <ul class="dropdown-menu">
       <!-- Create a new empty network -->
        <li><button type="button" id="new" class="btn btn-link" onclick="newnw()">New network</button></li>
          <!-- Rename current network -->
        <li><button class="btn btn-link" onclick="rename_network()">Rename Network</button></li>
          <!-- Delete current network -->
        <li><button class="btn btn-link" onclick="remove_network()">Delete Network</button></li>
        
      </ul>
    </li>

     <!-- insert a module into the current network -->
     <?php
     if($getNetworkType == "PetriNet"){
       echo '<li><button class="btn btn-link" data-toggle="modal" data-target="#moduleModal">Module</button></li>';
      }
    ?>

    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Merge<span class="caret"></span></a>
      <ul class="dropdown-menu">
       <!-- fit the window to the current network -->
        <li><button type="button" id="megamerge" class="btn btn-link" onclick="mergeAndView()">Merge all networks and view result</button></li>
        <li><button type="button" id="mergewith" class="btn btn-link" data-toggle="modal" data-target="#mergeModal">Merge with another project</button></li>
        <li><button type="button" id="mergepublic" class="btn btn-link" data-toggle="modal" data-target="#publicModal">Merge with a public project</button></li>
      </ul>
    </li>

    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">View<span class="caret"></span></a>
      <ul class="dropdown-menu">
       <!-- fit the window to the current network -->
        <li><button class="btn btn-link" onclick="cy.fit()">Reset View</button></li>
      </ul>
    </li>

    
    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Save<span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li><button onclick="save_to_server()" class="btn btn-link" id="save-online" >Save online</button></li>
        <li><button onclick="save_to_server('ignore')" class="btn btn-link" id="save-copy" >Save a copy</button></li>
        <li><button type="button" class="btn btn-link" id="saveall" onclick="save('all')">Save offline</button></li>
        <!-- Save the current network as a new project -->
        <li><button class="btn btn-link" onclick="save_network_as_project()">Save current network as new project</button></li>
      </ul>
    </li>

    <!-- if it's a diagram, show export to csv option -->

    <?php
    if ($getNetworkType == 'Graph'){
      include('include_menu_export_binary.php');
    } else {
      include('include_menu_export_petri.php');
    }
    ?>

    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Upload<span class="caret"></span></a>
      <ul class="dropdown-menu">
          <!-- Button trigger modal -->
          <li><button id="upload-btn" class="btn btn-link" data-toggle="modal" data-target="#myModal">Upload</button></li>
        
      </ul>
    </li>
    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Search<span class="caret"></span></a>
      <ul class="dropdown-menu">
          <!-- Button trigger modal -->
          <li><button id="upload-btn" class="btn btn-link" data-toggle="modal" data-target="#searchModal" onclick="setupSearch()">Search for nodes</button></li>
        
      </ul>
    </li>
  </ul>

  <select id="nwlist" class="form-control">
        <option selected="selected" value="">Select a network</option>
  </select>

  <!-- Set the filename if allowed - may be hidden by javascript -->
  <br>
  


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Select a file to upload</h4>
      </div>
      <div class="modal-body">
      <?php
      if($getNetworkType == 'PetriNet'){
        echo '<p>Select a saved esyN Petri Net to upload or a matrix exported from Snoopy to upload.</p> ';
      } else {
        echo '<p>Format:</p>
              <p>
              <input type="radio" name="upfiletype" onclick="hideDiv('."'#csv-controls'".')" value="esynp"> esyN project <br />
              <input type="radio" name="upfiletype" onclick="hideDiv('."'#csv-controls'".')" value="cyjs"> Cytoscape .cyjs file<br />
              <input type="radio" name="upfiletype" onclick="showDiv('."'#csv-controls'".')" value="csv"> csv file<br />
              <div id="csv-controls" class="collapse">
                <input type="checkbox" id="hasHeader" value=""> Header <br />
                <input type="checkbox" id="isDirected" value=""> Directed<br />
                <input type="checkbox" id="hasTypeCol" value=""> Includes edge type<br />

                <div class="row">
                  <div class="col-md-4">
                    <div id="srcColForm" class="form-group">
                      <label class="control-label" for="srcColNum">Source column</label>
                      <input type="text" class="form-control" name="srcColNum" id="srcColNum" size="5" value ="1">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div id="tgtColForm" class="form-group">
                      <label class="control-label" for="tgtColNum">Target column</label>
                      <input type="text" class="form-control" name="tgtColNum" id="tgtColNum" size="5" value ="3">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div id="typeColForm" class="form-group">
                      <label id="typeColumnLabel" class="control-label" for="typeColNum"><del>Type column</del></label>
                      <input type="text" class="form-control" name="typeColNum" id="typeColNum" size="5" value ="none" disabled="disabled">
                    </div>
                  </div>
                </div>
              </div>
              
              
              
              </p>
            ';
        //echo 'Source:<input type="text" id="up-src"> Target:<input type="text" id="up-tgt"> Type:<input type="text" id="up-type">';
      }
      ?>
        <p>Warning: Uploading a file will replace everything in the current project with the contents of the file. </p>
        
        Select a file:<input type="file" id="file_upload">
        <output id="file_list"></output>
        <div id="upload-loading"></div>
      </div>
      <div class="modal-footer">
        <button type="button" id="file-upload-btn" class="btn btn-primary" onclick="validUpload()">Upload</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Merge with existing project modal -->
<div class="modal fade" id="mergeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Select a project to import</h4>
      </div>
      <div class="modal-body">
      <?php echo $table; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end merge with existing project modal -->

<!-- Merge with public project modal -->
<div class="modal fade" id="publicModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Select a public project to import</h4>
      </div>
      <div class="modal-body" id="publicModalBody" ng-controller="ProjectListCtrl">
          

          <div class="row">
            <div class="col-md-4">Enter search term: <input ng-model="query"></div>
            <div class="col-md-8">Description: {{selecteditem.description}}</div>
          </div>
  
         <div>
          <table class="table table-hover table-boredered table-striped">
          <tr><th><a href="" ng-click="predicate = 'label'; reverse = predicate == 'label' && !reverse">Project Name</a></th>
          <th></th><th>Type</th>
          <th><a href="" ng-click="predicate = 'ownername'; reverse = predicate == 'ownername' && !reverse">Author</a></th>
          <th>Tags</th>
          <th><a href="" ng-click="predicate = 'publishdate'; reverse = predicate == 'publishdate' && !reverse">Date Published</a></th></tr>
            <tr ng-repeat="project in projects | orderBy:predicate:reverse | filter:{linktype:'<?php echo $getNetworkType ;?>'} | filter:query">
              <td>{{project.label}}</td>
              <td><button class="btn btn-success btn-xs" data-toggle="modal" data-target="#descriptionModal" <a ng-click="setSelection(project)">Show description</button>
                  <button class="btn btn-success btn-xs" ng-click="setupMergePublic(project.publishedid)">Merge</button>
              </td>
              <td><span style="white-space:nowrap;">{{project.type}}</span></td>
              <td>{{project.ownername}}</td>
              <td><ul class="taglist"><li ng-repeat="el in project.tags">{{el}}</li></ul></td>
              <td>{{project.publishdate | date:'dd/MM/yy'}}</td>
            </tr>
            
            </table>
   </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end merge with public project modal -->

<!-- Search for node modal -->
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Search for a node</h4>
      </div>
      <div class="modal-body">
      <input type="text" id="searchText" >
      <button id="searchButton" onclick="findNodeButton('text')">Search</button>
      <br /> Or select a node:
      <select id="nodelist"></select><button id="searchButton" onclick="findNodeButton('list')">Search</button>
      <div id="searchResult"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end search for node modal -->

<!-- Insert structure module modal -->
<div class="modal fade" id="moduleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Select a module in insert</h4>
      </div>
      <div class="modal-body">
          <button onclick="setupModule(152,true)">AND</button>
          <button onclick="setupModule(154,true)">NAND</button>
          <button onclick="setupModule(160,true)">NOT</button>
          <button onclick="setupModule(156,true)">OR</button>
          <button onclick="setupModule(157,true)">NOR</button>
          <button onclick="setupModule(158,true)">XOR</button>
          <button onclick="setupModule(159,true)">NXOR</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end insert structure module modal -->

<!-- Naming conventions modal -->
<div class="modal fade" id="namingModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Recommendations for node naming</h4>
      </div>
      <div class="modal-body">
      <p>Although no naming system is enforced by esyN, it is highly recommended that you use a consistent system when making a network. This will help avoid errors in your own projects, and will make
      any projects you make public much easier for others to use. We advise users to stick to the gene name (e.g. SOD1) as given by <a href="http://www.ensembl.org/" target="_blank">Ensembl</a>. This will be a 
      symbol appropriate the the organism. You can search ensembl for the name of the currently selected node by clicking "Search Ensembl" in the tool panel on the right hand side of the page. </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end naming conventions modal -->


</div> <!-- end tools section -->
  <div id="cy"></div>
  <div id="controls">
  	<div id="settings">
      <!-- Set what sort of element to add -->
      <form name='type_form'>
  <div class="toggle-btn-grp">
      <?php
      if ($getNetworkType == 'PetriNet'){
        echo '<div><input type="radio" name="types"  value="n-p" id="n-p"/><label for="n-p" onclick="" class="toggle-btn">Place</label></div>';
        echo '<div><input type="radio" name="types"  value="n-t" id="n-t"/><label for="n-t" onclick="" class="toggle-btn">Transition</label></div>';
        echo '<div><input type="radio" name="types"  value="e-n" id="e-n"/><label for="e-n" onclick="" class="toggle-btn">Normal Edge</label></div>';
        echo '<div><input type="radio" name="types" value="e-i" id="e-i"/><label for="e-i" onclick="" class="toggle-btn" >Inhibitor Edge</label></div>';
    } else if ($getNetworkType == 'Graph'){
        echo '<div><input type="radio" name="types"  value="n-p" id="n-p"/><label for="n-p" onclick="" class="toggle-btn">Node</label></div>';
        echo '<div><input type="radio" name="types"  value="e-n" id="e-n"/><label for="e-n" onclick="" class="toggle-btn">Edge</label></div>';
        echo '<div><input type="radio" name="types"  value="e-d" id="e-d"/><label for="e-d" onclick="" class="toggle-btn">Directed Edge</label></div>';
        //echo '<div><input type="radio" name="types"  value="n-t"/><label onclick="" class="toggle-btn">Nested network</label></div>';
    } else {
        echo 'invalid network type, please contact an administrator';
    }
    ?>
  </div>
  </form>
      <!-- Name the current network -->
      
      </div>
  	<div id="state">
      <!--
  	app state
      -->
  	</div>
  	<div id="info">
      <!--
  	node and edge info
      -->
  	</div>
  </div>
</div>

      <div id="aboveadvanced">
        <center>
      <form onkeypress="return event.keyCode != 13;"><font size="4" color='#F08147'>Network name:</font><input type="text" name="nw_name" id="nw_name" size="40" maxlength="25" value ="network_name" disabled="disabled"></form>
      &nbsp;    &nbsp;  &nbsp; &nbsp;
      <form id="set_filename_form" onkeypress="return event.keyCode != 13;"><font size="4" color='#F08147'> Project Name:</font><input type="text" name="set_filename" id="set_filename" size="40" maxlength="50" value ="network_json"></form>
    </center>
    </div>

<br class="clearFloat">
<br>
<!-- Advanced options -->
<div id="advanced">
  
  <h1>Advanced tools</h1>
  <div class="row1"> <!-- if Model tool, this row contains coarse place tools -->
  <?php
  if($getNetworkType == 'PetriNet'){
    include('include_advanced_row1_petri.php');
  }
  ?>
  </div>

  <div class="row2">
  <h4>Interactions</h4>
  <div class="row">
  <div class="col-md-12 fullwidth">
  <div id="interactions">
    Retrieve interactions for the selected node.<br />
  
  Organism:  
  
  <select name="organisms" id="organisms">
                      <option value="D. melanogaster">D. melanogaster</option>
                      <option value="H. sapiens">H. sapiens</option>
                      <option value="S. cerevisiae">S. cerevisiae</option>

  </select> <br />
  
  Interaction type: 
  
  <select id="interaction-type">
  <option value="any" selected="selected">Any</option>
    <option value="genetic">Genetic</option>
    <option value="physical">Physical</option>
  </select>
  <br />
  
  <br />
  <button class="btn btn-success" onclick="getInteractions()">Get interactions</button>
  <br />
  <div id="loading-container"></div>
  
  Results: <br />
  
  <div id="int-results">
  
  </div>

  </div>
  </div> <!-- end col-md-12 -->
  </div> <!-- end row 2 -- >
  </div> <!-- end row2 class -->
</div>



<!--<script type="text/javascript" src="app.js"></script>
<script type="text/javascript" src="megamerge_or.js"></script>
-->
<script type="text/javascript" src="src/app_common.js"></script>
<script type="text/javascript" src="<?php echo $appVersion ;?>"></script>

  </div>
</div>



</body>

</div>
</html>
