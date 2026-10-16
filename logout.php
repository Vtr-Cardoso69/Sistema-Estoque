<?php
session_start();
$_SESSION = [];
session_destroy();

header('Location: View/Usuario/login.php');
exit;
