<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';

logoutUser();
header('Location: login.php');
exit();
?>
