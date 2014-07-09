<?php session_start(); ?>

<?php include("header.php"); ?>

<?php

if (!empty($_SESSION['userid'])) {

  header("Location: home.php");
  die();
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

  </head>
  <body id="myesyn">
  <?php include("header-menu.php"); ?>




<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<style>
  *{margin:0;padding:0;font-size:15px;font-family:helvetica,arial,sans-serif}
  footer,section,header{display:block;}
  h1{margin:2em}
  button,p{border:none;background:transparent;margin:0 2em;}
</style>
<script src="https://browserid.org/include.js"></script></head>
<body>
<br> <br> <br>
<center>
  <header><h3>To save your networks online, please log in <br> using your google email address <br> (..or sign in with any other account)</h3></header>
 
  <section>
    <button><img src="https://browserid.org/i/sign_in_green.png" alt="sign in with browser ID"></button>
  </section>
  <br><br>
   By logging in esyN you agree to our <a href="policy.html#termsofuse"> Terms of Use</a> and <a href="policy.html" >  Privacy Policy </a>.
  </center>
  <br />


  </center>


<script>
(function(){
  var request,
      but = document.querySelector('button'),
      h1 = document.querySelector('h1');
    
  but.addEventListener('click', function(ev) {

    navigator.id.getVerifiedEmail(function(assertion) {
      if (assertion) {
        verify(assertion);
      } else {
        alert('I still don\'t know you...');
      }
    });

    function verify(assertion) {
      request = new XMLHttpRequest();
      var parameters = 'assert=' + assertion;
      request.open('POST', 'verify.php');
      request.setRequestHeader('If-Modified-Since',
                               'Wed, 05 Apr 2006 00:00:00 GMT');
      request.setRequestHeader('Content-type',
                               'application/x-www-form-urlencoded');
      request.setRequestHeader('Content-length', parameters.length);
      request.setRequestHeader('Connection', 'close');
      request.send(encodeURI(parameters));
    
      request.onreadystatechange = function() {
        if (request.readyState == 4){
          //alert(request.responseText);
          if (request.status && (/200|304/).test(request.status)) {
            response = JSON.parse(request.responseText);
            if(response.status === 'okay') {
              window.location.href = "home.php";
              // message = 'User logged in as '+response.email;
              // var p = document.createElement('p');
              // p.innerHTML = message;
              // but.parentNode.replaceChild(p,but);
              // h1.innerHTML = 'Woohoo, I know you!';

            }
          } else{
            alert('Sorry, couldn\'t log you in...');
          }
        }
      };
    }

  }, false);
}());
</script>
</body>
</html>