<?php

  session_start();

  $url = 'https://browserid.org/verify';
  $assert = $_POST['assert'];
  $params = 'assertion='.$assert.'&audience=' .
             urlencode('http://www.esyn.org/');
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch,CURLOPT_POST,2);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
  $result = curl_exec($ch);
  curl_close($ch);


  $json_a=json_decode($result,true);

  if ($json_a['status'] == 'okay') {
    $_SESSION['userid'] = $json_a['email'];
  }

  echo $result;

?>