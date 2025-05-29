<?php
require_once '../models/db.php';
require_once '../models/userModel.php';

$userModel = new UserModel();

session_start();
$userModel->salidaSesion();
session_unset();
session_destroy();

header('Location: ../views/login.php');
exit();
?>