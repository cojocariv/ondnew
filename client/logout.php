<?php
session_start();
unset($_SESSION['client_user_id'], $_SESSION['client_email'], $_SESSION['client_name'], $_SESSION['client_picture']);
header('Location: index.php');
exit;

