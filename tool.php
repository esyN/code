<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/tool.css">

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
      'loggedin' => isset($_SESSION['userid']),
    );

  $esynOpts = addslashes(json_encode($arr));

  $appVersion = 'src/app_'.$getNetworkType.'.js';

  //build a table of the users projects
  //this should be replaced by an action in manager.php
  $userid = $_SESSION['userid'];


?>

<script type="text/javascript">
   var esynOpts = JSON.parse("<?php echo $esynOpts; ?>");
</script>


<title>esyN</title>

<script type="text/javascript" src="./src/cytoscape.js-3.2.22/cytoscape.min.js"></script>
<!-- dagre layout -->
<script src="https://unpkg.com/dagre@0.7.4/dist/dagre.js"></script>
<script type="text/javascript" src="./src/cytoscape.js-dagre-2.2.1/cytoscape-dagre.js"></script>

<!-- klay layout -->
<script src="https://unpkg.com/klayjs@0.4.1/klay.js"></script>
<script type="text/javascript" src="./src/cytoscape.js-klay-3.1.2/cytoscape-klay.js"></script>


<script src="src/jquery.csv-0.71.min.js"></script>
<!-- UI dialogs -->
<script src="src/bootstrap.min.js"></script>
<script type="text/javascript" src="src/filtrex.js"></script>


<!-- underscore -->
<script type="text/javascript" src="src/underscore-1.6.0-min.js"></script>

<script type="text/javascript" src="src/typeahead.js"></script>
<link rel="stylesheet" type="text/css" href="src/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">
<script type="text/javascript" src="src/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>


</head>
<body>

<?php include("header-menu.php"); ?>

<div id="cy" class="hidden"></div>
<div class="hidden">
  <?php
    include('include_variable_editor.php');
  ?>
</div>
<div class="hidden">
  <?php
      include('include_rule_editor.php');
  ?>
</div>
  <div id="advanced">
  <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 fullwidth">
    <form id="set_filename_form">
    <input type="text" name="set_filename" id="set_filename" size="35" maxlength="50" value ="network_json">
  </form>

  <textarea id='de_text' class="form-control" rows="10" id="de_text" readonly></textarea>
  <br>
<?php
    include('include_run_model.php');
  ?>
</div>
</div>
</div>

<script type="text/javascript" src="src/app_common.js"></script>
<script type="text/javascript" src="src/dataset.js"></script>
<script type="text/javascript" src="src/app_DecisionTree.js"></script>
<script type="text/javascript" src="src/is_acyclic.js"></script>

<!-- for compatibility with app code -->
<input type="hidden" name="nw_name" id="nw_name" size="40" maxlength="25" value ="network_name" disabled="disabled" >
<input type="hidden" class="form-control typeahead" id="textconditionprop">
<div id="input-mode-select" class="input-mode-select">

  <form name='type_form' class="type_form">
  <div class="toggle-btn-grp" style="display:none;">
      <div><input type="radio" name="types"  value="e-d" id="e-d"/><label for="e-d" onclick="" class="toggle-btn">dummy</label></div>

  </div>
  </form>
</div>
</html>
