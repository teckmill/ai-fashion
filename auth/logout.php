<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth($pdo);
$auth->logout();

header("Location: /auth/login.php");
exit;