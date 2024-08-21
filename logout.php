<?php
session_start();
$_SESSION = array();
session_destroy();
header("Location: loginform.php");
exit;
?>