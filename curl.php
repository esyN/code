<?php 

function cURLcheckBasicFunctions() 
{ 
  if( !function_exists("curl_init") && 
      !function_exists("curl_setopt") && 
      !function_exists("curl_exec") && 
      !function_exists("curl_close") ) return false; 
  else return true; 
} 

/* 
 * Returns string status information. 
 * Can be changed to int or bool return types. 
 */ 
function cURLdownload($url, $file) 
{ 
  if( !cURLcheckBasicFunctions() ) return "UNAVAILABLE: cURL Basic Functions"; 
  $ch = curl_init(); 
  if($ch) 
  { 

      if( !curl_setopt($ch, CURLOPT_URL, $url) ) 
      { 

        curl_close($ch); // to match curl_init() 
        return "FAIL: curl_setopt(CURLOPT_URL)"; 
      }

      $assert = "ABCDEFG";
  $params = 'assertion='.urlencode($assert).'&audience=' .
             urlencode('http://www.esyn.org/');

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);



        curl_setopt($ch,CURLOPT_POST,2);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $params);

      if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) return "FAIL: curl_setopt(CURLOPT_HEADER)"; 
      if( !curl_exec($ch) ) {
        echo 'Curl error: ' . curl_error($ch);
        return "FAIL: curl_exec()";
      }  
      curl_close($ch); 

      return "SUCCESS:"; 

  } 
  else return "FAIL: curl_init()"; 
} 

// Download from 'example.com' to 'example.txt' 
echo cURLdownload("https://login.persona.org/verify", "example.txt"); 

?> 