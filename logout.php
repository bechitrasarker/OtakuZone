<?php
require_once 'connection.php';
session_destroy();
header("Location: index.php");
exit();
?>
