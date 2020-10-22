<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<?php  include("header-styles.php"); ?>


<?php
  session_start();

  $getProjectID = $_GET['projectid'];
  $getHistoryID = $_GET['historyid'];
  $getNetworkType = $_GET['type'];
  $getPublishedID = $_GET['publishedid'];
  $getQuery = $_GET['query'];
  $getOrganism = $_GET['organism'];
  $getSource = $_GET['source'];
  $getIntType = $_GET['interactionType'];
  $getGoTerm = $_GET['term'];
  $getIncludeInteractors = $_GET['includeInteractors'];
  $getColourSource = $_GET['colourSource'];
  $getColourCommInteract = $_GET['colourCommInteract'];
  $getInterInter = $_GET['interInter'];
  $getLayout = $_GET['layout'];
  $getIdentiferType = $_GET['identifierType'];
  $getThroughput = $_GET['throughput'];
  $getWhere = $_GET['where'];
  $getRoot = $_GET['root'];

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

  if (empty($getQuery)){
    $getQuery = -1; //don't populate the graph initially
  }

  if (empty($getOrganism)){
    $getOrganism = -1;
  }

  if (empty($getSource)){
    $getSource = -1;
  }

  if (empty($getIntType)){
    $getIntType = -1;
  }

  if (empty($getGoTerm)){
    $getGoTerm = -1;
  }

  if (empty($getIncludeInteractors)) {
    $getIncludeInteractors = true; //for compatibility with the original spec given to pombase
  }

  if (empty($getColourSource)) {
    $getColourSource = "true"; //default are coloured
  }

  if (empty($getColourCommInteract)) {
    $getColourCommInteract = "false"; //default are coloured
  }

  if (empty($getInterInter)) {
    $getInterInter = "false"; //default are coloured
  }

  if (empty($getLayout)) {
    $getLayout = "ForceDirected"; //this is useless - the layout is fixed in the getInteractions.js file
  }

  if (empty($getIdentiferType)){
    //for flymine to work this HAS to default to ID. If that's a problem later, add code HERE to default to ID if source is intermine, organism is 7227 and identifierType was not set
    $getIdentiferType = "id"; //by default assume searches are going to use IDs not symbols
  }

  if (empty($getThroughput)){
    $getThroughput = 'any'; //don't restrict by default
  }

  if (empty($getRoot)){
    $getRoot = -1;
  }

  if (empty($getWhere)){
    $getWhere = -1;
  }

  $arr = array(
      'projectid' => $getProjectID,
      'historyid' => $getHistoryID,
      'type' => $getNetworkType,
      'publishedid' => $getPublishedID,
      'loggedin' => isset($_SESSION['userid']),
      'query' => $getQuery,
      'organism' => $getOrganism,
      'source' => $getSource,
      'interactionType' => $getIntType,
      'term' => $getGoTerm,
      'includeInteractors' => $getIncludeInteractors,
      'colourSource' => $getColourSource,
      'colourCommInteract' => $getColourCommInteract,
      'interInter' => $getInterInter,
      'layout' => $getLayout,
      'identifierType' => $getIdentiferType,
      'throughput' => $getThroughput,
      'root' => $getRoot,
      'where' => $getWhere
    );

  $esynOpts = addslashes(json_encode($arr));

  $appVersion = 'src/app_'.$getNetworkType.'.js';
?>

<script type="text/javascript">
   var esynOpts = JSON.parse("<?php echo $esynOpts; ?>");
</script>


<title>esyN</title>

<!-- cytoscape -->
<?php
if ($getNetworkType == 'DecisionTree'){
  echo '<script type="text/javascript" src="./src/cytoscape.js-3.2.22/cytoscape.min.js"></script>';
  // dagre layout
  echo '<script src="https://unpkg.com/dagre@0.7.4/dist/dagre.js"></script>';
  echo '<script type="text/javascript" src="./src/cytoscape.js-dagre-2.2.1/cytoscape-dagre.js"></script>';

  //klay layout
  echo '<script src="https://unpkg.com/klayjs@0.4.1/klay.js"></script>';
  echo '<script type="text/javascript" src="./src/cytoscape.js-klay-3.1.2/cytoscape-klay.js"></script>';

  //<!-- user-defined functions -->
  echo '<script type="text/javascript" src="src/filtrex.js"></script>';

} else {
  //graphs and petrinets use an older library
  echo '<script type="text/javascript" src="./src/cytoscape.js-2.4.4/build/cytoscape.min.js"></script>';
  echo '<script type="text/javascript" src="./src/cytoscape.js-2.4.4/lib/cola.v3.min.js"></script>';

  echo '<link href="src/cytoscape.js-navigator/cytoscape.js-navigator.css" rel="stylesheet" type="text/css" />';
  echo '<script src="src/cytoscape.js-navigator/cytoscape.js-navigator.js"></script>';

}
?>



<!-- enable file download -->
<script src="src/Blob.js/Blob.js"></script>
<script src="src/FileSaver.js"></script>
<script src="src/download.js"></script>
<!-- file upload -->
<script src="src/jquery.csv-0.71.min.js"></script>
<!-- UI dialogs -->
<script src="src/bootstrap.min.js"></script>
<script src="src/bootbox.min.js"></script> <!-- bootbox makes bootstrap modals easier to use -->

<!-- InterMine library and search scripts -->
<script src="src/im.min.js"></script>
<script src="src/getInteractions.js"></script>
<script src="src/getCentralities.js"></script>
<script src="src/fillNetworks.js"></script>
<script type="text/javascript" src="src/sbml/SBMLJSON.js"></script>
<script type="text/javascript" src="src/sbml/parseSBML.js"></script>


<!-- underscore -->
<script type="text/javascript" src="src/underscore-1.6.0-min.js"></script>

<!-- Style for page -->
<link rel="stylesheet" type="text/css" href="css/builder.css">
<link rel="stylesheet" type="text/css" href="src/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">

<!-- slide toggle button -->
<script type="text/javascript" src="src/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>



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
  <div id="top">
<div id="input-mode-align" class="input-mode-align">
<div id="input-mode-select" class="input-mode-select">

  <form name='type_form' class="type_form">
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
    } else if ($getNetworkType == 'DecisionTree'){
        echo '<div><input type="radio" name="types"  value="n-p" id="n-p"/><label for="n-p" onclick="" class="toggle-btn">Node</label></div>';
        echo '<div><input type="radio" name="types"  value="e-d" id="e-d"/><label for="e-d" onclick="" class="toggle-btn">Directed Edge</label></div>';
        //echo '<div><input type="radio" name="types"  value="n-t"/><label onclick="" class="toggle-btn">Nested network</label></div>';
    } else {
        echo 'invalid network type, please contact an administrator';
    }
    ?>
  </div>
  </form>
</div>
</div>
</div>

<div id="tools">
  <!-- Nav tabs -->
  <div class="likemenu">Create: <input id="editToggle" type="checkbox" name="my-checkbox" data-size="mini" checked></div>
  <ul class="nav nav-stacked">

    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Project<span class="caret"></span></a>
      <ul class="dropdown-menu">
       <!-- Create a new empty network -->
        <li><button type="button" id="new" class="btn btn-link" onclick="newnw()">New network</button></li>

          <!-- Rename current network -->
        <li><button class="btn btn-link" id="btn-rename-nw" onclick="rename_network()">Rename Network</button></li>

        <!-- fit the window to the current network -->
        <li><button class="btn btn-link" onclick="cy.fit()">Reset View</button></li>


        <!-- merge all networks -->
        <?php
        if ($getNetworkType != 'DecisionTree'){
          include('include_menu_merge.php');
        }
        ?>


        <!-- add a separator before the delete button -->
        <li role="presentation" class="divider"></li>

          <!-- Delete current network -->
        <li><button id="btn-delete-nw" class="btn btn-link" onclick="remove_network()">Delete Network</button></li>

      </ul>
    </li>
      <!-- layouts -->
      <li class="dropdown">
      <button class="dropdown-toggle btn btn-link" data-toggle="dropdown">Layouts<span class="caret"></span></button>

          <?php
          if ($getNetworkType == 'DecisionTree'){
            include('include_menu_layout_decisiontree.php');
          } else {
            include('include_menu_layout_others.php');
          }

          ?>

    </li>

    <!-- centralities -->
<!-- analyses -->

    <li class="dropdown">
        <button class="dropdown-toggle btn btn-link" data-toggle="dropdown">Analyses<span class="caret"></span></button>
        <?php
        if ($getNetworkType == 'DecisionTree'){
          include('include_menu_analysis_decisiontree.php');
        } else {
          include('include_menu_analysis_other.php');
        }

        ?>
    </li>



     <!-- insert a module into the current network -->
     <?php
     if($getNetworkType == "PetriNet"){
       echo '<li><button class="btn btn-link" data-toggle="modal" data-target="#moduleModal">Module</button></li>';
      }
    ?>

    <!-- generate a network from a list of genes if it's a graph -->
    <?php
    if($getNetworkType == "Graph"){
      echo '<li><button type="button" id="generate" class="btn btn-link" data-toggle="modal" data-target="#generateModal">Network from list</button></li>';
    }
    ?>


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
    } else if($getNetworkType == 'DecisionTree') {
      include('include_menu_export_decisiontree.php');
    } else {
      include('include_menu_export_petri.php');
    }
    ?>

    <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Upload network<span class="caret"></span></a>
      <ul class="dropdown-menu">
          <!-- Button trigger modal -->
          <li><button id="upload-btn" class="btn btn-link" data-toggle="modal" data-target="#myModal">Upload network</button></li>
      </ul>
    </li>

    <!-- Search Button trigger modal -->
    <li><button id="search-btn" class="btn btn-link" data-toggle="modal" data-target="#searchModal" onclick="setupSearch()">Search</button></li>

  </ul>

  <select id="nwlist" class="form-control">
        <option selected="selected" value="">Select a network</option>
  </select>

<div id="overview"></div>

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
        echo '<p>Compatible file types: <ul><li>A saved esyN Petri Net</li><li>An SBML model (e.g. from BioModels Database - <a href="./tutorial.html#docs#sbml" target="_blank">click here for details</a>)</li><li>A matrix exported from Snoopy</li></ul></p> ';
      } else if($getNetworkType == 'DecisionTree'){
        echo '<p>Compatible formats:</p>
              <ul><li>Project file exported from esyN</li></ul>
            ';
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
        <div id="user-project-list">
      Project list loading...
        </div>
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

<!-- FILL NETWORK - info -  modal -->
<div class="modal fade" id="fillinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Fill the network with all the missing edges</h4>
      </div>
      <div class="modal-body">
      <p>Add all the missing edges between the nodes in your network! </p>
        <p> If you have built your network using a database you can add interactions from another database (e.g. H. sapiens (Biogrid)
          and H. sapiens (DrugBank). </p>
          <p>
          If you have added nodes in your network - you can make sure all interactions between the new nodes and the rest of the network
          have been added.
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end FILL NETWORK - info -  modal -->

<!-- Node Hierarchy - info -  modal -->
<div class="modal fade" id="hierarchyinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Node Hierarchy - Parent and Child Places</h4>
      </div>
      <div class="modal-body">
      <p> A coarse (or parent) place is different from a normal place in that it contains other places (its children) rather than tokens.
        It is a placeholder representing the idea that the edges connected to it actually apply to a number of other places
        (the places it contains). Coarse places can be considered as "classes" of place nodes, which represent a general category of
        place rather than a specific entity (and therefore they don't require tokens). For example, the coarse place "cities" may contain
        "Paris" and "London", or the coarse place "Kinase" would contain "Protein kinase A", "Protein kinase B".
        Any edges that apply the the coarse place will be inherited by the places it contains.</p>
         <p>
         Coarse places are represented by orange nodes.
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Node Hierarchy - info -  modal -->


<!-- Centralities - info -  modal -->
<div class="modal fade" id="centralityinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Find the Most Central Nodes in the Network</h4>
      </div>
      <div class="modal-body">
      <p>Discover which nodes are the most central in your network. By default we are treating the network as made of indirected edges
        and we are calculating the betweenness centrality for each node, which is
        equal to the total number of shortest paths that pass though that node. </p>
        <p> You can change these settings by
        selecting the "Advanced Options", where it is possible to choose to treat the network as made of Directed, Undirected
        or Mixed edges and calculate the Degree, the Closeness, the Betweenness, the Eccentricity, the Radiality, the Stress or the Centroid Value Centrality.
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Centralities - info -  modal -->


<!-- Degree - info -  modal -->
<div class="modal fade" id="degreeinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Degree of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>
       The Degree is the conceptually simplest centrality measure. It measures the number of edges attached to a node. If the network has directed
       adges then the degree can be separated into in-degree (incoming edges) and out-degree (outgoing edges).
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Degree - info -  modal -->


<!-- Closeness - info -  modal -->
<div class="modal fade" id="closenessinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Closeness Centrality ?</h4>
      </div>
      <div class="modal-body">
      <p>
       The Closeness centrality counts how far is each node from all the orthers. The distance between any two nodes is set to be 1.
       The value of the closeness for one selected node v is the reciprocal of the sum of all shortest distances connecting
       this node with all other nodes in the network. A node with a high value compared to the average closeness has the shorter
       distances to other network components then most of the other nodes and is therefore considered to be closer to the other
       network components.
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end closeness - info -  modal -->

<!-- Betweenness - info -  modal -->
<div class="modal fade" id="betweennessinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Betweenness Centrality ?</h4>
      </div>
      <div class="modal-body">
      <p>
        The betweenness of a node v is obtained by counting the number of all shortest paths, connecting any pair of nodes within the network,
        which are going through that particular node v. The value is divided by the number of all shortest paths connecting two nodes.
        A node with a high betweenness compared to the average has an increased number of shortest paths going through, and therefore even if
        it does not have a high degree or a high closeness its a key node in the network.

      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end betweenness- info -  modal -->

<!-- Eccentricity - info -  modal -->
<div class="modal fade" id="eccentricityinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Eccentricity of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>
        The eccentricity of each individual node is the reciprocal of the longest shortest path connecting
        the node with all other components of the network. A node with a high value for eccentricity compared
        to the average has shorter distances to the other nodes and is therefore considered to be central in the graph.

      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Eccentricity- info -  modal -->

<!-- Radiality - info -  modal -->
<div class="modal fade" id="radialityinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Radiality of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>
        The radiality of a node is the sum of the maximum shortest distance overall the network (i.e. the diameter) plus one minus the
        distance to all other nodes. This value is divided by the number of nodes minus one.
        A high radiality compared to the average is pointing out a close distance to a high number
         of other network components.

      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Radiality- info -  modal -->

<!-- Stress - info -  modal -->
<div class="modal fade" id="stressinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Stress of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>
        The stress of a node v is obtained by counting the number of all shortest path connecting any node couple in the networks,
        which are going through that particular node v.  A node with a high value of stress compared to the average value
        has an increased number of paths passing through, which makes this node more important for the connectivity of the network.

      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Stress- info -  modal -->

<!-- Centroid - info -  modal -->
<div class="modal fade" id="centroidinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Centroid Value of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>

        The centroid value of a node <i>v</i> is obtained by checking, for each node pair <i>v, w </i> which other nodes are closer to <i>v</i> rather than to <i>w</i>.
        The centroid value of <i>v</i> is therefore, the minimum such value for all node pairs involving <i>v</i>.



      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Centroid- info -  modal

<!-- Collective Influence - info -  modal -->
<div class="modal fade" id="collInfluenceinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">What is the Collective Influence of a Node ?</h4>
      </div>
      <div class="modal-body">
      <p>

        The Collective Influence is the product of a node reduced degree (number of links minus one) times the sum of the reduced degree of the
        nodes that are a certain number of steps away (the radius). See: Morone, F. &#38; Makse, H. A. Nature 524, 65-68 (2015).



      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Collective Influence- info -  modal -->

<!-- Disruption - info -  modal -->
<div class="modal fade" id="disruptioninfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Disruption of the Network</h4>
      </div>
      <div class="modal-body">
      <p>Discover what happens to the centrality measures when one or more nodes are removed from the network. </p>
        <p>If you have calculated one of them previously you can repeat the calculation and view the differences. </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Centralities - info -  modal -->


<!-- Paths - info -  modal -->
<div class="modal fade" id="pathinfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="namingModalLabel">Find the Shortest Path Between Two Nodes</h4>
      </div>
      <div class="modal-body">
      <p>Discover the shortest path between any two nodes. By default we are treating the network as made of indirected edges
        and we are calculating the shortest path between the source and the target. </p>
        <p>You can change these settings by
        selecting the "Advanced Options", where it is possible to choose to treat the network as made of Directed, Undirected
        or Mixed edges and calculate the shortest path between the source and the target and vice-versa.
      </p>

      <p></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end Paths - info -  modal -->


<!-- Generate network from list modal -->
<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Generate a network from a list of genes</h4>
      </div>

      <div class="modal-body">
        <p>Enter a comma-separated list of genes or upload a list of genes. Uploaded files must be .txt format with genes as a single column. Note this might take a moment for very large lists. </p>

        <p> Enter a list: </p>
        <input type="text" id="gen_list_text" placeholder="genes separated by commas"> <br />
        <p> Or select a file: </p>
        <input type="file" id="gen_file_upload">
        <output id="gen_file_list"></output>
        <br />

        Organism:
        <select name="org_generate_nw" id="org_generate_nw">
            <option value="3702|intermine">A. thaliana (ThaleMine)</option>
            <option value="3702|biogrid">A. thaliana (BioGRID)</option>
            <option value="6239|biogrid">C. elegans (BioGRID)</option>
            <option value="7227|biogrid">D. melanogaster (BioGRID)</option>
            <option value="7227|intermine">D. melanogaster (FlyMine)</option>
            <option value="7227|flybase">D. melanogaster (FlyBase)</option>
            <option value="7955|biogrid">D. rerio (BioGRID)</option>
            <option value="511145|biogrid">E. coli (BioGRID)</option>
            <option value="9606|biogrid">H. sapiens (BioGRID)</option>
            <option value="9606|intermine">H. sapiens (HumanMine)</option>
            <option value="10090|biogrid">M. musculus (BioGRID)</option>
            <option value="10090|intermine">M. musculus (MouseMIne)</option>
            <option value="10116|biogrid">R. norvegicus (BioGRID)</option>
            <option value="559292|biogrid">S. cerevisiae (BioGRID)</option>
            <option value="559292|intermine">S. cerevisiae (YeastMine)</option>
            <option value="4896|biogrid">S. pombe (BioGRID)</option>
            <option value="pombase|pombase">S. pombe (PomBase curated)</option>
        </select>
        <br />

        Interaction type:
         <select id="gen_int_type">
          <option value="any" selected="selected">Genetic and Physical</option>
            <option value="genetic">Genetic</option>
            <option value="physical">Physical</option>
          </select>
          <br />

        Throughput (BioGRID only):
        <select id="gen_int_throughput">
          <option value="any" selected="selected">High and Low</option>
            <option value="high">High</option>
            <option value="low">Low</option>
          </select>

          <div id="gen_selIntType" style="display:none;" class="bg-warning">
          Identifier type (required):
          <select id="gen_selectIdentifierType">
          <option value="id" selected="selected">ID</option>
          <option value="symbol">Symbol</option>
          </select>
          </div>

          <div class="checkbox">
          <label>
            <input type="checkbox" id="gen_incl_interactors"> Include interactors of these genes?
          </label>
        </div>
        <center><div class="alert-danger" role="alert">All the contents of the current project will be replaced with this network.</div></center>
      </div>

      <div class="modal-footer">
        <button type="button" id="generate-nw-btn" class="btn btn-primary" onclick="setupNetworkGenerator()">Generate</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end generate network from list modal -->

<!-- edge details modal -->
<div class="modal fade" id="edgeDetailsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Edge Details</h4>
      </div>
      <div class="modal-body">
          <div id='edgeDetailsTxt'>

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end edge details modal -->

<!-- embed link modal -->
<div class="modal fade" id="embedLinkModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Embed this project</h4>
      </div>
      <div class="modal-body">
          <div>
          <div id="embedLinkDescription">
            <p> Copy and paste the HTML code below to embed this project in another website. For more details
          on embedding esyN projects, check out the "embed" tab in the <a href="./tutorial.html">documentation</a>.
           </p>
          </div>

           <div id='embedLinkTxt'>
             <!-- div populated by generateEmbedLinkTxt() -->
           </div>

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end embed link modal -->

<!-- node and edge key modal -->
<div class="modal fade" id="keyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Node and edge key</h4>
      </div>
      <div class="modal-body">
          <div id='keyTxt'>
          <p>
          <?php
            if($getNetworkType == 'PetriNet'){
              include('key-petri.php');
            } else {
              include('key-graph.php');
            }
          ?>

          </p>

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end node and edge key modal -->

<!-- generic alert modal modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-body">
          <div id='modalPopupTxt'>

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end edge details modal -->

<!-- saving centralities modal -->

<div class="modal fade modal-open" id="printCentralities" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <center><h3 class="modal-title">
    <div id = "centralityToCalculate"></div>
    </h3></center>
    </div>

    <div class="modal-body My-modal-body" align="center">
        <!-- <button type="button"  class="btn btn-primary" onclick="saveCentralities()">Download Results</button> -->
        <p></p>
            <style>
        table{
        width: 250px;
        }
        th{width: 125px;
        text-align: center;}
        td{}
      </style>
            <div id="resultCentrality"></div>

      </div>
      <div class="modal-footer">
      <button type="button"  class="btn btn-primary" onclick="saveCentralities()">Download Results</button>
       <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>


    </div>
  </div>
</div>


<!-- end saving centralities -->




<!-- printing shortest path modal -->

<div class="modal fade" id="printPath" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <center><h3 class="modal-title">Results of pathway analysis</h3></center>
      </div>
      <div class="modal-body" align="center">
        <p></p>
            <div id="resultPath"></div>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- end printing shortest path -->


<!-- saving centralities modal for interrupted nw-->

<div class="modal fade" id="printCentralitiesDisrupted" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <center><h3 class="modal-title">
    Centralities of a Disrupted Network
    </h3></center>
    </div>

    <div class="modal-body My-modal-body" align="center">

        <p></p>
            <style>
        table{width: 250px;}
        th{width: 125px; text-align: center;}
        td{}
      </style>
            <div id="resultCentralityInterrupted" ></div>

      </div>

      <div class="modal-footer">
    <button type="button"  class="btn btn-primary" onclick="saveCentralitiesInterruted()">Download Results</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
  </div>
  </div>
</div>

<!-- end saving centralities for interrupted nw -->

<!-- printing network parameter modal -->

<div class="modal fade" id="printNwParameter" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <center><h3 class="modal-title">Network Parameters</h3></center>
      </div>
      <div class="modal-body My-modal-body" align="left">

        <p></p>
            <div id="nwParameter"></div>
      </div>
          <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
    </div>
  </div>
</div>


<!-- end printing network parameter -->


</div> <!-- end tools section -->
  <div id="cy">


  </div>
  <div id="controls">
  	<div id="settings">
      <!-- Set what sort of element to add -->

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
      <!-- welcome message -->
      <?php
      if($getNetworkType == 'DecisionTree'){
        include('include_info_pane_dt.php');
      } else {
        echo('
        <p>Welcome to esyN!</p>
        <p>Click in the blue area to create/ edit nodes and edges. </p>

        <p> Select a layout from the layouts tab on the left. </p>
        <p> Interactions for the selected node can be retrieved at the bottom of the page.
         </p>
        ');
      }
      ?>

  	</div>
  </div>
</div>

      <div id="aboveadvanced">
        <center>
      <form onkeypress="return event.keyCode != 13;"><font size="4" color='#F08147'>Network name:</font><input type="text" name="nw_name" id="nw_name" size="40" maxlength="25" value ="network_name" disabled="disabled"></form>
      &nbsp;    &nbsp;  &nbsp; &nbsp;
      <form id="set_filename_form" onkeypress="return event.keyCode != 13;"><font size="4" color='#F08147'> Project Name:</font><input type="text" name="set_filename" id="set_filename" size="35" maxlength="50" value ="network_json"></form>
      <br />
      <button class="btn btn-primary" onclick="setupEmbedLinkTxt()">Generate embed link</button>
      <button class="btn btn-primary" data-toggle="modal" data-target="#keyModal">Node and edge key</button>
    </center>
    </div>


<br class="clearFloat">
<br>
<!-- Advanced options -->
<div id="advanced">

  <h1>Advanced tools</h1>

<ul class="nav nav-tabs">




    <?php
    if($getNetworkType == 'DecisionTree'){
      echo('<li class="active"><a href="#run_model" data-toggle="tab">Run model</a></li>');
      echo('<li><a href="#batch" data-toggle="tab">Batch</a></li>');
      echo('<li><a href="#variable_editor" data-toggle="tab">Variable Editor</a></li>');
      echo('<li><a href="#rule_editor" data-toggle="tab">Calculators and Rules</a></li>');
      echo('<li><a href="#findpath" data-toggle="tab">Find a Path</a></li>');
      echo('<li><a href="#description_editor" data-toggle="tab">Description</a></li>');
      echo('<li><a href="#tests" data-toggle="tab">Check Model</a></li>');
    } else {
      echo('<li class="active"><a href="#interactions" data-toggle="tab">Interactions</a></li>');
      echo('<li><a href="#netstat" data-toggle="tab">Network Statistics</a></li>');
      echo('<li><a href="#findpath" data-toggle="tab">Find a Path</a></li>');
      echo('<li><a href="#batch" data-toggle="tab">Batch</a></li>');
    }
    ?>
  </ul>



<div class="tab-content">
  <?php
  if($getNetworkType == 'DecisionTree'){
    echo('<div role="tabpanel" class="tab-pane" id="interactions">');
  } else {
    echo('<div role="tabpanel" class="tab-pane active" id="interactions">');
  }

  ?>



  <div class="row5">  <!-- if Model tool, this row contains coarse place tools -->
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == 'PetriNet'){
   include('include_advanced_row1_petri.php');
  }
  ?>
  </div><!-- end fullwidth -->
</div><!-- end row -->
</div><!-- end row5 -->

  <div class="row2">
    <div class="row">


  <div class="col-md-8 col-sm-8 col-xs-8 ">


  <div id="dropdowns">


  Organism:

  <select name="organisms" id="organisms">
            <option value="" selected="selected">Select an option</option>
            <option value="3702|intermine">A. thaliana (ThaleMine)</option>
            <option value="3702|biogrid">A. thaliana (BioGRID)</option>
            <option value="6239|biogrid">C. elegans (BioGRID)</option>
            <option value="7227|biogrid">D. melanogaster (BioGRID)</option>
            <option value="7227|intermine">D. melanogaster (FlyMine)</option>
            <option value="7227|flybase">D. melanogaster (FlyBase)</option>
            <option value="7955|biogrid">D. rerio (BioGRID)</option>
            <option value="511145|biogrid">E. coli (BioGRID)</option>
            <option value="9606|biogrid">H. sapiens (BioGRID)</option>
            <option value="9606|intermine">H. sapiens (HumanMine)</option>
            <option value="9606|drugbank">H. sapiens: Gene <-Drug (DrugBank)</option>
            <option value="10090|biogrid">M. musculus (BioGRID)</option>
            <option value="10090|intermine">M. musculus (MouseMIne)</option>
            <option value="10116|biogrid">R. norvegicus (BioGRID)</option>
            <option value="559292|biogrid">S. cerevisiae (BioGRID)</option>
            <option value="559292|intermine">S. cerevisiae (YeastMine)</option>
            <option value="4896|biogrid">S. pombe (BioGRID)</option>
            <option value="pombase|pombase">S. pombe (PomBase curated)</option>





  </select> <br />
  <div id="intOpts">

  Interaction type:

  <select id="interaction-type">
     <option value="-1" selected="selected">Select an option</option>
  <option value="any">Genetic and Physical</option>
    <option value="genetic">Genetic</option>
    <option value="physical">Physical</option>
  </select>
  <br />
  Throughput (BioGRID only):
  <select id="interaction-throughput">
    <option value="any" selected="selected">High and Low</option>
      <option value="high">High</option>
      <option value="low">Low</option>
    </select>
    </div>

    </div> <!-- end of #dropdowns -->

    <div id="selIntType" style="display:none;" class="bg-warning">
    Identifier type (required):
    <select id="selectIdentifierType">
    <option value="id" selected="selected">ID</option>
    <option value="symbol">Symbol</option>
    </select>
    </div>


  <br />
  <h4>Retrieve Interactions for the Selected Node.</h4>
  <button class="btn btn-success" onclick="getInteractions()" id="get-ints-btn">Get interactions</button>
  <br />
  <div id="loading-container"></div>

  Results: <br />




  </div> <!-- end col-md-8 -->
<div class="col-md-4 col-sm-4 col-xs-4 ">
<div id="fillNetworksContainer"></div>
</div>
  </div> <!-- end row class -->
</div> <!-- end row2 class -->

<div class="row2">
  <div class="row">

<div class="col-md-12 col-sm-12 col-xs-12 fullwidth">
<div id="int-results"></div>
</div>
</div>
</div>



</div> <!-- end tab -pane - interactions -->

<div class="tab-pane" id="netstat">
<div class="row3">
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">
  <?php
  include('include_results_centralities.php')
  ?>
</div><!-- end fullwidth -->
</div><!-- end row -->
  </div><!-- end row3 -->

</div> <!-- end tab netstat -->

<div class="tab-pane" id="findpath">
<div class="row4">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == 'DecisionTree'){
    include('include_results_paths_decisiontree.php');
  } else {
    include('include_results_paths_other.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab findpath -->

<!-- batch -->
<div class="tab-pane" id="batch">
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == "Graph"){
    include('include_batch.php');
  }
  if($getNetworkType == "DecisionTree"){
    include('include_batch_dt.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab batch -->

<!-- run model -->
<?php
if($getNetworkType == 'DecisionTree'){
  echo('<div class="tab-pane active" id="run_model">');
} else {
  echo('<div class="tab-pane" id="run_model">');
}

?>
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth" id="adv_a">

  <?php
  if($getNetworkType == "DecisionTree"){
    include('include_run_model.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab run model -->

<!-- variable editor -->
<div class="tab-pane" id="variable_editor">
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == "DecisionTree"){
    include('include_variable_editor.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab run model -->


<!-- rule editor -->
<div class="tab-pane" id="rule_editor">
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == "DecisionTree"){
    include('include_rule_editor.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab run model -->

<!-- rule editor -->
<div class="tab-pane" id="description_editor">
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == "DecisionTree"){
    include('include_description_editor.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab run model -->

<!-- tests -->
<div class="tab-pane" id="tests">
<div class="row2">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">

  <?php
  if($getNetworkType == "DecisionTree"){
    include('include_model_tests.php');
  }
  ?>

  </div><!-- end fullwidth -->
 </div><!-- end row -->

  </div><!-- end row4 -->

</div> <!-- end tab tests -->

</div> <!-- end tab class -->

</div> <!-- end div advanced -->



<!--<script type="text/javascript" src="app.js"></script>
<script type="text/javascript" src="megamerge_or.js"></script>
-->
<script type="text/javascript" src="src/app_common.js"></script>
<script type="text/javascript" src="src/dataset.js"></script>
<script type="text/javascript" src="<?php echo $appVersion ;?>"></script>
<script type="text/javascript" src="src/is_acyclic.js"></script>
<script type="text/javascript" src="src/typeahead.js"></script>
<script type="text/javascript" src="src/info_pane_ui.js"></script>

<script type="text/javascript">
  $( document ).ready(function(){
    $("#organisms").change(function(){
      var v = $("#organisms option:selected").val()
      if(v == "7227|intermine"){
        console.log("flymine")
        document.getElementById("selIntType").style.display = 'block'

      }else if(v == "9606|drugbank"){
        console.log("drugbank")
        document.getElementById("intOpts").style.display = 'none'
        document.getElementById("selIntType").style.display = 'none'
      } else {
        document.getElementById("selIntType").style.display = 'none'
        document.getElementById("intOpts").style.display = 'block'
      }
      $("#organisms").val(v) //otherwise the selection doesn't get updated
    })

    //for the network generator modal
    $("#org_generate_nw").change(function(){
      var v = $("#org_generate_nw option:selected").val()
      if(v == "7227|intermine"){
        console.log("flymine")
        document.getElementById("gen_selIntType").style.display = 'block'

      } else {
        document.getElementById("gen_selIntType").style.display = 'none'
      }
      $("#org_generate_nw").val(v) //otherwise the selection doesn't get updated
    })



    //show fill network options
    if(window.esynOpts.type == "Graph"){
      $("#fillNetworksContainer").html('<br /><h4>Fill missing edges <sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#fillinfoModal">[?]</button></sup></a></h4><button class="btn btn-success" onclick="fillNetwork()" id="fill-ints-btn">Fill</button>')

      //set interactions to be consistent with API
      setInteractionOptions(window.esynOpts)
    }



    //the code will have to go here for the appearing Advanced tools

    $("#AdvOpt").change(function(){
       if($(this).is(":checked")) {
            document.getElementById("gen_selAdvOpt").style.display = 'block'

        } else {

          document.getElementById("gen_selAdvOpt").style.display = 'none'

        }

    })
    $("#AdvOpt2").change(function(){
       if($(this).is(":checked")) {
            document.getElementById("gen_selAdvOpt2").style.display = 'block'

        } else {

          document.getElementById("gen_selAdvOpt2").style.display = 'none'

        }

    })

    $("#AdvOpt3").change(function(){
       if($(this).is(":checked")) {
            document.getElementById("gen_selAdvOpt3").style.display = 'block'

        } else {

          document.getElementById("gen_selAdvOpt3").style.display = 'none'

        }

    })

    $("#AdvOptPetri").change(function(){
       if($(this).is(":checked")) {
            document.getElementById("gen_selAdvOptPetri").style.display = 'block'

        } else {

          document.getElementById("gen_selAdvOptPetri").style.display = 'none'

        }

    })


    })
</script>

<script type="text/javascript">
$('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>


  </div>
</div>

<!-- grey out the screen for loading -->
<div id="loading-grey">
  <div id="loading-text"></div>
</div>

<?php include("footer.php"); ?>


</body>

</div>
</html>
