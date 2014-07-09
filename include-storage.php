<?php
$super = array('dbean8@gmail.com','favrin@gmail.com','lorenzo.ficorella@gmail.com');
if(isset($_SESSION['userid']) && !in_array($_SESSION['userid'], $super) ){
  echo '<script src="src/checkStorage.js"></script>';
}
?>