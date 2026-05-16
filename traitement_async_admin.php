<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["success" => false]); exit();
}
if (isset($_POST['id_user']) && $_POST['action'] === 'bloquer') {
    echo json_encode(["success" => true]);
    exit();
}
echo json_encode(["success" => false]);
?>
