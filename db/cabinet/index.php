<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}
header('Location: conversations.php');
exit;
