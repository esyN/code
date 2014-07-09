<html id="ng-app" ng-app="projectApp">
<head>
<!-- Favicon -->
<link rel="icon" type="image/ico" href="favicon.ico"/>
<!-- Menu -->
<link rel="stylesheet" type="text/css" href="./css/fancymenu.css">
<!-- basic page style -->
<link rel="stylesheet" type="text/css" href="./css/body.css">
<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'> 
<!-- <script src="src/jquery-2.1.0.min.js"></script> -->
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

<script type="text/javascript">
$(function(){
	//on DOM ready
	var sel = $('body').attr('id') + '-nav';
	$('#' + sel).addClass('active');
})
</script>